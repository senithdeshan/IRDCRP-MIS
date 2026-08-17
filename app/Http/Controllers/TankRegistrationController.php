<?php

namespace App\Http\Controllers;

use App\Models\TankRegistration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class TankRegistrationController extends Controller
{
    private const TEMPLATE_COLUMNS = [
        'Tank Id',
        'Tank Name',
        'River Basin',
        'Cascade Name',
        'Province',
        'District',
        'DS Division',
        'GN Division',
        'AS Centre',
        'Agency',
        'No. of Family',
        'Longitude',
        'Latitude',
        'Progress',
        'Contractor',
        'Contractor Address',
        'Contractor Contact Number',
        'Contractor CIDA Grade',
        'Construction Start Date',
        'Payment',
        'Awarded Date',
        'Construction Period (Days)',
        'Extension of Time(EOT)(months)',
        'Status',
        'Remarks',
        'Open Ref No',
        'Cumulative Amount',
        'Paid Advanced Amount',
        'Recommended IPC No',
        'Recommended IPC Amount',
        'Base Cost',
        'Physical Contingencies',
        'Price Contingencies',
        'Net Value',
        'VAT',
        'Grand Total',
    ];

    private const AMOUNT_FIELDS = [
        'cumulative_amount',
        'paid_advanced_amount',
        'recommended_ipc_amount',
        'base_cost',
        'physical_contingencies',
        'price_contingencies',
        'net_value',
        'vat',
        'grand_total',
    ];

    public function index(Request $request): View
    {
        $query = TankRegistration::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('tank_id', 'like', "%{$search}%")
                    ->orWhere('tank_name', 'like', "%{$search}%")
                    ->orWhere('river_basin', 'like', "%{$search}%")
                    ->orWhere('cascade_name', 'like', "%{$search}%")
                    ->orWhere('contractor', 'like', "%{$search}%")
                    ->orWhere('contractor_contact_number', 'like', "%{$search}%")
                    ->orWhere('contractor_cida_grade', 'like', "%{$search}%")
                    ->orWhere('open_ref_no', 'like', "%{$search}%");
            });
        }

        foreach (['province', 'district', 'payment', 'status'] as $filter) {
            if ($value = $request->string($filter)->trim()->toString()) {
                $query->where($filter, $value);
            }
        }

        $summaryQuery = TankRegistration::query();
        $mapPoints = $this->mapPoints();

        return view('tank-registration.index', [
            'tanks' => $query->latest()->paginate(15)->withQueryString(),
            'summary' => [
                'total' => (clone $summaryQuery)->count(),
                'families' => (clone $summaryQuery)->sum('no_of_family'),
                'averageProgress' => (clone $summaryQuery)->avg('progress'),
                'netValue' => (clone $summaryQuery)->sum('net_value'),
                'grandTotal' => (clone $summaryQuery)->sum('grand_total'),
            ],
            'mapPoints' => $mapPoints,
            'locationSummary' => [
                'located' => $mapPoints->count(),
                'exact' => $mapPoints->where('source', 'Exact GPS coordinates')->count(),
                'detected' => $mapPoints->whereIn('source', ['Detected from district', 'Detected from province'])->count(),
                'project' => $mapPoints->where('source', 'Project coordinate view')->count(),
            ],
            'statusCounts' => TankRegistration::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'paymentCounts' => TankRegistration::query()->select('payment', DB::raw('count(*) as total'))->groupBy('payment')->pluck('total', 'payment'),
            'provinceCounts' => TankRegistration::query()->select('province', DB::raw('count(*) as total'))->whereNotNull('province')->groupBy('province')->orderByDesc('total')->limit(6)->pluck('total', 'province'),
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'statuses' => $this->distinctValues('status'),
            'payments' => $this->distinctValues('payment'),
        ]);
    }

    public function create(): View
    {
        return view('tank-registration.create', [
            'tank' => new TankRegistration,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tank = TankRegistration::create($this->recordData($request));
        $this->syncConstructionImages($request, $tank);

        return redirect()->route('tank-registration.index')->with('status', 'Tank record created successfully.');
    }

    public function show(TankRegistration $tankRegistration): View
    {
        return view('tank-registration.show', [
            'tank' => $tankRegistration,
            'mapPoint' => $tankRegistration->sriLankaMapPoint(),
            'mapPoints' => $this->mapPoints(),
        ]);
    }

    public function edit(TankRegistration $tankRegistration): View
    {
        return view('tank-registration.edit', [
            'tank' => $tankRegistration,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, TankRegistration $tankRegistration): RedirectResponse
    {
        $tankRegistration->update($this->recordData($request, $tankRegistration));
        $this->syncConstructionImages($request, $tankRegistration);

        return redirect()->route('tank-registration.index')->with('status', 'Tank record updated successfully.');
    }

    public function destroy(TankRegistration $tankRegistration): RedirectResponse
    {
        $tankRegistration->delete();

        return redirect()->route('tank-registration.index')->with('status', 'Tank record deleted successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'tank_file' => ['required', 'file', 'max:15360'],
        ]);

        $file = $request->file('tank_file');
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'tank_file' => 'Please upload a .xlsx Excel file.',
            ]),
        };

        if ($rows === []) {
            throw ValidationException::withMessages([
                'tank_file' => 'The Excel file could not be read. Please use the downloaded Tank Registration template.',
            ]);
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $headers = array_map(fn ($header) => $this->fieldForHeader((string) $header), array_shift($rows) ?? []);

        if (! in_array('tank_id', $headers, true) || ! in_array('tank_name', $headers, true)) {
            throw ValidationException::withMessages([
                'tank_file' => 'The Excel file must include Tank Id and Tank Name columns.',
            ]);
        }

        foreach ($rows as $row) {
            $payload = $this->payloadFromImportRow($headers, $row);

            if (empty($payload['tank_id']) || empty($payload['tank_name'])) {
                $skipped++;
                continue;
            }

            $payload['imported_at'] = now();
            $existing = TankRegistration::where('tank_id', $payload['tank_id'])->first();
            TankRegistration::updateOrCreate(['tank_id' => $payload['tank_id']], $payload);
            $existing ? $updated++ : $imported++;
        }

        if ($imported + $updated === 0) {
            throw ValidationException::withMessages([
                'tank_file' => "No tank records were imported. {$skipped} row(s) were skipped because Tank Id or Tank Name was missing.",
            ]);
        }

        return redirect()
            ->route('tank-registration.index')
            ->with('status', "Import completed. {$imported} new, {$updated} updated, {$skipped} skipped.");
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo $this->makeXlsx([
                self::TEMPLATE_COLUMNS,
            ]);
        }, 'tank-registration-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function export(): StreamedResponse
    {
        $rows = [self::TEMPLATE_COLUMNS];

        TankRegistration::query()->orderBy('tank_id')->chunk(200, function ($tanks) use (&$rows) {
            foreach ($tanks as $tank) {
                $rows[] = [
                    $tank->tank_id,
                    $tank->tank_name,
                    $tank->river_basin,
                    $tank->cascade_name,
                    $tank->province,
                    $tank->district,
                    $tank->ds_division,
                    $tank->gn_division,
                    $tank->as_centre,
                    $tank->agency,
                    $tank->no_of_family,
                    $tank->longitude,
                    $tank->latitude,
                    $tank->progress,
                    $tank->contractor,
                    $tank->contractor_address,
                    $tank->contractor_contact_number,
                    $tank->contractor_cida_grade,
                    optional($tank->construction_start_date)->toDateString(),
                    $tank->payment,
                    optional($tank->awarded_date)->toDateString(),
                    $tank->construction_period_days,
                    $tank->extension_of_time_months,
                    $tank->status,
                    $tank->remarks,
                    $tank->open_ref_no,
                    $tank->cumulative_amount,
                    $tank->paid_advanced_amount,
                    $tank->recommended_ipc_no,
                    $tank->recommended_ipc_amount,
                    $tank->base_cost,
                    $tank->physical_contingencies,
                    $tank->price_contingencies,
                    $tank->net_value,
                    $tank->vat,
                    $tank->grand_total,
                ];
            }
        });

        return response()->streamDownload(function () use ($rows) {
            echo $this->makeXlsx($rows);
        }, 'tank-registration-export.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function validated(Request $request, ?TankRegistration $tankRegistration = null): array
    {
        return $request->validate([
            'tank_id' => ['required', 'string', 'max:255', Rule::unique('tank_registrations')->ignore($tankRegistration)],
            'tank_name' => ['required', 'string', 'max:255'],
            'river_basin' => ['nullable', 'string', 'max:255'],
            'cascade_name' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ds_division' => ['nullable', 'string', 'max:255'],
            'gn_division' => ['nullable', 'string', 'max:255'],
            'as_centre' => ['nullable', 'string', 'max:255'],
            'agency' => ['nullable', 'string', 'max:255'],
            'no_of_family' => ['nullable', 'integer', 'min:0'],
            'longitude' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric'],
            'progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'contractor' => ['nullable', 'string', 'max:255'],
            'contractor_address' => ['nullable', 'string'],
            'contractor_contact_number' => ['nullable', 'string', 'max:255'],
            'contractor_cida_grade' => ['nullable', 'string', 'max:255'],
            'construction_start_date' => ['nullable', 'date'],
            'payment' => ['nullable', 'string', 'max:255'],
            'awarded_date' => ['nullable', 'date'],
            'construction_period_days' => ['nullable', 'integer', 'min:0'],
            'extension_of_time_months' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'open_ref_no' => ['nullable', 'string', 'max:255'],
            'recommended_ipc_no' => ['nullable', 'string', 'max:255'],
            'pre_construction_images' => ['array'],
            'pre_construction_images.*' => ['image', 'max:5120'],
            'during_construction_images' => ['array'],
            'during_construction_images.*' => ['image', 'max:5120'],
            'post_construction_images' => ['array'],
            'post_construction_images.*' => ['image', 'max:5120'],
            ...collect(self::AMOUNT_FIELDS)->mapWithKeys(fn ($field) => [$field => ['nullable', 'numeric', 'min:0']])->all(),
        ]);
    }

    private function formOptions(): array
    {
        return [
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'statuses' => $this->distinctValues('status'),
            'payments' => $this->distinctValues('payment'),
        ];
    }

    private function recordData(Request $request, ?TankRegistration $tankRegistration = null): array
    {
        return collect($this->validated($request, $tankRegistration))
            ->except([
                'pre_construction_images',
                'during_construction_images',
                'post_construction_images',
            ])
            ->all();
    }

    private function distinctValues(string $field): array
    {
        return TankRegistration::query()
            ->whereNotNull($field)
            ->distinct()
            ->orderBy($field)
            ->pluck($field)
            ->filter()
            ->values()
            ->all();
    }

    private function mapPoints()
    {
        return TankRegistration::query()
            ->select(['id', 'tank_id', 'tank_name', 'province', 'district', 'longitude', 'latitude'])
            ->orderBy('tank_name')
            ->get()
            ->map(function (TankRegistration $tank) {
                $point = $tank->sriLankaMapPoint();

                if (! $point) {
                    return null;
                }

                $point['show_url'] = route('tank-registration.show', $tank).'#view-location';

                return $point;
            })
            ->filter()
            ->values();
    }

    private function syncConstructionImages(Request $request, TankRegistration $tank): void
    {
        $updates = [];

        foreach (['pre_construction_images', 'during_construction_images', 'post_construction_images'] as $field) {
            $uploads = $this->storeUploads($request, $field, 'tank-registration/'.$tank->id.'/'.$field);

            if ($uploads === []) {
                continue;
            }

            $updates[$field] = [
                ...($tank->{$field} ?? []),
                ...$uploads,
            ];
        }

        if ($updates !== []) {
            $tank->update($updates);
        }
    }

    private function storeUploads(Request $request, string $field, string $directory): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }

        return collect($request->file($field))
            ->map(fn ($file) => $file->store($directory, 'public'))
            ->filter()
            ->values()
            ->all();
    }

    private function payloadFromImportRow(array $headers, array $row): array
    {
        $payload = [];

        foreach ($headers as $index => $field) {
            if (! $field) {
                continue;
            }

            $payload[$field] = trim((string) ($row[$index] ?? ''));
        }

        $payload['construction_start_date'] = $this->normalizeDate($payload['construction_start_date'] ?? null);
        $payload['awarded_date'] = $this->normalizeDate($payload['awarded_date'] ?? null);
        $payload['tank_id'] = $this->normalizeTankId($payload['tank_id'] ?? '');

        foreach (['no_of_family', 'construction_period_days', 'extension_of_time_months'] as $field) {
            $value = $payload[$field] ?? null;
            $payload[$field] = $value === '' || $value === null ? null : (int) $this->number($value);
        }

        foreach (['longitude', 'latitude', 'progress', ...self::AMOUNT_FIELDS] as $field) {
            $payload[$field] = $this->number($payload[$field] ?? null);
        }

        return array_filter($payload, fn ($value) => $value !== '' && $value !== null);
    }

    private function fieldForHeader(string $header): ?string
    {
        $normalized = preg_replace('/[^a-z0-9]+/', '', strtolower($header));

        return match (true) {
            str_contains($normalized, 'tankid') || str_contains($normalized, 'tankcode') => 'tank_id',
            str_contains($normalized, 'tankname') => 'tank_name',
            str_contains($normalized, 'riverbasin') => 'river_basin',
            str_contains($normalized, 'cascadename') => 'cascade_name',
            $normalized === 'province' => 'province',
            $normalized === 'district' => 'district',
            str_contains($normalized, 'dsdivision') || $normalized === 'dsd' => 'ds_division',
            str_contains($normalized, 'gndivision') || $normalized === 'gnd' => 'gn_division',
            str_contains($normalized, 'ascentre') || str_contains($normalized, 'ascenter') || $normalized === 'asc' => 'as_centre',
            str_contains($normalized, 'agency') => 'agency',
            str_contains($normalized, 'nooffamily') || str_contains($normalized, 'families') => 'no_of_family',
            str_contains($normalized, 'longitude') => 'longitude',
            str_contains($normalized, 'latitude') => 'latitude',
            str_contains($normalized, 'progress') => 'progress',
            str_contains($normalized, 'contractoraddress') => 'contractor_address',
            str_contains($normalized, 'contractorcontact') || str_contains($normalized, 'contractorphone') || str_contains($normalized, 'contractortelephone') => 'contractor_contact_number',
            str_contains($normalized, 'contractorcidagrade') || str_contains($normalized, 'cidagrade') => 'contractor_cida_grade',
            str_contains($normalized, 'constructionstartdate') => 'construction_start_date',
            str_contains($normalized, 'contractor') => 'contractor',
            str_contains($normalized, 'payment') => 'payment',
            str_contains($normalized, 'awardeddate') => 'awarded_date',
            str_contains($normalized, 'constructionperiod') => 'construction_period_days',
            str_contains($normalized, 'extensionoftime') || str_contains($normalized, 'eot') => 'extension_of_time_months',
            $normalized === 'status' => 'status',
            str_contains($normalized, 'remarks') => 'remarks',
            str_contains($normalized, 'openref') => 'open_ref_no',
            str_contains($normalized, 'cumulativeamount') => 'cumulative_amount',
            str_contains($normalized, 'paidadvancedamount') || str_contains($normalized, 'paidadvanceamount') => 'paid_advanced_amount',
            str_contains($normalized, 'recommendedipcno') => 'recommended_ipc_no',
            str_contains($normalized, 'recommendedipcamount') => 'recommended_ipc_amount',
            str_contains($normalized, 'basecost') => 'base_cost',
            str_contains($normalized, 'physicalcontingencies') => 'physical_contingencies',
            str_contains($normalized, 'pricecontingencies') => 'price_contingencies',
            str_contains($normalized, 'netvalue') => 'net_value',
            $normalized === 'vat' => 'vat',
            str_contains($normalized, 'grandtotal') => 'grand_total',
            default => null,
        };
    }

    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $namespaces = $workbook->getNamespaces(true);
        $sheet = $workbook->sheets->sheet[0];
        $relationshipId = (string) $sheet->attributes($namespaces['r'])['id'];
        $relationships = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $target = 'worksheets/sheet1.xml';

        foreach ($relationships->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = (string) $relationship['Target'];
            }
        }

        $sheetXml = simplexml_load_string($zip->getFromName($this->worksheetPath($target)));
        $rows = [];

        foreach ($sheetXml->sheetData->row as $row) {
            $values = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->columnIndex($reference);
                $values[$columnIndex] = $this->cellValue($cell, $sharedStrings);
            }

            if ($values) {
                $max = max(array_keys($values));
                $filled = [];

                for ($index = 0; $index <= $max; $index++) {
                    $filled[] = $values[$index] ?? '';
                }

                $rows[] = $filled;
            }
        }

        $zip->close();

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if (! $xml) {
            return [];
        }

        $sharedStrings = [];
        $strings = simplexml_load_string($xml);

        foreach ($strings->si as $item) {
            if (isset($item->t)) {
                $sharedStrings[] = trim((string) $item->t);

                continue;
            }

            $text = '';

            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }

            $sharedStrings[] = trim($text);
        }

        return $sharedStrings;
    }

    private function worksheetPath(string $target): string
    {
        $target = str_replace('\\', '/', $target);

        if (str_starts_with($target, '/')) {
            return ltrim($target, '/');
        }

        if (str_starts_with($target, 'xl/')) {
            return $target;
        }

        return 'xl/'.$target;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $value = (string) $cell->v;

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return (string) $cell->is->t;
            }

            $text = '';

            foreach ($cell->is->r as $run) {
                $text .= (string) $run->t;
            }

            return $text;
        }

        return $value;
    }

    private function columnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeTankId(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^TANK[-\/]?\d{1,5}$/i', $value)) {
            preg_match('/(\d{1,5})$/', $value, $matches);

            return 'TANK-'.str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^\d{1,5}$/', $value)) {
            return 'TANK-'.str_pad($value, 4, '0', STR_PAD_LEFT);
        }

        return $value;
    }

    private function number(string|int|float|null $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return $normalized === '' ? null : (float) $normalized;
    }

    private function makeXlsx(array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'irdcrp-tanks-xlsx-');
        $zip = new ZipArchive;
        $zip->open($tempFile, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="Tank Registration" sheetId="1" r:id="rId1"/></sheets>
</workbook>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts>
<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF15803D"/><bgColor indexed="64"/></patternFill></fill></fills>
<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>
</styleSheet>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));
        $zip->close();

        $contents = file_get_contents($tempFile);
        unlink($tempFile);

        return $contents;
    }

    private function sheetXml(array $rows): string
    {
        $sheetRows = '';

        foreach ($rows as $rowIndex => $row) {
            $cellXml = '';

            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $cellXml .= '<c r="'.$reference.'" t="inlineStr"'.$style.'><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
            }

            $sheetRows .= '<row r="'.($rowIndex + 1).'">'.$cellXml.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetViews><sheetView workbookViewId="0"/></sheetViews>
<sheetFormatPr defaultRowHeight="18"/>
<sheetData>'.$sheetRows.'</sheetData>
</worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }
}
