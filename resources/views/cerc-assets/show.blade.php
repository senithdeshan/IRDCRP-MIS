<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC · Record details</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $asset->tank_name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $asset->tank_registration_number ?: 'No registration number' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('cerc-assets.index', ['tank_type' => $tankType]) }}" class="tank-rehab-secondary">Back to records</a>
                <a href="{{ route('cerc-assets.edit', [$asset, 'tank_type' => $tankType]) }}" class="tank-rehab-action">Edit record</a>
            </div>
        </div>
    </x-slot>

    @php
        $display = fn ($value) => filled($value) ? $value : '—';
        $schemeFields = [
            'Tank Registration Number' => $asset->tank_registration_number,
            'Tank Name' => $asset->tank_name,
            'River Basin' => $asset->river_basin,
            'River Basin Number' => $asset->river_basin_number,
            'Tank Cascade Name' => $asset->cascade_name,
            'Province' => $asset->province,
            'District' => $asset->district,
            'DS Division' => $asset->ds_division,
            'ASC' => $asset->asc,
            'GN Division' => $asset->gn_division,
            'GN Code' => $asset->gn_code,
            'GN Division Families' => $asset->gn_division_families,
            'Tank Capacity (ac.ft)' => $asset->tank_capacity_ac_ft,
            'Catchment Area (ha)' => $asset->catchment_area_ha,
            'Existing Irrigable Area (ha)' => $asset->irrigable_area_existing_ha,
            'Cultivated Irrigable Area (ha)' => $asset->irrigable_area_cultivated_ha,
            'Main Crop Cultivation' => $asset->main_crop_cultivation,
            'Farmer Families in Tank' => $asset->tank_farmer_families,
            'Farmer Families in Farmer Organization' => $asset->farmer_organization_families,
            'Tank Scheme Type' => $asset->scheme_type_tank,
            'Anicut Scheme Type' => $asset->scheme_type_anicut,
            'Left Bank Canal Length (km)' => $asset->canal_length_left_bank_km,
            'Center Bank Canal Length (km)' => $asset->canal_length_center_bank_km,
            'Right Bank Canal Length (km)' => $asset->canal_length_right_bank_km,
            'Tank Status / Condition' => $asset->tank_status,
        ];
        $organization = $asset->farmerOrganization;
    @endphp

    <section class="irrigation-module irrigation-detail-page px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Section A: Scheme Details</h2>
                    <p class="mt-1 text-sm text-slate-500">Tank, location, irrigation and scheme information.</p>
                </div>
                <dl class="irrigation-detail-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($schemeFields as $label => $value)
                        <div class="irrigation-detail-field">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $display($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Section B: Cascade Action Committee (CAC)</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $asset->cacMembers->count() }} member record(s).</p>
                </div>
                @if ($asset->cacMembers->isNotEmpty())
                    <dl class="irrigation-detail-grid mb-5 grid gap-3 sm:grid-cols-2">
                        <div class="irrigation-detail-field"><dt>CMC Name</dt><dd>{{ $display($asset->cacMembers->first()->cmc_name) }}</dd></div>
                        <div class="irrigation-detail-field"><dt>CAC Name</dt><dd>{{ $display($asset->cacMembers->first()->cac_name) }}</dd></div>
                    </dl>
                @endif
                <div class="space-y-4">
                    @forelse ($asset->cacMembers as $member)
                        <article class="irrigation-member-detail">
                            <h3>CAC Member {{ $loop->iteration }}</h3>
                            <dl class="irrigation-detail-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="irrigation-detail-field"><dt>Member Name</dt><dd>{{ $member->member_name }}</dd></div>
                                <div class="irrigation-detail-field"><dt>Position</dt><dd>{{ $member->position }}</dd></div>
                                <div class="irrigation-detail-field"><dt>NIC</dt><dd>{{ $display($member->nic_number) }}</dd></div>
                                <div class="irrigation-detail-field"><dt>Gender</dt><dd>{{ $display($member->gender) }}</dd></div>
                                <div class="irrigation-detail-field"><dt>Contact Number</dt><dd>{{ $display($member->contact_number) }}</dd></div>
                            </dl>
                        </article>
                    @empty
                        <p class="irrigation-detail-empty">No CAC members recorded.</p>
                    @endforelse
                </div>
            </section>

            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Section C: Farmer Organization</h2>
                    <p class="mt-1 text-sm text-slate-500">Organization registered for this scheme.</p>
                </div>
                @if ($organization)
                    @php
                        $organizationFields = [
                            'Organization Name' => $organization->name,
                            'Legal / Registration Status' => $organization->legal_registration_status,
                            'Registration Number' => $organization->registration_number,
                            'Registration Date' => $organization->registration_date?->format('Y-m-d'),
                            'Address' => $organization->address,
                            'Contact Number' => $organization->contact_number,
                            'Email' => $organization->email,
                            'Total Number of Members above available' => $organization->total_members,
                        ];
                    @endphp
                    <dl class="irrigation-detail-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($organizationFields as $label => $value)
                            <div class="irrigation-detail-field">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ $display($value) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="rounded-lg bg-slate-50 px-4 py-6 text-sm text-slate-500">No Farmer Organization recorded for this scheme.</p>
                @endif
            </section>

            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Section D: Farmer Organization Members</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $organization?->members->count() ?? 0 }} member record(s).</p>
                </div>
                @if ($organization)
                    <div class="space-y-4">
                        @forelse ($organization->members as $member)
                            <article class="irrigation-member-detail">
                                <h3>Farmer Organization Member {{ $loop->iteration }}</h3>
                                <dl class="irrigation-detail-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="irrigation-detail-field"><dt>Name with Initials</dt><dd>{{ $member->name_with_initials }}</dd></div>
                                    <div class="irrigation-detail-field"><dt>Gender</dt><dd>{{ $display($member->gender) }}</dd></div>
                                    <div class="irrigation-detail-field"><dt>NIC</dt><dd>{{ $display($member->nic_number) }}</dd></div>
                                    <div class="irrigation-detail-field"><dt>Phone Number</dt><dd>{{ $display($member->phone_number) }}</dd></div>
                                    <div class="irrigation-detail-field"><dt>Address</dt><dd>{{ $display($member->address) }}</dd></div>
                                    <div class="irrigation-detail-field"><dt>Land Extent (acres)</dt><dd>{{ $display($member->land_extent_acres) }}</dd></div>
                                    <div class="irrigation-detail-field"><dt>Role in Farmer Organization</dt><dd>{{ $display($member->role_in_organization) }}</dd></div>
                                </dl>
                            </article>
                        @empty
                            <p class="irrigation-detail-empty">No Farmer Organization members recorded.</p>
                        @endforelse
                    </div>
                @else
                    <p class="rounded-lg bg-slate-50 px-4 py-6 text-sm text-slate-500">Add a Farmer Organization to view its members here.</p>
                @endif
            </section>
        </div>
    </section>
</x-app-layout>


