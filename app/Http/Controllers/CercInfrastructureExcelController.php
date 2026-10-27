<?php

namespace App\Http\Controllers;

use App\Models\CercAssetCacMember;
use App\Models\CercFarmerOrganization;
use App\Models\CercFarmerOrganizationMember;
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
    private function tankType(Request $request): string
    {
        $tankType = strtolower((string) $request->input('tank_type', $request->query('tank_type', '')));
        abort_unless(in_array($tankType, ['major', 'minor'], true), 404);

        return $tankType;
    }

    private const SHEET_FIELDS = [
        'A_Scheme' => [
            'tank_registration_number' => 'Tank Registration Number',
            'river_basin' => 'River Basin',
            'river_basin_number' => 'River Basin - Number',
            'tank_name' => 'Tank Name',
            'cascade_name' => 'Tank Cascade Name',
            'province' => 'Province',
            'district' => 'District',
            'ds_division' => 'DS Division',
            'asc' => 'ASC',
            'gn_division' => 'GN Division',
            'gn_code' => 'GN Code',
            'gn_division_families' => 'GN Division families - number',
            'tank_capacity_ac_ft' => 'Tank Capacity (ac.ft)',
            'catchment_area_ha' => 'Catchment Area (ha)',
            'irrigable_area_existing_ha' => 'Irrigable Area - Existing (ha)',
            'irrigable_area_cultivated_ha' => 'Irrigable Area - Actual Cultivated (ha)',
            'main_crop_cultivation' => 'Main Crop Cultivation',
            'tank_farmer_families' => 'Number of farmer families in the Tank - Number',
            'farmer_organization_families' => 'Number of farmer families in Farmer Organization - Number',
            'scheme_type_tank' => 'Scheme Type - Tank',
            'scheme_type_anicut' => 'Scheme Type - Anicut',
            'canal_length_left_bank_km' => 'Canal Length - Left Bank (Km)',
            'canal_length_center_bank_km' => 'Canal Length - Center Bank (Km)',
            'canal_length_right_bank_km' => 'Canal Length - Right Bank (Km)',
            'tank_status' => 'Tank Status / Condition',
        ],
        'B_CAC' => [
            'tank_registration_number' => 'Tank Registration Number',
            'cmc_name' => 'CMC Name',
            'cac_name' => 'CAC Name',
            'member_name' => 'CAC Member Name',
            'position' => 'Member Position',
            'nic_number' => 'Member NIC Number',
            'gender' => 'Member Gender',
            'contact_number' => 'Member Contact Number',
        ],
        'C_Organization' => [
            'tank_registration_number' => 'Tank Registration Number',
            'name' => 'Name of Farmer Organization',
            'legal_registration_status' => 'Legal / Registration Status',
            'registration_number' => 'Registration Number',
            'registration_date' => 'Registration Date',
            'address' => 'Address',
            'contact_number' => 'Organization Contact Number',
            'email' => 'Email Address',
            'total_members' => 'Total Number of Members above available',
        ],
        'D_Members' => [
            'tank_registration_number' => 'Tank Registration Number',
            'name_with_initials' => 'Name with Initials',
            'gender' => 'Gender',
            'nic_number' => 'NIC',
            'phone_number' => 'Phone Number',
            'address' => 'Address',
            'land_extent_acres' => 'Land Extent (acres)',
            'role_in_organization' => 'Role in Farmer Organization',
        ],
    ];

    private const HEADER_ALIASES = [
        'A_Scheme' => [
            'cascade_name' => ['Cascade Name', 'TankCascade Name'],
            'irrigable_area_existing_ha' => ['Existing Irrigable Area (ha)'],
            'irrigable_area_cultivated_ha' => ['Cultivated Irrigable Area (ha)'],
            'tank_farmer_families' => ['Farmer Families in Tank'],
            'farmer_organization_families' => ['Farmer Families in Farmer Organization'],
            'scheme_type_tank' => ['Tank Scheme Type'],
            'scheme_type_anicut' => ['Anicut Scheme Type'],
            'canal_length_left_bank_km' => ['Left Bank Canal Length (km)'],
            'canal_length_center_bank_km' => ['Center Bank Canal Length (km)'],
            'canal_length_right_bank_km' => ['Right Bank Canal Length (km)'],
        ],
        'C_Organization' => [
            'total_members' => ['Total Number of Members', 'Total Number of Membersabove available'],
        ],
    ];

    public function template(Request $request, SimpleXlsxWorkbook $workbook): StreamedResponse
    {
        $tankType = $this->tankType($request);
        $sheets = [];
        foreach (self::SHEET_FIELDS as $name => $fields) {
            $sheets[$name] = [array_values($fields)];
        }

        $content = $workbook->write($sheets);

        return response()->streamDownload(fn () => print $content, "cerc-{$tankType}-tank-template.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function export(Request $request, SimpleXlsxWorkbook $workbook): StreamedResponse
    {
        $tankType = $this->tankType($request);
        $sheets = [];
        foreach (self::SHEET_FIELDS as $name => $fields) {
            $sheets[$name] = [array_values($fields)];
        }

        $assets = CercInfrastructureAsset::with('cacMembers')->where('tank_type', $tankType)->orderBy('tank_registration_number')->get();
        foreach ($assets as $asset) {
            $sheets['A_Scheme'][] = $this->exportRow(self::SHEET_FIELDS['A_Scheme'], $asset);

            foreach ($asset->cacMembers as $index => $member) {
                $overrides = [
                    'tank_registration_number' => $asset->tank_registration_number,
                ];
                if ($index > 0) {
                    $overrides['cmc_name'] = null;
                    $overrides['cac_name'] = null;
                }
                $sheets['B_CAC'][] = $this->exportRow(self::SHEET_FIELDS['B_CAC'], $member, $overrides);
            }
        }

        $organizations = CercFarmerOrganization::with(['asset', 'members'])
            ->whereHas('asset', fn ($query) => $query->where('tank_type', $tankType))
            ->orderBy('name')->get();
        foreach ($organizations as $organization) {
            $registrationNumber = $organization->asset?->tank_registration_number;
            $sheets['C_Organization'][] = $this->exportRow(self::SHEET_FIELDS['C_Organization'], $organization, [
                'tank_registration_number' => $registrationNumber,
            ]);

            foreach ($organization->members as $member) {
                $sheets['D_Members'][] = $this->exportRow(self::SHEET_FIELDS['D_Members'], $member, [
                    'tank_registration_number' => $registrationNumber,
                ]);
            }
        }

        $content = $workbook->write($sheets);

        return response()->streamDownload(fn () => print $content, "cerc-{$tankType}-tank-records.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, SimpleXlsxWorkbook $workbook): RedirectResponse
    {
        $tankType = $this->tankType($request);
        $request->validate([
            'excel_file' => ['required', 'file', 'max:20480', 'mimes:xlsx'],
        ], [
            'excel_file.mimes' => 'Upload an Excel workbook in .xlsx format.',
            'excel_file.max' => 'The workbook must not exceed 20 MB.',
        ]);

        $sheets = $workbook->read($request->file('excel_file')->getRealPath());
        if (! isset($sheets['A_Scheme'])) {
            throw ValidationException::withMessages(['excel_file' => 'The workbook must include the A_Scheme sheet from the downloaded template.']);
        }

        $columnCount = 0;
        foreach ($sheets as $rows) {
            if ($rows !== []) {
                $headerRow = $rows[array_key_first($rows)];
                $columnCount += count(array_filter($headerRow, fn ($header) => trim((string) $header) !== ''));
            }
        }

        $sheetRows = [];
        foreach (self::SHEET_FIELDS as $sheetName => $fieldLabels) {
            if (! isset($sheets[$sheetName])) {
                continue;
            }
            $sheetRows[$sheetName] = $this->prepareRows($sheetName, $sheets[$sheetName], $fieldLabels);
        }

        if ($sheetRows['A_Scheme']['records'] === []) {
            throw ValidationException::withMessages(['excel_file' => 'Add at least one scheme row to the A_Scheme sheet.']);
        }

        $counts = DB::transaction(function () use ($sheetRows, $request, $tankType): array {
            $counts = [
                'schemes_added' => 0, 'schemes_updated' => 0,
                'cac_members_added' => 0, 'cac_members_updated' => 0,
                'organizations_added' => 0, 'organizations_updated' => 0,
                'fo_members_added' => 0, 'fo_members_updated' => 0,
            ];
            $assetsByRegistration = [];
            $cacNamesByRegistration = [];

            foreach ($sheetRows['A_Scheme']['records'] as $entry) {
                $row = $this->validateRow($entry['data'], [
                    'tank_registration_number' => ['required', 'string', 'max:255'],
                    'tank_name' => ['required', 'string', 'max:255'],
                    'gn_division_families' => ['nullable', 'integer', 'min:0'],
                    'tank_capacity_ac_ft' => ['nullable', 'numeric', 'min:0'],
                    'catchment_area_ha' => ['nullable', 'numeric', 'min:0'],
                    'irrigable_area_existing_ha' => ['nullable', 'numeric', 'min:0'],
                    'irrigable_area_cultivated_ha' => ['nullable', 'numeric', 'min:0'],
                    'tank_farmer_families' => ['nullable', 'integer', 'min:0'],
                    'farmer_organization_families' => ['nullable', 'integer', 'min:0'],
                    'scheme_type_tank' => ['nullable', 'in:Major,Medium,Minor'],
                    'scheme_type_anicut' => ['nullable', 'in:Large,Minor'],
                    'canal_length_left_bank_km' => ['nullable', 'numeric', 'min:0'],
                    'canal_length_center_bank_km' => ['nullable', 'numeric', 'min:0'],
                    'canal_length_right_bank_km' => ['nullable', 'numeric', 'min:0'],
                    'tank_status' => ['nullable', 'in:Functional,Partially functional,Non-functional,Abandoned'],
                ], 'A_Scheme', $entry['row']);
                $registration = trim($row['tank_registration_number']);
                if (isset($assetsByRegistration[$registration])) {
                    $this->rowError('A_Scheme', $entry['row'], 'Tank Registration Number appears more than once in this workbook.');
                }

                $asset = CercInfrastructureAsset::query()->where('tank_type', $tankType)->where('tank_registration_number', $registration)->first();
                $isNew = ! $asset;
                $payload = $row;
                if ($isNew) {
                    $payload['created_by'] = $request->user()->id;
                    $payload['tank_type'] = $tankType;
                    $asset = new CercInfrastructureAsset;
                }
                $asset->fill($payload);
                $asset->save();
                $assetsByRegistration[$registration] = $asset;
                $counts[$isNew ? 'schemes_added' : 'schemes_updated']++;
            }

            foreach (($sheetRows['B_CAC']['records'] ?? []) as $entry) {
                $row = $this->validateRow($entry['data'], [
                    'tank_registration_number' => ['required', 'string'],
                    'cmc_name' => ['nullable', 'string', 'max:255'],
                    'cac_name' => ['nullable', 'string', 'max:255'],
                    'member_name' => ['required', 'string', 'max:255'],
                    'position' => ['required', 'in:Member,Co-chairperson'],
                    'nic_number' => ['nullable', 'regex:/^(\d{10}|\d{9}[vVxX]|\d{12})$/'],
                    'gender' => ['nullable', 'in:Male,Female'],
                    'contact_number' => ['nullable', 'regex:/^\d{10}$/'],
                ], 'B_CAC', $entry['row']);
                $asset = $this->assetForRegistration($row['tank_registration_number'], $assetsByRegistration, 'B_CAC', $entry['row'], $tankType);
                $registration = trim($row['tank_registration_number']);
                if (! array_key_exists($registration, $cacNamesByRegistration)) {
                    $existingMember = $asset->cacMembers()->first();
                    $cacNamesByRegistration[$registration] = [
                        'cmc_name' => $row['cmc_name'] ?? $existingMember?->cmc_name,
                        'cac_name' => $row['cac_name'] ?? $existingMember?->cac_name,
                    ];
                }
                foreach (['cmc_name', 'cac_name'] as $field) {
                    $value = $row[$field] ?? null;
                    if (filled($value) && filled($cacNamesByRegistration[$registration][$field]) && trim($value) !== trim($cacNamesByRegistration[$registration][$field])) {
                        $this->rowError('B_CAC', $entry['row'], ucfirst(str_replace('_', ' ', $field)).' should be entered only once for each Tank Registration Number.');
                    }
                    if (filled($value)) {
                        $cacNamesByRegistration[$registration][$field] = $value;
                    }
                    $row[$field] = $cacNamesByRegistration[$registration][$field];
                }
                unset($row['tank_registration_number']);
                $member = $asset->cacMembers()
                    ->where('member_name', $row['member_name'])
                    ->when(! empty($row['nic_number']), fn ($query) => $query->where('nic_number', $row['nic_number']))
                    ->first();
                if ($member) {
                    $member->update($row);
                    $counts['cac_members_updated']++;
                } else {
                    $asset->cacMembers()->create($row);
                    $counts['cac_members_added']++;
                }
            }

            foreach ($cacNamesByRegistration as $registration => $names) {
                ($assetsByRegistration[$registration] ?? CercInfrastructureAsset::where('tank_type', $tankType)->where('tank_registration_number', $registration)->first())
                    ?->cacMembers()->update($names);
            }

            foreach (($sheetRows['C_Organization']['records'] ?? []) as $entry) {
                $row = $this->validateRow($entry['data'], [
                    'tank_registration_number' => ['required', 'string'],
                    'name' => ['required', 'string', 'max:255'],
                    'legal_registration_status' => ['required', 'in:Registered,Not registered,Registration in progress'],
                    'registration_number' => ['nullable', 'string', 'max:255'],
                    'registration_date' => ['nullable', 'string'],
                    'address' => ['nullable', 'string'],
                    'contact_number' => ['nullable', 'string', 'max:30'],
                    'email' => ['nullable', 'email', 'max:255'],
                    'total_members' => ['nullable', 'integer', 'min:0'],
                ], 'C_Organization', $entry['row']);
                $asset = $this->assetForRegistration($row['tank_registration_number'], $assetsByRegistration, 'C_Organization', $entry['row'], $tankType);
                unset($row['tank_registration_number']);
                if (isset($row['registration_date'])) {
                    $row['registration_date'] = $this->excelDate($row['registration_date'], 'C_Organization', $entry['row']);
                }
                $organization = CercFarmerOrganization::firstOrNew(['cerc_infrastructure_asset_id' => $asset->id]);
                $isNew = ! $organization->exists;
                if ($isNew) {
                    $row['created_by'] = $request->user()->id;
                }
                $organization->fill($row);
                $organization->asset()->associate($asset);
                $organization->save();
                $assetsByRegistration[trim($asset->tank_registration_number)] = $asset;
                $counts[$isNew ? 'organizations_added' : 'organizations_updated']++;
            }

            foreach (($sheetRows['D_Members']['records'] ?? []) as $entry) {
                $row = $this->validateRow($entry['data'], [
                    'tank_registration_number' => ['required', 'string'],
                    'name_with_initials' => ['required', 'string', 'max:255'],
                    'gender' => ['nullable', 'in:Male,Female'],
                    'nic_number' => ['nullable', 'regex:/^(\d{10}|\d{12})$/'],
                    'phone_number' => ['nullable', 'regex:/^\d{10}$/'],
                    'address' => ['nullable', 'string'],
                    'land_extent_acres' => ['nullable', 'numeric', 'min:0'],
                    'role_in_organization' => ['nullable', 'in:Member,President,Secretary,Treasurer,Other'],
                ], 'D_Members', $entry['row']);
                $asset = $this->assetForRegistration($row['tank_registration_number'], $assetsByRegistration, 'D_Members', $entry['row'], $tankType);
                $organization = CercFarmerOrganization::where('cerc_infrastructure_asset_id', $asset->id)->first();
                if (! $organization) {
                    $this->rowError('D_Members', $entry['row'], 'Add this scheme to the C_Organization sheet before importing its members.');
                }
                unset($row['tank_registration_number']);

                $memberQuery = $organization->members();
                if (! empty($row['nic_number'])) {
                    $memberQuery->where('nic_number', $row['nic_number']);
                } else {
                    $memberQuery->where('name_with_initials', $row['name_with_initials']);
                }
                $member = $memberQuery->first();
                if ($member) {
                    $member->update($row);
                    $counts['fo_members_updated']++;
                } else {
                    $organization->members()->create($row);
                    $counts['fo_members_added']++;
                }
            }

            return $counts;
        });

        $addedTotal = $counts['schemes_added'] + $counts['cac_members_added'] + $counts['organizations_added'] + $counts['fo_members_added'];
        $updatedTotal = $counts['schemes_updated'] + $counts['cac_members_updated'] + $counts['organizations_updated'] + $counts['fo_members_updated'];

        return redirect()->route('cerc-assets.index', ['tank_type' => $tankType])
            ->with('status', "Excel import complete: {$addedTotal} records added, {$updatedTotal} records updated.")
            ->with('import_summary', [
                'column_count' => $columnCount,
            ]);
    }

    private function prepareRows(string $sheet, array $rows, array $fields): array
    {
        if ($rows === []) {
            return ['records' => []];
        }

        $headerRowNumber = array_key_first($rows);
        $headers = $rows[$headerRowNumber];
        $fieldIndexes = [];
        foreach ($fields as $field => $label) {
            $expectedHeaders = array_map(
                fn ($header) => $this->normalizeHeader($header),
                array_merge([$label], self::HEADER_ALIASES[$sheet][$field] ?? [])
            );
            foreach ($headers as $index => $header) {
                if (in_array($this->normalizeHeader((string) $header), $expectedHeaders, true)) {
                    $fieldIndexes[$field] = $index;
                    break;
                }
            }
        }

        $requiredHeaders = match ($sheet) {
            'A_Scheme' => ['tank_registration_number', 'tank_name'],
            'B_CAC' => ['tank_registration_number', 'member_name', 'position'],
            'C_Organization' => ['tank_registration_number', 'name', 'legal_registration_status'],
            'D_Members' => ['tank_registration_number', 'name_with_initials'],
        };
        foreach ($requiredHeaders as $required) {
            if (! array_key_exists($required, $fieldIndexes)) {
                $this->rowError($sheet, $headerRowNumber, 'Missing required column: '.$fields[$required].'. Download a fresh template and keep its column headers.');
            }
        }

        $records = [];
        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber === $headerRowNumber || collect($row)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }

            $data = [];
            foreach ($fieldIndexes as $field => $index) {
                $value = trim((string) ($row[$index] ?? ''));
                $data[$field] = $value === '' ? null : $value;
            }
            $records[] = ['row' => $rowNumber, 'data' => $data];
        }

        return ['records' => $records];
    }

    private function validateRow(array $data, array $rules, string $sheet, int $row): array
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $this->rowError($sheet, $row, implode(' ', $validator->errors()->all()));
        }

        return $validator->validated();
    }

    private function assetForRegistration(?string $registration, array $assetsByRegistration, string $sheet, int $row, string $tankType): CercInfrastructureAsset
    {
        $registration = trim((string) $registration);
        $asset = $assetsByRegistration[$registration] ?? CercInfrastructureAsset::where('tank_type', $tankType)->where('tank_registration_number', $registration)->first();
        if (! $asset) {
            $this->rowError($sheet, $row, 'No scheme uses Tank Registration Number "'.$registration.'". Add it to A_Scheme or check the number.');
        }

        return $asset;
    }

    private function excelDate(?string $value, string $sheet, int $row): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
                return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            $this->rowError($sheet, $row, 'Registration Date must be a valid Excel date or YYYY-MM-DD.');
        }
    }

    private function normalizeHeader(string $header): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($header)));
    }

    private function exportRow(array $fields, object $record, array $extra = []): array
    {
        $row = [];
        foreach (array_keys($fields) as $field) {
            $value = array_key_exists($field, $extra) ? $extra[$field] : ($record->{$field} ?? null);
            if ($value instanceof Carbon) {
                $value = $value->toDateString();
            }
            $row[] = $value ?? '';
        }

        return $row;
    }

    private function rowError(string $sheet, int $row, string $message): never
    {
        throw ValidationException::withMessages([
            'excel_file' => "{$sheet}, row {$row}: {$message}",
        ]);
    }
}
