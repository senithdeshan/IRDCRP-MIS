<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Irrigation Infrastructure · Sections C and D</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $organization->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">Farmer Organization and linked member details.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('irrigation-farmer-organizations.index') }}" class="tank-rehab-secondary">Back to records</a>
                <a href="{{ route('irrigation-farmer-organizations.edit', $organization) }}" class="tank-rehab-action">Edit organization</a>
            </div>
        </div>
    </x-slot>

    @php
        $display = fn ($value) => filled($value) ? $value : '—';
        $fields = [
            'Related Scheme' => $organization->asset?->tank_name,
            'Scheme Registration Number' => $organization->asset?->tank_registration_number,
            'District' => $organization->asset?->district,
            'Legal / Registration Status' => $organization->legal_registration_status,
            'Registration Number' => $organization->registration_number,
            'Registration Date' => $organization->registration_date?->format('Y-m-d'),
            'Address' => $organization->address,
            'Contact Number' => $organization->contact_number,
            'Email Address' => $organization->email,
            'Total Number of Members above available' => $organization->total_members,
        ];
    @endphp

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Section C: Farmer Organization</h2>
                    <p class="mt-1 text-sm text-slate-500">Organization details and related scheme.</p>
                </div>
                <dl class="irrigation-detail-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($fields as $label => $value)
                        <div class="irrigation-detail-field">
                            <dt>{{ $label }}</dt>
                            <dd>{{ $display($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="irrigation-form-card irrigation-detail-card">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Section D: Farmer Organization Member Details</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $organization->members->count() }} member record(s).</p>
                </div>
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
            </section>
        </div>
    </section>
</x-app-layout>
