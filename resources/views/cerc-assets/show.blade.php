<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC · Minor Tank</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $asset->tank_name }}</h1>
                <p class="mt-1 text-sm text-slate-500">Scheme details</p>
            </div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('cerc-assets.index') }}" class="tank-rehab-secondary">Back to Scheme Details</a><a href="{{ route('cerc-assets.pdf', $asset) }}" class="tank-rehab-secondary">Download full PDF</a><button type="button" onclick="window.print()" class="tank-rehab-secondary">Print page</button><a href="{{ route('cerc-assets.edit', $asset) }}" class="tank-rehab-action">Update scheme</a></div>
        </div>
    </x-slot>

    @php
        $detailSections = [
            'Scheme information' => [
                'Institution' => $asset->institution, 'Programme Type' => $asset->programme_type,
                'Contract Status' => $asset->contract_status, 'Scheme Name' => $asset->tank_name,
                'Category' => $asset->category, 'Year' => $asset->year, 'Size' => $asset->size,
                'Lot' => $asset->lot, 'Cluster' => $asset->cluster,
            ],
            'Estimated costs' => [
                'Civil Cost (LKR)' => $asset->civil_cost, 'Preliminaries (LKR)' => $asset->preliminaries_cost,
                'Direct Cost (LKR)' => $asset->direct_cost, 'VAT 18% (LKR)' => $asset->vat_18,
                'Contingencies (LKR)' => $asset->contingencies_cost, 'Total Cost Estimate (LKR)' => $asset->total_cost_estimate,
            ],
            'Award and contract' => [
                'Awarded Amount - Civil Cost (LKR)' => $asset->awarded_civil_cost,
                'Awarded Amount - Preliminaries (LKR)' => $asset->awarded_preliminaries_cost,
                'Awarded Amount without VAT & Contingencies (LKR)' => $asset->awarded_amount_without_vat_contingencies,
                'Advance / Imprest (LKR)' => $asset->advance_imprest, 'Provisional Sum (LKR)' => $asset->provisional_sum,
                'Date of Award' => $asset->date_of_award?->format('Y-m-d'),
                'Agreement Sign Date' => $asset->agreement_sign_date?->format('Y-m-d'),
                'Contract Period (Days)' => $asset->contract_period_days,
                'Completion Date' => $asset->completion_date?->format('Y-m-d'),
                'Contract Number' => $asset->contract_number, 'Contractor Name' => $asset->contractor_name,
                'Contractor Address' => $asset->contractor_address,
                'Contractor Contact Number' => $asset->contractor_contact_number,
            ],
            'Beneficiaries and command area' => [
                'Beneficiary Families' => $asset->beneficiary_families,
                'Command Area (Ac)' => $asset->command_area_ac,
            ],
            'Environmental and social clearances' => [
                'EMP Clearance Received Date' => $asset->emp_clearance_received_date?->format('Y-m-d'),
                'SMP Clearance Received Date' => $asset->smp_clearance_received_date?->format('Y-m-d'),
                'Date of ESMP Submitted to Procurement Division' => $asset->esmp_submitted_to_procurement_date?->format('Y-m-d'),
            ],
            'Scheme Details' => [
                'Province' => $asset->province, 'District' => $asset->district,
                'DSD' => $asset->ds_division, 'ASC' => $asset->asc,
            ],
        ];
        $display = fn ($value) => filled($value) ? $value : '—';
        $progressValue = filled($asset->progress) ? min(100, max(0, (float) $asset->progress)) : null;
        $photoCards = [
            'Before work photo' => $asset->before_work_photo_path,
            'Current photo 1' => $asset->current_photo_1_path,
            'Current photo 2' => $asset->current_photo_2_path,
            'Contractor information board photo' => $asset->contractor_board_photo_path,
        ];
        $currencyFields = ['Civil Cost (LKR)', 'Preliminaries (LKR)', 'Direct Cost (LKR)', 'VAT 18% (LKR)', 'Contingencies (LKR)', 'Total Cost Estimate (LKR)', 'Awarded Amount - Civil Cost (LKR)', 'Awarded Amount - Preliminaries (LKR)', 'Awarded Amount without VAT & Contingencies (LKR)', 'Advance / Imprest (LKR)', 'Provisional Sum (LKR)'];
    @endphp

    <section class="irrigation-module irrigation-detail-page cerc-minor-tank-detail px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))<div class="irrigation-notice">{{ session('status') }}</div>@endif
            <section class="irrigation-form-card irrigation-detail-card cerc-minor-map-card">
                <div class="mb-5 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">Sri Lanka location map</h2><p class="mt-1 text-sm text-slate-500">{{ collect([$asset->ds_division, $asset->district, $asset->province])->filter()->join(' · ') ?: 'Location not recorded' }}</p></div>
                @if ($locationPoint)
                    <p class="mb-3 text-xs text-slate-500">{{ ($locationPoint['source'] ?? null) === 'Exact GPS coordinates' ? 'Map pin is the saved scheme coordinate.' : 'Map pin marks an approximate district/province area. Update the record and click the exact scheme location to place a precise pin.' }}</p>
                    @if (isset($locationPoint['latitude'], $locationPoint['longitude']))<p class="mb-3 inline-flex rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">{{ ($locationPoint['source'] ?? null) === 'Exact GPS coordinates' ? 'GPS location' : 'Approximate area' }}: {{ number_format((float) $locationPoint['latitude'], 6) }}, {{ number_format((float) $locationPoint['longitude'], 6) }}</p>@endif
                    @include('tank-registration._sri-lanka-map', ['points' => [$locationPoint], 'selectedTankId' => $asset->id])
                @else
                    <p class="rounded-lg bg-slate-50 px-4 py-6 text-sm text-slate-500">Add a province or district to display this scheme on the map.</p>
                @endif
            </section>

            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 pb-4"><div><h2 class="text-lg font-semibold text-slate-900">Construction progress</h2><p class="mt-1 text-sm text-slate-500">Latest reported progress for this scheme.</p></div><span class="rounded-full bg-emerald-50 px-4 py-2 text-lg font-bold text-emerald-800">{{ $progressValue === null ? '—' : number_format($progressValue, 1).'%' }}</span></div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all" style="width: {{ $progressValue ?? 0 }}%"></div></div>
                @if ($progressValue === null)<p class="mt-2 text-xs text-slate-500">No progress value has been recorded yet.</p>@endif
            </section>

            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">Site photos</h2><p class="mt-1 text-sm text-slate-500">Before work, current site status, and contractor information board.</p></div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($photoCards as $label => $path)
                        <article class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                            <div class="flex min-h-14 items-center border-b border-slate-200 px-4 py-3"><h3 class="text-sm font-semibold text-slate-800">{{ $label }}</h3></div>
                            @if ($path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" target="_blank" class="group block"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt="{{ $label }}" class="h-56 w-full object-cover transition duration-200 group-hover:scale-[1.02]"><span class="block px-4 py-3 text-xs font-semibold text-emerald-800">Open full photo ↗</span></a>
                            @else
                                <div class="flex h-56 items-center justify-center px-5 text-center text-sm text-slate-400">No photo uploaded</div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            @foreach ($detailSections as $sectionTitle => $fields)
                <section class="irrigation-form-card irrigation-detail-card">
                    <div class="mb-5 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">{{ $sectionTitle }}</h2></div>
                    <dl class="irrigation-detail-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($fields as $label => $value)
                            <div class="irrigation-detail-field"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900">@if (in_array($label, $currencyFields, true) && filled($value))LKR {{ number_format((float) $value, 2) }}@else{{ $display($value) }}@endif</dd></div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    </section>
    @push('head')
        <style>
            .cerc-minor-map-card .tank-leaflet-map { height: 300px !important; min-height: 0 !important; }
            @media print {
                @page { size: A4 portrait; margin: 12mm; }
                body * { visibility: hidden !important; }
                .cerc-minor-tank-detail, .cerc-minor-tank-detail * { visibility: visible !important; }
                .cerc-minor-tank-detail { position: absolute !important; inset: 0 auto auto 0 !important; width: 100% !important; padding: 0 !important; background: #fff !important; }
                .cerc-minor-tank-detail .tank-leaflet-map { height: 190px !important; }
                .cerc-minor-tank-detail .irrigation-form-card { break-inside: avoid; box-shadow: none !important; }
                .cerc-minor-tank-detail img { max-height: 150px !important; }
            }
        </style>
    @endpush
</x-app-layout>
