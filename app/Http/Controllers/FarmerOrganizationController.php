<?php

namespace App\Http\Controllers;

use App\Models\ProductivePartnershipEoi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FarmerOrganizationController extends ProductivePartnershipEoiController
{
    private function screenedQuery(string $status)
    {
        $query = ProductivePartnershipEoi::query();
        return match ($status) {
            'Selected' => $query->where('initial_stage', true),
            'Reject' => $query->where('initial_stage', false)->whereIn('initial_desk_review_status', ['Reject', 'Rejected']),
            'Pending' => $query->where('initial_stage', false)->where(fn ($q) => $q
                ->whereNull('initial_desk_review_status')->orWhereNotIn('initial_desk_review_status', ['Reject', 'Rejected'])),
            default => $query,
        };
    }

    private function summaries(): array
    {
        return [
            'summary' => [
                'total' => ProductivePartnershipEoi::count(),
                'selected' => $this->screenedQuery('Selected')->count(),
                'rejected' => $this->screenedQuery('Reject')->count(),
                'pending' => $this->screenedQuery('Pending')->count(),
                'investment' => ProductivePartnershipEoi::sum('proposed_total_investment'),
            ],
            'legalStatusCounts' => ProductivePartnershipEoi::selectRaw('legal_status, count(*) as total')->groupBy('legal_status')->pluck('total', 'legal_status'),
            'provinceCounts' => ProductivePartnershipEoi::selectRaw('province, count(*) as total')->whereNotNull('province')->groupBy('province')->orderByDesc('total')->limit(6)->pluck('total', 'province'),
        ];
    }

    public function overview(): View
    {
        return view('farmer-organizations.overview', [
            ...$this->summaries(),
            'recentApplicants' => ProductivePartnershipEoi::latest()->limit(5)->get(),
        ]);
    }

    public function records(Request $request): View
    {
        $query = $this->screenedQuery($request->string('initial_screening_result')->toString());
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                foreach (['eoi_number', 'organization_name', 'registration_number', 'contact_person_name', 'contact_person_telephone', 'business_proposal_title'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
        foreach (['legal_status', 'province', 'district'] as $field) {
            if ($value = $request->string($field)->trim()->toString()) {
                $query->where($field, $value);
            }
        }
        $summaries = $this->summaries();
        return view('farmer-organizations.records', [
            ...$summaries,
            'applicants' => $query->latest()->paginate(15)->withQueryString(),
            'screeningCounts' => ['Selected' => $summaries['summary']['selected'], 'Reject' => $summaries['summary']['rejected'], 'Pending' => $summaries['summary']['pending']],
            'screeningResults' => ['Selected', 'Reject', 'Pending'],
            'administrativeDivisions' => config('admin_divisions.provinces', []),
            'provinces' => array_keys(config('admin_divisions.provinces', [])),
        ]);
    }

    public function create(): View
    {
        return view('farmer-organizations.form', [...parent::create()->getData(), 'eoi' => new ProductivePartnershipEoi]);
    }

    public function edit(ProductivePartnershipEoi $eoi): View
    {
        return view('farmer-organizations.form', [...parent::create()->getData(), 'eoi' => $eoi]);
    }

    private function screenedData(Request $request, ?ProductivePartnershipEoi $eoi = null): array
    {
        $request->validate(['screening_result' => ['required', Rule::in(['Selected', 'Reject', 'Pending'])]]);
        $data = $this->validatedSingleEntry($request, $eoi);
        $selected = $request->input('screening_result') === 'Selected';
        $data['initial_desk_review_status'] = $request->input('screening_result');
        $data['initial_stage'] = $selected;
        if ($eoi?->initial_stage && ! $selected) {
            $data += ['verification_stage' => false, 'verification_status' => 'pending', 'approved_at' => null, 'verified_at' => null];
        }
        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        ProductivePartnershipEoi::create($this->screenedData($request));
        return redirect()->route('farmer-organizations.records')->with('status', 'Farmer group created successfully.');
    }

    public function update(Request $request, ProductivePartnershipEoi $eoi): RedirectResponse
    {
        $eoi->update($this->screenedData($request, $eoi));
        return redirect()->route('farmer-organizations.records')->with('status', 'Farmer group updated successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        return parent::import($request)->setTargetUrl(route('farmer-organizations.records'));
    }

    protected function importExtensions(): array
    {
        return ['xlsx', 'csv', 'txt'];
    }

    protected function prepareImportedPayload(array $payload, ?ProductivePartnershipEoi $existing): array
    {
        $status = $payload['initial_desk_review_status'] ?? null;
        if (in_array($status, ['Selected', 'Reject', 'Rejected', 'Pending'], true)) {
            $payload['initial_stage'] = $status === 'Selected';
            if ($existing?->initial_stage && ! $payload['initial_stage']) {
                $payload += ['verification_stage' => false, 'verification_status' => 'pending', 'approved_at' => null, 'verified_at' => null];
            }
        }
        return $payload;
    }
}
