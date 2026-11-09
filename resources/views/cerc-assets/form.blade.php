<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC · Minor Tank</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $editing ? 'Update Scheme Registration' : 'Scheme Registration' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Enter the scheme, location, cost, contract, and safeguard details.</p>
            </div>
            <a href="{{ route('cerc-assets.index') }}" class="tank-rehab-secondary">Back to Scheme Details</a>
        </div>
    </x-slot>

    @php
        $sections = [
            'Scheme information' => [
                'institution' => ['Institution', 'text'],
                'programme_type' => ['Programme Type', 'text'],
                'contract_status' => ['Contract Status', 'text'],
                'tank_name' => ['Scheme Name', 'text', 'required'],
                'category' => ['Category', 'text'],
                'year' => ['Year', 'number', 'integer'],
                'size' => ['Size', 'text'],
                'lot' => ['Lot', 'text'],
                'cluster' => ['Cluster', 'text'],
            ],
            'Estimated costs' => [
                'civil_cost' => ['Civil Cost (LKR)', 'number', 'money'],
                'preliminaries_cost' => ['Preliminaries (LKR)', 'number', 'money'],
                'direct_cost' => ['Direct Cost (LKR)', 'number', 'money'],
                'vat_18' => ['VAT 18% (LKR)', 'number', 'money'],
                'contingencies_cost' => ['Contingencies (LKR)', 'number', 'money'],
                'total_cost_estimate' => ['Total Cost Estimate (LKR)', 'number', 'money'],
            ],
            'Award and contract' => [
                'awarded_civil_cost' => ['Awarded Amount - Civil Cost (LKR)', 'number', 'money'],
                'awarded_preliminaries_cost' => ['Awarded Amount - Preliminaries (LKR)', 'number', 'money'],
                'awarded_amount_without_vat_contingencies' => ['Awarded Amount without VAT & Contingencies (LKR)', 'number', 'money'],
                'advance_imprest' => ['Advance / Imprest (LKR)', 'number', 'money'],
                'provisional_sum' => ['Provisional Sum (LKR)', 'number', 'money'],
                'date_of_award' => ['Date of Award', 'date'],
                'agreement_sign_date' => ['Agreement Sign Date', 'date'],
                'contract_period_days' => ['Contract Period (Days)', 'number', 'integer'],
                'completion_date' => ['Completion Date', 'date'],
                'contract_number' => ['Contract Number', 'text'],
                'contractor_name' => ['Contractor Name', 'text'],
                'contractor_address' => ['Contractor Address', 'textarea'],
                'contractor_contact_number' => ['Contractor Contact Number', 'tel'],
            ],
            'Beneficiaries and command area' => [
                'beneficiary_families' => ['Beneficiary Families', 'number', 'integer'],
                'command_area_ac' => ['Command Area (Ac)', 'number', 'money'],
            ],
            'Environmental and social clearances' => [
                'emp_clearance_received_date' => ['EMP Clearance Received Date', 'date'],
                'smp_clearance_received_date' => ['SMP Clearance Received Date', 'date'],
                'esmp_submitted_to_procurement_date' => ['Date of ESMP Submitted to Procurement Division', 'date'],
            ],
        ];
        $oldProvince = old('province', $asset->province ?? '');
        $oldDistrict = old('district', $asset->district ?? '');
        $oldDsDivision = old('ds_division', $asset->ds_division ?? '');
        $savedLatitude = old('latitude', $asset->latitude ?? '');
        $savedLongitude = old('longitude', $asset->longitude ?? '');
        $mapCenterLatitude = old('latitude') ?: ($locationPoint['latitude'] ?? 7.8731);
        $mapCenterLongitude = old('longitude') ?: ($locationPoint['longitude'] ?? 80.7718);
    @endphp

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8" x-data="adminDivisionPicker(@js($administrativeDivisions), @js($oldProvince), @js($oldDistrict), @js($oldDsDivision))">
        <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('cerc-assets.update', $asset) : route('cerc-assets.store') }}" class="mx-auto max-w-7xl space-y-6">
            @csrf
            @if ($editing) @method('PUT') @endif

            @if ($errors->any())
                <div class="irrigation-validation-summary" role="alert"><strong>Please review the highlighted fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <section class="irrigation-form-card">
                <header class="mb-6 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">Location</h2><p class="mt-1 text-sm text-slate-500">Choose the administrative area for this scheme.</p></header>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div><label for="province" class="block text-sm font-medium text-slate-700">Province</label><select id="province" name="province" x-model="province" @change="district = ''; dsDivision = ''; $refs.district.querySelectorAll('[data-initial-option]').forEach(option => option.remove()); $refs.dsDivision.querySelectorAll('[data-initial-option]').forEach(option => option.remove())" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select province</option>@foreach (array_keys($administrativeDivisions) as $province)<option value="{{ $province }}" @selected($oldProvince === $province)>{{ $province }}</option>@endforeach @if ($oldProvince && !array_key_exists($oldProvince, $administrativeDivisions))<option value="{{ $oldProvince }}" selected>{{ $oldProvince }} (saved)</option>@endif</select><x-input-error class="mt-1" :messages="$errors->get('province')" /></div>
                    <div><label for="district" class="block text-sm font-medium text-slate-700">District</label><select id="district" name="district" x-ref="district" x-model="district" @change="dsDivision = ''; $refs.dsDivision.querySelectorAll('[data-initial-option]').forEach(option => option.remove())" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select district</option>@foreach (($administrativeDivisions[$oldProvince] ?? []) as $districtName => $dsdOptions)<option data-initial-option value="{{ $districtName }}" @selected($oldDistrict === $districtName)>{{ $districtName }}</option>@endforeach @if ($oldDistrict && !array_key_exists($oldDistrict, $administrativeDivisions[$oldProvince] ?? []))<option data-initial-option value="{{ $oldDistrict }}" selected>{{ $oldDistrict }} (saved)</option>@endif<template x-for="districtName in districts" :key="districtName"><option :value="districtName" x-text="districtName"></option></template></select><x-input-error class="mt-1" :messages="$errors->get('district')" /></div>
                    <div><label for="ds_division" class="block text-sm font-medium text-slate-700">DSD</label><select id="ds_division" name="ds_division" x-ref="dsDivision" x-model="dsDivision" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select DSD</option>@foreach (($administrativeDivisions[$oldProvince][$oldDistrict] ?? []) as $dsdOption)<option data-initial-option value="{{ $dsdOption }}" @selected($oldDsDivision === $dsdOption)>{{ $dsdOption }}</option>@endforeach @if ($oldDsDivision && !in_array($oldDsDivision, $administrativeDivisions[$oldProvince][$oldDistrict] ?? [], true))<option data-initial-option value="{{ $oldDsDivision }}" selected>{{ $oldDsDivision }} (saved)</option>@endif<template x-for="division in dsDivisions" :key="division"><option :value="division" x-text="division"></option></template></select><x-input-error class="mt-1" :messages="$errors->get('ds_division')" /></div>
                    <div><label for="asc" class="block text-sm font-medium text-slate-700">ASC</label><input id="asc" name="asc" value="{{ old('asc', $asset->asc) }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('asc')" /></div>
                </div>
                <div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 sm:p-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div><h3 class="font-semibold text-slate-900">Pin the scheme on Sri Lanka map</h3><p class="mt-1 text-sm text-slate-600">Click the exact location on the map, or drag its pin to update coordinates.</p></div>
                        <span id="cerc-map-coordinate-label" class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
                            @if (filled($savedLatitude) && filled($savedLongitude))
                                {{ number_format((float) $savedLatitude, 6) }}, {{ number_format((float) $savedLongitude, 6) }}
                            @else
                                Choose a location on the map
                            @endif
                        </span>
                    </div>
                    <input id="latitude" name="latitude" type="hidden" value="{{ $savedLatitude }}"><input id="longitude" name="longitude" type="hidden" value="{{ $savedLongitude }}">
                    <div id="cerc-location-picker" class="h-[340px] overflow-hidden rounded-xl border border-emerald-100 shadow-sm" data-center-lat="{{ $mapCenterLatitude }}" data-center-lng="{{ $mapCenterLongitude }}" data-selected-lat="{{ $savedLatitude }}" data-selected-lng="{{ $savedLongitude }}" aria-label="Map for choosing the scheme location"></div>
                    <x-input-error class="mt-2" :messages="$errors->get('latitude')" /><x-input-error class="mt-2" :messages="$errors->get('longitude')" />
                </div>
            </section>

            @foreach ($sections as $sectionTitle => $fields)
                <section class="irrigation-form-card">
                    <header class="mb-6 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">{{ $sectionTitle }}</h2></header>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($fields as $name => $field)
                            @php([$label, $type] = $field)
                            @php($step = $type === 'number' ? (($field[2] ?? '') === 'money' ? '0.01' : '1') : null)
                            <div class="{{ $type === 'textarea' ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                                <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }} @if (($field[2] ?? '') === 'required')<span class="text-red-600">*</span>@endif</label>
                                @if ($type === 'textarea')
                                    <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm">{{ old($name, $asset->{$name}) }}</textarea>
                                @else
                                    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $asset->{$name}) }}" @if ($type === 'number') min="0" step="{{ $step }}" @endif @required(($field[2] ?? '') === 'required') aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                @endif
                                <x-input-error class="mt-1" :messages="$errors->get($name)" />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section class="irrigation-form-card">
                <header class="mb-6 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">Construction progress and photos</h2><p class="mt-1 text-sm text-slate-500">Update progress and upload site evidence. Each photo slot accepts one image up to 10 MB.</p></header>
                <div class="mb-6 max-w-md"><label for="progress" class="block text-sm font-medium text-slate-700">Progress (%)</label><div class="mt-1.5 flex items-center gap-3"><input id="progress" name="progress" type="number" min="0" max="100" step="0.01" value="{{ old('progress', $asset->progress) }}" class="block w-full rounded-lg border-slate-300 shadow-sm"><span class="font-semibold text-slate-500">%</span></div><x-input-error class="mt-1" :messages="$errors->get('progress')" /></div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        'before_work_photo' => ['Before work photo', 'before_work_photo_path'],
                        'current_photo_1' => ['Current photo 1', 'current_photo_1_path'],
                        'current_photo_2' => ['Current photo 2', 'current_photo_2_path'],
                        'contractor_board_photo' => ['Contractor information board photo', 'contractor_board_photo_path'],
                    ] as $input => [$label, $pathField])
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label for="{{ $input }}" class="block text-sm font-semibold text-slate-800">{{ $label }}</label>
                            @if ($editing && $asset->{$pathField})<a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($asset->{$pathField}) }}" target="_blank" class="mt-3 block overflow-hidden rounded-lg bg-white"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($asset->{$pathField}) }}" alt="Current {{ strtolower($label) }}" class="h-36 w-full object-cover"></a><p class="mt-2 text-xs text-slate-500">Current photo. Upload a replacement if needed.</p>@else<div class="mt-3 flex h-36 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-sm text-slate-400">No photo uploaded</div>@endif
                            <input id="{{ $input }}" name="{{ $input }}" type="file" accept="image/*" class="mt-3 block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-100 file:px-3 file:py-2 file:font-semibold file:text-emerald-800">
                            <x-input-error class="mt-1" :messages="$errors->get($input)" />
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('cerc-assets.index') }}" class="tank-rehab-secondary">Cancel</a><button type="submit" class="tank-rehab-action">{{ $editing ? 'Update Scheme' : 'Register Scheme' }}</button></div>
        </form>
    </section>
    @include('business-information.partials.admin-division-script')
    @push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            (() => {
                const setup = () => {
                    const element = document.getElementById('cerc-location-picker');
                    if (!element || !window.L || element.dataset.initialized) return;
                    element.dataset.initialized = 'true';
                    const latInput = document.getElementById('latitude');
                    const lngInput = document.getElementById('longitude');
                    const label = document.getElementById('cerc-map-coordinate-label');
                    const center = [Number(element.dataset.centerLat) || 7.8731, Number(element.dataset.centerLng) || 80.7718];
                    const map = L.map(element, { maxBounds: [[5.85, 79.45], [9.95, 82.05]], maxBoundsViscosity: 0.8, scrollWheelZoom: false }).setView(center, 8);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
                    let marker = null;
                    const update = (point) => {
                        const lat = point.lat.toFixed(7);
                        const lng = point.lng.toFixed(7);
                        latInput.value = lat;
                        lngInput.value = lng;
                        label.textContent = `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
                        if (!marker) marker = L.marker(point, { draggable: true }).addTo(map).bindPopup('Minor Tank scheme location');
                        marker.setLatLng(point);
                        marker.off('dragend').on('dragend', (event) => update(event.target.getLatLng()));
                    };
                    const saved = [Number(element.dataset.selectedLat), Number(element.dataset.selectedLng)];
                    if (Number.isFinite(saved[0]) && Number.isFinite(saved[1]) && saved[0] && saved[1]) update(L.latLng(saved[0], saved[1]));
                    map.on('click', (event) => update(event.latlng));
                    setTimeout(() => map.invalidateSize(), 120);
                };
                document.addEventListener('DOMContentLoaded', setup);
                setTimeout(setup, 250);
            })();
        </script>
    @endpush
</x-app-layout>
