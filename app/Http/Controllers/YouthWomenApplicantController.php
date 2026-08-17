<?php

namespace App\Http\Controllers;

use App\Models\YouthWomenApplicant;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class YouthWomenApplicantController extends Controller
{
    public const GENDERS = ['Male', 'Female'];

    public const LEGAL_STATUSES = [
        'Proprietor',
        'Partnership',
        'Private Limited Company (Pvt) Ltd',
        'Public Limited Company (PLC)',
        'Public Unlisted Company',
        'Other',
    ];

    public const BUSINESS_SECTORS = [
        'Agriculture',
        'Livestock',
        'Fisheries/ Aquaculture',
        'Plantation',
        'Spices',
        'Coconut',
        'Tea',
        'Rubber',
        'Ornamental Plants (Flowers/Foliage)',
        'Medicinal Products',
        'Food & Beverages',
        'Other',
    ];

    public const SCREENING_RESULTS = ['Selected', 'Reject', 'Option 3'];

    private const TEMPLATE_COLUMNS = [
        'Project Reference',
        'EOI Number',
        'Applicant Name',
        'Gender',
        'NIC',
        'Date of Birth',
        'Age as at 2026-01-01',
        'Telephone',
        'WhatsApp',
        'Email',
        'Business Name',
        'Legal Status',
        'Business Registered Address',
        'Province',
        'District',
        'DS Division',
        'Business Registration Number',
        'Business Registration Date',
        'Business Sectors',
        'Proposed Total Investment',
        'Initial Screening Result',
    ];

    public function overview(): View
    {
        $summaryQuery = YouthWomenApplicant::query();

        return view('youth-women.index', [
            'summary' => [
                'total' => (clone $summaryQuery)->count(),
                'selected' => (clone $summaryQuery)->where('initial_screening_result', 'Selected')->count(),
                'rejected' => (clone $summaryQuery)->where('initial_screening_result', 'Reject')->count(),
                'pending' => (clone $summaryQuery)
                    ->where(function ($query) {
                        $query
                            ->whereNull('initial_screening_result')
                            ->orWhereNotIn('initial_screening_result', ['Selected', 'Reject']);
                    })
                    ->count(),
                'investment' => (clone $summaryQuery)->sum('proposed_total_investment'),
            ],
            'genderCounts' => YouthWomenApplicant::query()
                ->select('gender', DB::raw('count(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender'),
            'provinceCounts' => YouthWomenApplicant::query()
                ->select('province', DB::raw('count(*) as total'))
                ->whereNotNull('province')
                ->groupBy('province')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'province'),
            'recentApplicants' => YouthWomenApplicant::query()->latest()->limit(5)->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $query = YouthWomenApplicant::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('eoi_number', 'like', "%{$search}%")
                    ->orWhere('applicant_name', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('business_registration_number', 'like', "%{$search}%");
            });
        }

        foreach (['gender', 'province', 'district', 'initial_screening_result'] as $filter) {
            if ($value = $request->string($filter)->trim()->toString()) {
                $query->where($filter, $value);
            }
        }

        $summaryQuery = YouthWomenApplicant::query();

        return view('business-information.index', [
            'applicants' => $query->latest()->paginate(15)->withQueryString(),
            'summary' => [
                'total' => (clone $summaryQuery)->count(),
                'selected' => (clone $summaryQuery)->where('initial_screening_result', 'Selected')->count(),
                'rejected' => (clone $summaryQuery)->where('initial_screening_result', 'Reject')->count(),
                'investment' => (clone $summaryQuery)->sum('proposed_total_investment'),
            ],
            'genderCounts' => YouthWomenApplicant::query()->select('gender', DB::raw('count(*) as total'))->groupBy('gender')->pluck('total', 'gender'),
            'screeningCounts' => YouthWomenApplicant::query()->select('initial_screening_result', DB::raw('count(*) as total'))->groupBy('initial_screening_result')->pluck('total', 'initial_screening_result'),
            'provinceCounts' => YouthWomenApplicant::query()->select('province', DB::raw('count(*) as total'))->whereNotNull('province')->groupBy('province')->orderByDesc('total')->limit(6)->pluck('total', 'province'),
            'districts' => YouthWomenApplicant::query()->whereNotNull('district')->distinct()->orderBy('district')->pluck('district'),
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'screeningResults' => self::SCREENING_RESULTS,
        ]);
    }

    public function create(): View
    {
        return view('business-information.create', [
            'applicant' => new YouthWomenApplicant,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        YouthWomenApplicant::create($this->validated($request));

        return redirect()->route('business-information.index')->with('status', 'Applicant record created successfully.');
    }

    public function edit(YouthWomenApplicant $businessInformation): View
    {
        return view('business-information.edit', [
            'applicant' => $businessInformation,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, YouthWomenApplicant $businessInformation): RedirectResponse
    {
        $businessInformation->update($this->validated($request, $businessInformation));

        return redirect()->route('business-information.index')->with('status', 'Applicant record updated successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'applicant_file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('applicant_file');
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'xlsx' => $this->readXlsx($file->getRealPath()),
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            default => throw \Illuminate\Validation\ValidationException::withMessages([
                'applicant_file' => 'Please upload a .xlsx or .csv file.',
            ]),
        };

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $headers = array_map(fn ($header) => $this->fieldForHeader((string) $header), array_shift($rows) ?? []);

        foreach ($rows as $row) {
            $payload = $this->payloadFromImportRow($headers, $row);

            if ($this->hasMissingRequiredImportFields($payload)) {
                $skipped++;
                continue;
            }

            $payload['imported_at'] = now();
            $existing = YouthWomenApplicant::where('eoi_number', $payload['eoi_number'])->first();
            YouthWomenApplicant::updateOrCreate(['eoi_number' => $payload['eoi_number']], $payload);
            $existing ? $updated++ : $imported++;
        }

        return redirect()
            ->route('business-information.index')
            ->with('status', "Import completed. {$imported} new, {$updated} updated, {$skipped} skipped.");
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::TEMPLATE_COLUMNS);
            fputcsv($handle, [
                'IRDCRP-C1.3',
                'EOI/YW/2026/0001',
                'Wanigasekara Arachchige Asela Kavinda',
                'Male',
                '200012345678',
                '2000-01-15',
                '25',
                '0712345678',
                '0712345678',
                'applicant@example.com',
                'ABC Export (Pvt) Ltd',
                'Private Limited Company (Pvt) Ltd',
                'No 01, Main Street',
                'Central',
                'Matale',
                'Dambulla',
                'BR-123',
                '2024-01-01',
                'Agriculture; Food & Beverages',
                '1500000',
                'Selected',
            ]);
            fclose($handle);
        }, 'youth-women-business-information-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function validated(Request $request, ?YouthWomenApplicant $applicant = null): array
    {
        $data = $request->validate([
            'project_reference' => ['nullable', 'string', 'max:255'],
            'eoi_number' => ['required', 'string', 'max:255', Rule::unique('youth_women_applicants')->ignore($applicant)],
            'applicant_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(self::GENDERS)],
            'nic' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'age_as_at_2026' => ['nullable', 'integer', 'min:0', 'max:120'],
            'telephone' => ['required', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'legal_status' => ['required', Rule::in(self::LEGAL_STATUSES)],
            'business_registered_address' => ['nullable', 'string'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ds_division' => ['nullable', 'string', 'max:255'],
            'business_registration_number' => ['nullable', 'string', 'max:255'],
            'business_registration_date' => ['nullable', 'date'],
            'business_sectors' => ['array'],
            'business_sectors.*' => ['string', 'max:255'],
            'proposed_total_investment' => ['required', 'numeric', 'min:0'],
            'initial_screening_result' => ['nullable', Rule::in(self::SCREENING_RESULTS)],
        ]);

        $data['business_sectors'] = $data['business_sectors'] ?? [];

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'genders' => self::GENDERS,
            'legalStatuses' => self::LEGAL_STATUSES,
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'businessSectors' => self::BUSINESS_SECTORS,
            'screeningResults' => self::SCREENING_RESULTS,
        ];
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

        $payload['eoi_number'] = $this->normalizeEoi($payload['eoi_number'] ?? '');
        $payload['date_of_birth'] = $this->normalizeDate($payload['date_of_birth'] ?? null);
        $payload['business_registration_date'] = $this->normalizeDate($payload['business_registration_date'] ?? null);
        $payload['age_as_at_2026'] = $payload['age_as_at_2026'] ?? $this->ageAsAt2026($payload['date_of_birth'] ?? null);
        $payload['business_sectors'] = $this->splitMultiValue($payload['business_sectors'] ?? '');
        $payload['proposed_total_investment'] = $this->money($payload['proposed_total_investment'] ?? 0);

        return array_filter($payload, fn ($value) => $value !== '' && $value !== null);
    }

    private function hasMissingRequiredImportFields(array $payload): bool
    {
        foreach (['eoi_number', 'applicant_name', 'gender', 'nic', 'telephone', 'business_name', 'legal_status'] as $field) {
            if (empty($payload[$field])) {
                return true;
            }
        }

        return false;
    }

    private function fieldForHeader(string $header): ?string
    {
        $normalized = preg_replace('/[^a-z0-9]+/', '', strtolower($header));

        return match (true) {
            str_contains($normalized, 'projectreference') => 'project_reference',
            str_contains($normalized, 'eoinumber') || str_contains($normalized, 'eoiyw2026') => 'eoi_number',
            str_contains($normalized, 'nameoftheapplicant') || str_contains($normalized, 'applicantname') => 'applicant_name',
            str_contains($normalized, 'gender') => 'gender',
            str_contains($normalized, 'nationalidentity') || str_contains($normalized, 'nic') => 'nic',
            str_contains($normalized, 'dateofbirth') || str_contains($normalized, 'dob') => 'date_of_birth',
            str_contains($normalized, 'age') => 'age_as_at_2026',
            str_contains($normalized, 'whatsapp') => 'whatsapp',
            str_contains($normalized, 'telephone') || str_contains($normalized, 'phone') => 'telephone',
            str_contains($normalized, 'email') => 'email',
            str_contains($normalized, 'nameofthebusiness') || str_contains($normalized, 'businessname') => 'business_name',
            str_contains($normalized, 'legalstatus') => 'legal_status',
            str_contains($normalized, 'registeredaddress') || str_contains($normalized, 'businessaddress') => 'business_registered_address',
            str_contains($normalized, 'province') => 'province',
            str_contains($normalized, 'district') && ! str_contains($normalized, 'secretariat') => 'district',
            str_contains($normalized, 'divisional') || str_contains($normalized, 'dsdivision') || str_contains($normalized, 'secretariat') => 'ds_division',
            str_contains($normalized, 'registrationnumber') => 'business_registration_number',
            str_contains($normalized, 'registrationdate') => 'business_registration_date',
            str_contains($normalized, 'businesssector') || str_contains($normalized, 'sector') => 'business_sectors',
            str_contains($normalized, 'investment') => 'proposed_total_investment',
            str_contains($normalized, 'screening') || str_contains($normalized, 'initialscore') => 'initial_screening_result',
            default => null,
        };
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        while (($row = fgetcsv($handle)) !== false) {
            if ($rows === [] && isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
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

        $sheetXml = simplexml_load_string($zip->getFromName('xl/'.$target));
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

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $value = (string) $cell->v;

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
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

    private function normalizeEoi(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^EOI\/YW\/2026\/\d{1,4}\/?$/i', $value)) {
            preg_match('/(\d{1,4})\/?$/', $value, $matches);

            return 'EOI/YW/2026/'.str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^\d{1,4}$/', $value)) {
            return 'EOI/YW/2026/'.str_pad($value, 4, '0', STR_PAD_LEFT);
        }

        return $value;
    }

    private function ageAsAt2026(?string $date): ?int
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->diffInYears(Carbon::create(2026, 1, 1));
    }

    private function splitMultiValue(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,;|]+/', $value))));
    }

    private function money(string|int|float $value): float
    {
        return (float) preg_replace('/[^0-9.]/', '', (string) $value);
    }
}
