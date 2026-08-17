<?php

namespace App\Http\Controllers;

use App\Models\ProductivePartnershipEoi;
use App\Models\ProductivePartnershipEoiInterviewHistory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ProductivePartnershipEoiController extends Controller
{
    private const TEMPLATE_COLUMNS = [
        'EOI Number',
        '2.1 Name of the Organization',
        '2.2 Number of Members',
        '2.3 Legal Status',
        '2.4 Place of Registration',
        '2.5.1 Registration Number',
        '2.5.2 Registration Date',
        '2.6 Name of the Contact Person',
        '2.7 Designation of the Contact Person',
        '2.8 Telephone Number of the Contact Person',
        '2.9 Registered Address of the Organization',
        '2.10 email Address of the Contact Person',
        '2.11 Proposed Business Location Address',
        'Province',
        'District',
        'Divisional_Secretariat_DS_Division',
        '3.1 Title of the Business Proposal',
        '3.2 Sector',
        '4.7 Proposed Total Investment (Rs)',
        '4.7.1 Expected Grant from IRDCRP (Rs.)',
        '5.1 Completeness of Application with Mandatory Requirement',
        '5.1.2 Status after Initial Desk Review',
        '5.1.3 Date of Initial Screening',
        '_id',
        '_uuid',
        '_submission_time',
        '_validation_status',
        '_notes',
        '_status',
    ];

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $stageQuery = ProductivePartnershipEoi::query();

        return view('productive-partnership-eois.index', [
            'eois' => $query->latest()->paginate(15)->withQueryString(),
            'summary' => $this->summary(),
            'periodSummary' => $this->periodSummary($stageQuery),
            'stageCounts' => ProductivePartnershipEoi::query()
                ->select('initial_stage', DB::raw('count(*) as total'))
                ->groupBy('initial_stage')
                ->pluck('total', 'initial_stage'),
            'reviewStatusCounts' => ProductivePartnershipEoi::query()
                ->select('initial_desk_review_status', DB::raw('count(*) as total'))
                ->groupBy('initial_desk_review_status')
                ->pluck('total', 'initial_desk_review_status'),
            'provinceCounts' => $this->provinceCounts(),
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'reviewStatuses' => ProductivePartnershipEoi::query()
                ->whereNotNull('initial_desk_review_status')
                ->distinct()
                ->orderBy('initial_desk_review_status')
                ->pluck('initial_desk_review_status'),
            'eoiYears' => ProductivePartnershipEoi::query()
                ->whereNotNull('eoi_year')
                ->distinct()
                ->orderByDesc('eoi_year')
                ->pluck('eoi_year'),
            'eoiCallNumbers' => $this->callNumbersForYear($request),
        ]);
    }

    public function reviewedInterviews(Request $request): View
    {
        $query = $this->filteredQuery($request)->where('initial_stage', true);
        $this->applyReviewedInterviewFilters($query, $request);
        $stageQuery = clone $query;

        return view('productive-partnership-eois.reviewed-interviews', [
            'eois' => $query->latest()->paginate(15)->withQueryString(),
            'interviewStatuses' => ProductivePartnershipEoi::INTERVIEW_STATUSES,
            'summary' => [
                'total' => (clone $stageQuery)->count(),
                'passed' => (clone $stageQuery)->where('interview_marks', '>', 50)->count(),
                'not_passed' => (clone $stageQuery)->whereNotNull('interview_marks')->where('interview_marks', '<=', 50)->count(),
            ],
            'interviewStatusCounts' => (clone $stageQuery)
                ->select('interview_status', DB::raw('count(*) as total'))
                ->groupBy('interview_status')
                ->pluck('total', 'interview_status'),
            'nextStepSummary' => [
                'verification' => (clone $stageQuery)->where('interview_marks', '>', 50)->count(),
                'not_passed' => (clone $stageQuery)->whereNotNull('interview_marks')->where('interview_marks', '<=', 50)->count(),
                'pending' => (clone $stageQuery)->whereNull('interview_marks')->count(),
            ],
            'periodSummary' => $this->periodSummary($stageQuery),
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'eoiYears' => ProductivePartnershipEoi::query()
                ->whereNotNull('eoi_year')
                ->distinct()
                ->orderByDesc('eoi_year')
                ->pluck('eoi_year'),
            'eoiCallNumbers' => $this->callNumbersForYear($request),
        ]);
    }

    public function fieldVisits(Request $request): View
    {
        $query = $this->filteredQuery($request)->where('verification_stage', true);
        $this->applyInterviewStatusFilter($query, $request);

        if ($status = $request->string('verification_status')->trim()->toString()) {
            $query->where('verification_status', $status);
        }

        $stageQuery = clone $query;

        return view('productive-partnership-eois.field-visits', [
            'eois' => $query->latest()->paginate(15)->withQueryString(),
            'summary' => [
                'total' => (clone $stageQuery)->count(),
                'pending' => (clone $stageQuery)->where('verification_status', 'pending')->count(),
                'approved' => (clone $stageQuery)->where('verification_status', 'approved')->count(),
                'not_approved' => (clone $stageQuery)->where('verification_status', 'not_approved')->count(),
            ],
            'periodSummary' => $this->periodSummary($stageQuery),
            'verificationStatuses' => [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'not_approved' => 'Not Approved',
            ],
            'interviewStatuses' => ProductivePartnershipEoi::INTERVIEW_STATUSES,
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
            'eoiYears' => ProductivePartnershipEoi::query()
                ->whereNotNull('eoi_year')
                ->distinct()
                ->orderByDesc('eoi_year')
                ->pluck('eoi_year'),
            'eoiCallNumbers' => $this->callNumbersForYear($request),
        ]);
    }

    public function updateInterviewMarks(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        $data = $request->validate([
            'interview_marks' => ['required', 'numeric', 'min:0', 'max:100'],
            'interview_status' => ['required', Rule::in(array_keys(ProductivePartnershipEoi::INTERVIEW_STATUSES))],
            'interview_notes' => ['nullable', 'string'],
        ]);

        $previousInterviewStatus = $eoi->interview_status;
        $passedInterview = (float) $data['interview_marks'] > 50;
        $data['verification_stage'] = $passedInterview;

        if (($data['interview_status'] ?? null) === 'resubmit' && ! $eoi->interview_resubmitted_at) {
            $data['interview_resubmitted_at'] = now();
        }

        if ($passedInterview) {
            $data['verification_status'] = $eoi->verification_status === 'approved'
                ? 'approved'
                : 'pending';
        } else {
            $data['verification_status'] = 'not_approved';
            $data['approved_at'] = null;
            $data['verified_at'] = now();
        }

        $eoi->update($data);

        ProductivePartnershipEoiInterviewHistory::create([
            'productive_partnership_eoi_id' => $eoi->id,
            'from_interview_status' => $previousInterviewStatus,
            'to_interview_status' => $data['interview_status'],
            'interview_marks' => $data['interview_marks'],
            'interview_notes' => $data['interview_notes'] ?? null,
            'changed_by_user_id' => $request->user()?->id,
            'changed_at' => now(),
        ]);

        if ($passedInterview) {
            return redirect()
                ->route('field-visits.index')
                ->with('status', 'Interview passed. EOI moved to Selected For Verification Field Visit Pass.');
        }

        return back()->with('status', 'Interview marks updated successfully.');
    }

    public function updateVerificationStatus(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        abort_unless($eoi->verification_stage, 404);

        $data = $request->validate([
            'verification_status' => ['required', 'in:approved,not_approved,pending'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        $data['approved_at'] = $data['verification_status'] === 'approved' ? ($eoi->approved_at ?? now()) : null;
        $data['verified_at'] = $data['verification_status'] === 'pending' ? null : now();

        $eoi->update($data);

        if ($data['verification_status'] === 'approved') {
            return redirect()
                ->route('approved-farmer-producer-groups.index')
                ->with('status', 'Field visit approved. EOI moved to Approved Farmer Producer Groups.');
        }

        return back()->with('status', 'Verification field visit status updated successfully.');
    }

    public function approved(Request $request): View
    {
        $query = $this->filteredQuery($request)->where('verification_status', 'approved');
        $stageQuery = ProductivePartnershipEoi::query()->where('verification_status', 'approved');

        return view('productive-partnership-eois.approved', [
            'eois' => $query->latest('approved_at')->paginate(15)->withQueryString(),
            'summary' => [
                'total' => (clone $stageQuery)->count(),
                'documents' => (clone $stageQuery)
                    ->whereNotNull('business_registration_image')
                    ->whereNotNull('business_proposal_pdf')
                    ->count(),
                'agreement_signed' => (clone $stageQuery)
                    ->whereNotNull('fop_agreement_tracking->agreement_sign->date')
                    ->count(),
            ],
            'periodSummary' => $this->periodSummary($stageQuery),
        ]);
    }

    public function editApproved(ProductivePartnershipEoi $eoi): View
    {
        abort_unless($eoi->verification_status === 'approved', 404);

        return view('productive-partnership-eois.approved-edit', [
            'eoi' => $eoi,
        ]);
    }

    public function updateApproved(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        abort_unless($eoi->verification_status === 'approved', 404);

        $data = $request->validate([
            'pre_construction_date' => ['nullable', 'date'],
            'pre_construction_notes' => ['nullable', 'string'],
            'pre_construction_images' => ['array'],
            'pre_construction_images.*' => ['image', 'max:5120'],
            'during_construction_date' => ['nullable', 'date'],
            'during_construction_notes' => ['nullable', 'string'],
            'during_construction_images' => ['array'],
            'during_construction_images.*' => ['image', 'max:5120'],
            'post_construction_date' => ['nullable', 'date'],
            'post_construction_notes' => ['nullable', 'string'],
            'post_construction_images' => ['array'],
            'post_construction_images.*' => ['image', 'max:5120'],
            'business_registration_image' => ['nullable', 'image', 'max:5120'],
            'business_proposal_pdf' => ['nullable', 'mimes:pdf', 'max:10240'],
        ]);

        foreach (['pre_construction_images', 'during_construction_images', 'post_construction_images'] as $field) {
            $data[$field] = [
                ...($eoi->{$field} ?? []),
                ...$this->storeUploads($request, $field, 'productive-partnership/'.$eoi->id.'/'.$field),
            ];
        }

        if ($request->hasFile('business_registration_image')) {
            $data['business_registration_image'] = $request->file('business_registration_image')
                ->store('productive-partnership/'.$eoi->id.'/business-registration', 'public');
        }

        if ($request->hasFile('business_proposal_pdf')) {
            $data['business_proposal_pdf'] = $request->file('business_proposal_pdf')
                ->store('productive-partnership/'.$eoi->id.'/business-proposal', 'public');
        }

        $eoi->update($data);

        return redirect()->route('approved-farmer-producer-groups.index')->with('status', 'Approved farmer producer group updated successfully.');
    }

    public function updateAgreementTracking(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        abort_unless($eoi->verification_status === 'approved', 404);

        $request->validate([
            'fop_agreement_tracking' => ['array'],
            'fop_agreement_tracking.*.date' => ['nullable', 'date'],
            'fop_agreement_tracking.*.value' => ['nullable', 'string', 'max:255'],
        ]);

        $input = $request->input('fop_agreement_tracking', []);
        $tracking = [];

        foreach (ProductivePartnershipEoi::AGREEMENT_TRACKING_STAGES as $key => $label) {
            $date = $input[$key]['date'] ?? null;
            $value = trim((string) ($input[$key]['value'] ?? ''));

            if (! filled($date) && ! filled($value)) {
                continue;
            }

            $tracking[$key] = [
                'date' => filled($date) ? $date : null,
                'value' => filled($value) ? $value : null,
            ];
        }

        $eoi->update([
            'fop_agreement_tracking' => $tracking ?: null,
        ]);

        return back()->with('status', 'FOP agreement tracking updated successfully.');
    }

    public function fullProposals(Request $request): View
    {
        $query = ProductivePartnershipEoi::query()->where('verification_status', 'approved');
        $stageQuery = ProductivePartnershipEoi::query()->where('verification_status', 'approved');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('eoi_number', 'like', "%{$search}%")
                    ->orWhere('organization_name', 'like', "%{$search}%")
                    ->orWhere('business_proposal_title', 'like', "%{$search}%")
                    ->orWhere('contact_person_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('full_proposal_status')->trim()->toString()) {
            $query->where('full_proposal_status', $status);
        }

        return view('productive-partnership-eois.full-proposals', [
            'eois' => $query->latest('approved_at')->paginate(15)->withQueryString(),
            'summary' => [
                'total' => (clone $stageQuery)->count(),
                'pending' => (clone $stageQuery)->where('full_proposal_status', 'pending')->count(),
                'approved' => (clone $stageQuery)->where('full_proposal_status', 'approved')->count(),
                'rejected' => (clone $stageQuery)->where('full_proposal_status', 'rejected')->count(),
            ],
            'periodSummary' => $this->periodSummary($stageQuery),
            'statuses' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'],
        ]);
    }

    public function agreementSignFop(Request $request): View
    {
        $query = ProductivePartnershipEoi::query()
            ->where('verification_status', 'approved')
            ->where('full_proposal_status', 'approved');
        $stageQuery = ProductivePartnershipEoi::query()
            ->where('verification_status', 'approved')
            ->where('full_proposal_status', 'approved');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('eoi_number', 'like', "%{$search}%")
                    ->orWhere('organization_name', 'like', "%{$search}%")
                    ->orWhere('business_proposal_title', 'like', "%{$search}%")
                    ->orWhere('contact_person_name', 'like', "%{$search}%");
            });
        }

        return view('productive-partnership-eois.agreement-sign-fop', [
            'eois' => $query->latest('full_proposal_reviewed_at')->paginate(15)->withQueryString(),
            'summary' => [
                'total' => (clone $stageQuery)->count(),
                'documents' => (clone $stageQuery)
                    ->whereNotNull('business_registration_image')
                    ->whereNotNull('business_proposal_pdf')
                    ->count(),
                'pending_documents' => (clone $stageQuery)
                    ->where(function ($builder) {
                        $builder
                            ->whereNull('business_registration_image')
                            ->orWhereNull('business_proposal_pdf');
                    })
                    ->count(),
            ],
            'periodSummary' => $this->periodSummary($stageQuery),
        ]);
    }

    public function updateFullProposalStatus(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        abort_unless($eoi->verification_status === 'approved', 404);

        $data = $request->validate([
            'full_proposal_status' => ['required', 'in:approved,rejected,pending'],
            'full_proposal_notes' => ['nullable', 'string'],
        ]);

        $data['full_proposal_reviewed_at'] = $data['full_proposal_status'] === 'pending' ? null : now();

        $eoi->update($data);

        return back()->with('status', 'Full proposal status updated successfully.');
    }

    public function show(ProductivePartnershipEoi $eoi): View
    {
        $eoi->load(['interviewHistories.changedBy']);

        return view('productive-partnership-eois.show', [
            'eoi' => $eoi,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'eoi_file' => ['required', 'file', 'mimes:xlsx', 'max:15360'],
        ]);

        $file = $request->file('eoi_file');
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'eoi_file' => 'Please upload a .xlsx Excel file.',
            ]),
        };

        $headers = array_map(fn ($header) => $this->fieldForHeader((string) $header), array_shift($rows) ?? []);
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $payload = $this->payloadFromImportRow($headers, $row);

            if (empty($payload['eoi_number'])) {
                $skipped++;
                continue;
            }

            $payload['imported_at'] = now();
            $existing = ProductivePartnershipEoi::where('eoi_number', $payload['eoi_number'])->first();
            ProductivePartnershipEoi::updateOrCreate(['eoi_number' => $payload['eoi_number']], $payload);
            $existing ? $updated++ : $imported++;
        }

        return redirect()
            ->route('selected-eois.index')
            ->with('status', "EOI import completed. {$imported} new, {$updated} updated, {$skipped} skipped.");
    }

    public function updateInitialStage(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        $data = $request->validate([
            'initial_stage' => ['required', 'boolean'],
        ]);

        $eoi->update([
            'initial_stage' => (bool) $data['initial_stage'],
            ...((bool) $data['initial_stage'] ? [] : [
                'verification_stage' => false,
                'verification_status' => 'pending',
                'approved_at' => null,
                'verified_at' => null,
            ]),
        ]);

        return back()->with('status', 'Initial Stage updated successfully.');
    }

    public function destroy(ProductivePartnershipEoi $eoi): RedirectResponse
    {
        $eoi->delete();

        return redirect()->route('selected-eois.index')->with('status', 'EOI record deleted successfully.');
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo $this->makeXlsx([self::TEMPLATE_COLUMNS]);
        }, 'component-1-2-selected-all-eoi-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'component-1-2-selected-all-eoi-export.xlsx';
        $eois = $this->filteredQuery($request)->latest()->get();
        $rows = [[...self::TEMPLATE_COLUMNS, 'Initial Stage']];

        foreach ($eois as $eoi) {
            $rows[] = [
                $eoi->eoi_number,
                $eoi->organization_name,
                $eoi->number_of_members,
                $eoi->legal_status,
                $eoi->place_of_registration,
                $eoi->registration_number,
                optional($eoi->registration_date)->format('Y-m-d'),
                $eoi->contact_person_name,
                $eoi->contact_person_designation,
                $eoi->contact_person_telephone,
                $eoi->organization_registered_address,
                $eoi->contact_person_email,
                $eoi->proposed_business_location_address,
                $eoi->province,
                $eoi->district,
                $eoi->ds_division,
                $eoi->business_proposal_title,
                $eoi->sector,
                $eoi->proposed_total_investment,
                $eoi->expected_grant_irdcrp,
                $eoi->completeness_mandatory_requirement,
                $eoi->initial_desk_review_status,
                optional($eoi->initial_screening_date)->format('Y-m-d'),
                $eoi->kobo_id,
                $eoi->kobo_uuid,
                optional($eoi->submission_time)->format('Y-m-d H:i:s'),
                $eoi->validation_status,
                $eoi->notes,
                $eoi->status,
                $eoi->initial_stage ? 'Yes' : 'No',
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            echo $this->makeXlsx($rows);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = ProductivePartnershipEoi::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('eoi_number', 'like', "%{$search}%")
                    ->orWhere('organization_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('contact_person_name', 'like', "%{$search}%")
                    ->orWhere('contact_person_telephone', 'like', "%{$search}%")
                    ->orWhere('business_proposal_title', 'like', "%{$search}%");
            });
        }

        foreach (['province', 'district', 'sector', 'initial_desk_review_status'] as $filter) {
            if ($value = $request->string($filter)->trim()->toString()) {
                $query->where($filter, $value);
            }
        }

        if ($request->filled('eoi_year')) {
            $query->where('eoi_year', $request->integer('eoi_year'));
        }

        if ($request->filled('eoi_call_number')) {
            $query->where('eoi_call_number', $request->integer('eoi_call_number'));
        }

        if ($request->filled('initial_stage')) {
            $query->where('initial_stage', $request->boolean('initial_stage'));
        }

        return $query;
    }

    private function applyReviewedInterviewFilters($query, Request $request): void
    {
        $this->applyInterviewStatusFilter($query, $request);

        match ($request->string('next_step')->trim()->toString()) {
            'verification' => $query->where('interview_marks', '>', 50),
            'not_passed' => $query->whereNotNull('interview_marks')->where('interview_marks', '<=', 50),
            'pending' => $query->whereNull('interview_marks'),
            default => null,
        };
    }

    private function applyInterviewStatusFilter($query, Request $request): void
    {
        if ($status = $request->string('interview_status')->trim()->toString()) {
            $query->where('interview_status', $status);
        }
    }

    private function callNumbersForYear(Request $request)
    {
        return ProductivePartnershipEoi::query()
            ->when(
                $request->filled('eoi_year'),
                fn ($query) => $query->where('eoi_year', $request->integer('eoi_year'))
            )
            ->whereNotNull('eoi_call_number')
            ->distinct()
            ->orderBy('eoi_call_number')
            ->pluck('eoi_call_number');
    }

    private function summary(): array
    {
        return [
            'total' => ProductivePartnershipEoi::count(),
            'initial_yes' => ProductivePartnershipEoi::where('initial_stage', true)->count(),
            'initial_no' => ProductivePartnershipEoi::where('initial_stage', false)->count(),
        ];
    }

    private function periodSummary($query): array
    {
        return [
            'years' => (clone $query)
                ->select('eoi_year', DB::raw('count(*) as total'))
                ->whereNotNull('eoi_year')
                ->groupBy('eoi_year')
                ->orderByDesc('eoi_year')
                ->get(),
            'calls' => (clone $query)
                ->select('eoi_year', 'eoi_call_number', DB::raw('count(*) as total'))
                ->whereNotNull('eoi_year')
                ->groupBy('eoi_year', 'eoi_call_number')
                ->orderByDesc('eoi_year')
                ->orderBy('eoi_call_number')
                ->get(),
        ];
    }

    private function provinceCounts()
    {
        $configuredProvinces = array_keys(config('admin_divisions.provinces', []));
        $counts = ProductivePartnershipEoi::query()
            ->select('province', DB::raw('count(*) as total'))
            ->whereNotNull('province')
            ->groupBy('province')
            ->pluck('total', 'province');

        $normalizedCounts = $counts->reduce(function (array $carry, int $total, string $province) {
            $key = $this->normalizeProvinceName($province);
            $carry[$key] = ($carry[$key] ?? 0) + $total;

            return $carry;
        }, []);

        return collect($configuredProvinces)
            ->mapWithKeys(fn (string $province) => [
                $province => $normalizedCounts[$this->normalizeProvinceName($province)] ?? 0,
            ]);
    }

    private function normalizeProvinceName(string $province): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($province));
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

        foreach (['registration_date', 'initial_screening_date'] as $field) {
            $payload[$field] = $this->normalizeDate($payload[$field] ?? null);
        }

        $payload['submission_time'] = $this->normalizeDateTime($payload['submission_time'] ?? null);
        $payload['number_of_members'] = $this->integer($payload['number_of_members'] ?? null);
        $payload['proposed_total_investment'] = $this->money($payload['proposed_total_investment'] ?? 0);
        $payload['expected_grant_irdcrp'] = $this->money($payload['expected_grant_irdcrp'] ?? 0);

        return array_filter($payload, fn ($value) => $value !== '' && $value !== null);
    }

    private function fieldForHeader(string $header): ?string
    {
        $normalized = preg_replace('/[^a-z0-9_]+/', '', strtolower($header));

        return match (true) {
            str_contains($normalized, 'eoinumber') => 'eoi_number',
            str_contains($normalized, 'nameoftheorganization') || str_contains($normalized, 'organizationname') => 'organization_name',
            str_contains($normalized, 'numberofmembers') => 'number_of_members',
            str_contains($normalized, 'legalstatus') => 'legal_status',
            str_contains($normalized, 'placeofregistration') => 'place_of_registration',
            str_contains($normalized, 'registrationnumber') => 'registration_number',
            str_contains($normalized, 'registrationdate') => 'registration_date',
            str_contains($normalized, 'nameofthecontactperson') || str_contains($normalized, 'contactpersonname') => 'contact_person_name',
            str_contains($normalized, 'designationofthecontactperson') || str_contains($normalized, 'contactpersondesignation') => 'contact_person_designation',
            str_contains($normalized, 'telephonenumberofthecontactperson') || str_contains($normalized, 'contactpersontelephone') => 'contact_person_telephone',
            str_contains($normalized, 'registeredaddressoftheorganization') => 'organization_registered_address',
            str_contains($normalized, 'emailaddressofthecontactperson') || str_contains($normalized, 'contactpersonemail') => 'contact_person_email',
            str_contains($normalized, 'proposedbusinesslocationaddress') => 'proposed_business_location_address',
            str_contains($normalized, 'province') => 'province',
            str_contains($normalized, 'district') && ! str_contains($normalized, 'secretariat') => 'district',
            str_contains($normalized, 'divisional_secretariat_ds_division') || str_contains($normalized, 'dsdivision') || str_contains($normalized, 'secretariat') => 'ds_division',
            str_contains($normalized, 'titleofthebusinessproposal') || str_contains($normalized, 'businessproposaltitle') => 'business_proposal_title',
            str_contains($normalized, 'sector') => 'sector',
            str_contains($normalized, 'proposedtotalinvestment') => 'proposed_total_investment',
            str_contains($normalized, 'expectedgrant') || str_contains($normalized, 'irdcrp') => 'expected_grant_irdcrp',
            str_contains($normalized, 'completenessofapplication') || str_contains($normalized, 'mandatoryrequirement') => 'completeness_mandatory_requirement',
            str_contains($normalized, 'statusafterinitialdeskreview') || str_contains($normalized, 'deskreview') => 'initial_desk_review_status',
            str_contains($normalized, 'dateofinitialscreening') || str_contains($normalized, 'initialscreeningdate') => 'initial_screening_date',
            $normalized === '_id' || $normalized === 'id' => 'kobo_id',
            $normalized === '_uuid' || $normalized === 'uuid' => 'kobo_uuid',
            str_contains($normalized, 'submission_time') || str_contains($normalized, 'submissiontime') => 'submission_time',
            str_contains($normalized, 'validation_status') || str_contains($normalized, 'validationstatus') => 'validation_status',
            str_contains($normalized, 'notes') => 'notes',
            $normalized === '_status' || $normalized === 'status' => 'status',
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

    private function normalizeDateTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateTimeString();
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function money(string|int|float|null $value): float
    {
        return (float) preg_replace('/[^0-9.]/', '', (string) $value);
    }

    private function integer(string|int|null $value): ?int
    {
        $value = preg_replace('/[^0-9]/', '', (string) $value);

        return $value === '' ? null : (int) $value;
    }

    private function storeUploads(Request $request, string $field, string $directory): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }

        return collect($request->file($field))
            ->map(fn ($file) => $file->store($directory, 'public'))
            ->all();
    }

    private function makeXlsx(array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'irdcrp-xlsx-');
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
<sheets><sheet name="Selected All EOI" sheetId="1" r:id="rId1"/></sheets>
</workbook>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>
<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>
<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>
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
<sheetFormatPr defaultRowHeight="15"/>
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
