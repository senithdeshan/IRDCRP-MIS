<?php

namespace App\Http\Controllers;

use App\Models\CercInfrastructureAsset;
use App\Support\SimpleXlsxWorkbook;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CercInfrastructureExcelController extends Controller
{
    private const FIELDS = [
        'institution' => 'Institution',
        'programme_type' => 'Programme Type',
        'contract_status' => 'Contract Status',
        'province' => 'Province',
        'district' => 'District',
        'ds_division' => 'DSD',
        'asc' => 'ASC',
        'tank_name' => 'Scheme Name',
        'progress' => 'Progress (%)',
        'civil_cost' => 'Civil Cost (LKR)',
        'preliminaries_cost' => 'Preliminaries (LKR)',
        'direct_cost' => 'Direct Cost (LKR)',
        'vat_18' => 'VAT 18% (LKR)',
        'contingencies_cost' => 'Contingencies (LKR)',
        'total_cost_estimate' => 'Total Cost Estimate (LKR)',
        'awarded_civil_cost' => 'Awarded Amount - Civil Cost (LKR)',
        'awarded_preliminaries_cost' => 'Awarded Amount - Preliminaries (LKR)',
        'awarded_amount_without_vat_contingencies' => 'Awarded Amount without VAT & Contingencies (LKR)',
        'advance_imprest' => 'Advance / Imprest (LKR)',
        'provisional_sum' => 'Provisional Sum (LKR)',
        'category' => 'Category',
        'year' => 'Year',
        'size' => 'Size',
        'beneficiary_families' => 'Beneficiary Families',
        'command_area_ac' => 'Command Area (Ac)',
        'lot' => 'Lot',
        'cluster' => 'Cluster',
        'date_of_award' => 'Date of Award',
        'agreement_sign_date' => 'Agreement Sign Date',
        'contract_period_days' => 'Contract Period (Days)',
        'completion_date' => 'Completion Date',
        'contract_number' => 'Contract Number',
        'contractor_name' => 'Contractor Name',
        'contractor_address' => 'Contractor Address',
        'contractor_contact_number' => 'Contractor Contact Number',
        'emp_clearance_received_date' => 'EMP Clearance Received Date',
        'smp_clearance_received_date' => 'SMP Clearance Received Date',
        'esmp_submitted_to_procurement_date' => 'Date of ESMP Submitted to Procurement Division',
    ];

    public function template(Request $request, SimpleXlsxWorkbook $workbook): StreamedResponse
    {
        $content = $workbook->write(['Minor_Tank' => [array_values(self::FIELDS)]]);

        return response()->streamDownload(fn () => print $content, 'cerc-minor-tank-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function export(Request $request, SimpleXlsxWorkbook $workbook): StreamedResponse
    {
        $rows = [array_values(self::FIELDS)];
        foreach (CercInfrastructureAsset::query()->where('tank_type', 'minor')->orderBy('tank_name')->get() as $asset) {
            $row = [];
            foreach (array_keys(self::FIELDS) as $field) {
                $value = $asset->{$field};
                $row[] = $value instanceof Carbon ? $value->toDateString() : ($value ?? '');
            }
            $rows[] = $row;
        }

        $content = $workbook->write(['Minor_Tank' => $rows]);

        return response()->streamDownload(fn () => print $content, 'cerc-minor-tank-records.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, SimpleXlsxWorkbook $workbook): RedirectResponse
    {
        $request->validate(['excel_file' => ['required', 'file', 'max:20480', 'mimes:xlsx']]);
        $sheets = $workbook->read($request->file('excel_file')->getRealPath());
        $rows = $sheets['Minor_Tank'] ?? $sheets['A_Scheme'] ?? null;
        if (! is_array($rows) || $rows === []) {
            throw ValidationException::withMessages(['excel_file' => 'The workbook must contain a Minor_Tank sheet with the downloaded template headers.']);
        }

        $headerRowNumber = array_key_first($rows);
        $headers = $rows[$headerRowNumber];
        $indexes = [];
        foreach (self::FIELDS as $field => $label) {
            $expected = $this->normalizeHeader($label);
            foreach ($headers as $index => $header) {
                if ($this->normalizeHeader((string) $header) === $expected) {
                    $indexes[$field] = $index;
                    break;
                }
            }
        }
        if (! isset($indexes['tank_name'])) {
            $this->rowError((int) $headerRowNumber, 'Missing required column: Scheme Name. Download a fresh template and keep its headers.');
        }

        $records = [];
        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber === $headerRowNumber || collect($row)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }
            $record = [];
            foreach ($indexes as $field => $index) {
                $value = trim((string) ($row[$index] ?? ''));
                $record[$field] = $value === '' ? null : $value;
            }
            if (! filled($record['tank_name'] ?? null)) {
                $this->rowError((int) $rowNumber, 'Scheme Name is required.');
            }
            foreach (['date_of_award', 'agreement_sign_date', 'completion_date', 'emp_clearance_received_date', 'smp_clearance_received_date', 'esmp_submitted_to_procurement_date'] as $dateField) {
                if (filled($record[$dateField] ?? null)) {
                    $record[$dateField] = $this->excelDate($record[$dateField], (int) $rowNumber);
                }
            }
            $record = $this->validateRecord($record, (int) $rowNumber);
            $records[] = ['row' => (int) $rowNumber, 'data' => $record];
        }

        if ($records === []) {
            throw ValidationException::withMessages(['excel_file' => 'Add at least one scheme row to the workbook.']);
        }

        $counts = DB::transaction(function () use ($records, $request): array {
            $counts = ['added' => 0, 'updated' => 0];
            $seen = [];
            foreach ($records as $entry) {
                $data = $entry['data'];
                $key = mb_strtolower(trim($data['tank_name']));
                if (isset($seen[$key])) {
                    $this->rowError($entry['row'], 'Scheme Name appears more than once in this workbook.');
                }
                $seen[$key] = true;
                $asset = CercInfrastructureAsset::query()->where('tank_type', 'minor')->whereRaw('LOWER(tank_name) = ?', [$key])->first();
                $isNew = ! $asset;
                $data['tank_type'] = 'minor';
                if ($isNew) {
                    $data['created_by'] = $request->user()->id;
                    $asset = new CercInfrastructureAsset;
                }
                $asset->fill($data)->save();
                $counts[$isNew ? 'added' : 'updated']++;
            }
            return $counts;
        });

        return redirect()->route('cerc-assets.index')
            ->with('status', "Excel import complete: {$counts['added']} schemes added, {$counts['updated']} updated.")
            ->with('import_summary', ['column_count' => count($indexes)]);
    }

    private function validateRecord(array $data, int $row): array
    {
        $rules = [
            'institution' => ['nullable', 'string', 'max:255'], 'programme_type' => ['nullable', 'string', 'max:255'],
            'contract_status' => ['nullable', 'string', 'max:255'], 'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'], 'ds_division' => ['nullable', 'string', 'max:255'],
            'asc' => ['nullable', 'string', 'max:255'], 'tank_name' => ['required', 'string', 'max:255'],
            'progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'civil_cost' => ['nullable', 'numeric', 'min:0'], 'preliminaries_cost' => ['nullable', 'numeric', 'min:0'],
            'direct_cost' => ['nullable', 'numeric', 'min:0'], 'vat_18' => ['nullable', 'numeric', 'min:0'],
            'contingencies_cost' => ['nullable', 'numeric', 'min:0'], 'total_cost_estimate' => ['nullable', 'numeric', 'min:0'],
            'awarded_civil_cost' => ['nullable', 'numeric', 'min:0'], 'awarded_preliminaries_cost' => ['nullable', 'numeric', 'min:0'],
            'awarded_amount_without_vat_contingencies' => ['nullable', 'numeric', 'min:0'], 'advance_imprest' => ['nullable', 'numeric', 'min:0'],
            'provisional_sum' => ['nullable', 'numeric', 'min:0'], 'category' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'], 'size' => ['nullable', 'string', 'max:255'],
            'beneficiary_families' => ['nullable', 'integer', 'min:0'], 'command_area_ac' => ['nullable', 'numeric', 'min:0'],
            'lot' => ['nullable', 'string', 'max:255'], 'cluster' => ['nullable', 'string', 'max:255'],
            'date_of_award' => ['nullable', 'date'], 'agreement_sign_date' => ['nullable', 'date'],
            'contract_period_days' => ['nullable', 'integer', 'min:0'], 'completion_date' => ['nullable', 'date'],
            'contract_number' => ['nullable', 'string', 'max:255'], 'contractor_name' => ['nullable', 'string', 'max:255'],
            'contractor_address' => ['nullable', 'string', 'max:2000'], 'contractor_contact_number' => ['nullable', 'string', 'max:50'],
            'emp_clearance_received_date' => ['nullable', 'date'], 'smp_clearance_received_date' => ['nullable', 'date'],
            'esmp_submitted_to_procurement_date' => ['nullable', 'date'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $this->rowError($row, implode(' ', $validator->errors()->all()));
        }
        return $validator->validated();
    }

    private function excelDate(string $value, int $row): ?string
    {
        try {
            if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
                return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
            }
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            $this->rowError($row, 'Date fields must be valid Excel dates or YYYY-MM-DD.');
        }
    }

    private function normalizeHeader(string $header): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($header)));
    }

    private function rowError(int $row, string $message): never
    {
        throw ValidationException::withMessages(['excel_file' => "Minor_Tank, row {$row}: {$message}"]);
    }
}
