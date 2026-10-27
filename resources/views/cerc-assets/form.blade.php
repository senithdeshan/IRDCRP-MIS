<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center sm:gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC · Sections A and B</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $editing ? 'Edit CERC Tank' : ($tankType === 'minor' ? 'Add Minor Tank' : 'Add Major Tank') }}</h1>
                <p class="mt-1 text-sm text-slate-500">Enter scheme details and add one row for each CAC member.</p>
            </div>
            <a href="{{ route('cerc-assets.index', ['tank_type' => $tankType]) }}" class="tank-rehab-secondary">Back to records</a>
        </div>
    </x-slot>

    @php
        $members = old('cac_members', $asset->exists ? $asset->cacMembers->map(fn ($member) => $member->only(['member_name', 'position', 'nic_number', 'gender', 'contact_number']))->all() : [['member_name' => '', 'position' => 'Member', 'nic_number' => '', 'gender' => '', 'contact_number' => '']]);
        if (count($members) === 0) $members = [['member_name' => '', 'position' => 'Member', 'nic_number' => '', 'gender' => '', 'contact_number' => '']];
        $firstCacMember = $asset->exists ? $asset->cacMembers->first() : null;
        $textFields = [
            'river_basin' => 'River Basin', 'river_basin_number' => 'River Basin - Number', 'tank_name' => 'Tank Name',
            'tank_registration_number' => 'Tank Registration Number', 'cascade_name' => 'Tank Cascade Name', 'province' => 'Province',
            'district' => 'District', 'ds_division' => 'DS Division', 'asc' => 'ASC', 'gn_division' => 'GN Division', 'gn_code' => 'GN Code',
            'gn_division_families' => 'GN Division families - number',
        ];
        $numericFields = [
            'main_crop_cultivation' => ['Main crop cultivation', 'text', ''],
            'tank_capacity_ac_ft' => ['Tank Capacity (ac.ft)', 'number', '0.01'],
            'catchment_area_ha' => ['Catchment Area (ha)', 'number', '0.01'],
            'irrigable_area_existing_ha' => ['Irrigable Area - Existing (ha)', 'number', '0.01'],
            'irrigable_area_cultivated_ha' => ['Irrigable Area - Cultivated (ha)', 'number', '0.01'],
            'tank_farmer_families' => ['Number of farmer families in the Tank', 'number', '1'],
            'farmer_organization_families' => ['Number of farmer families in Farmer Organization', 'number', '1'],
            'canal_length_left_bank_km' => ['Canal Length - Left Bank (Km)', 'number', '0.001'],
            'canal_length_center_bank_km' => ['Canal Length - Center Bank (Km)', 'number', '0.001'],
            'canal_length_right_bank_km' => ['Canal Length - Right Bank (Km)', 'number', '0.001'],
        ];
    @endphp

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8">
        <form method="POST" action="{{ $editing ? route('cerc-assets.update', $asset) : route('cerc-assets.store') }}" class="irrigation-asset-form mx-auto max-w-7xl space-y-6">
            @csrf
            <input type="hidden" name="tank_type" value="{{ $tankType }}">
            @if ($editing) @method('PUT') @endif

            @if ($errors->any())
                <div class="irrigation-validation-summary" role="alert" aria-live="assertive">
                    <strong>Please review the highlighted fields.</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="irrigation-form-card">
                <div class="mb-6 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold text-slate-900">Section A: Scheme Details</h2><p class="mt-1 text-sm text-slate-500">Tank, location, irrigation and scheme information.</p></div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($textFields as $name => $label)
                        <div>
                            <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }} @if ($name === 'tank_name')<span class="text-red-600">*</span>@endif</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="{{ $name === 'gn_division_families' ? 'number' : 'text' }}" @if ($name === 'gn_division_families') min="0" step="1" @endif value="{{ old($name, $asset->{$name}) }}" @required($name === 'tank_name') aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <x-input-error class="mt-1" :messages="$errors->get($name)" />
                        </div>
                    @endforeach
                    @foreach ($numericFields as $name => [$label, $type, $step])
                        <div>
                            <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if ($type === 'number') min="0" step="{{ $step }}" @endif value="{{ old($name, $asset->{$name}) }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <x-input-error class="mt-1" :messages="$errors->get($name)" />
                        </div>
                    @endforeach
                    <div>
                        <label for="scheme_type_tank" class="block text-sm font-medium text-slate-700">Scheme Type - Tank</label>
                        <select id="scheme_type_tank" name="scheme_type_tank" aria-invalid="{{ $errors->has('scheme_type_tank') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Major', 'Medium', 'Minor'] as $option)<option value="{{ $option }}" @selected(old('scheme_type_tank', $asset->scheme_type_tank) === $option)>{{ $option }}</option>@endforeach</select>
                        <x-input-error class="mt-1" :messages="$errors->get('scheme_type_tank')" />
                    </div>
                    <div>
                        <label for="scheme_type_anicut" class="block text-sm font-medium text-slate-700">Scheme Type - Anicut</label>
                        <select id="scheme_type_anicut" name="scheme_type_anicut" aria-invalid="{{ $errors->has('scheme_type_anicut') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Large', 'Minor'] as $option)<option value="{{ $option }}" @selected(old('scheme_type_anicut', $asset->scheme_type_anicut) === $option)>{{ $option }}</option>@endforeach</select>
                        <x-input-error class="mt-1" :messages="$errors->get('scheme_type_anicut')" />
                    </div>
                    <div>
                        <label for="tank_status" class="block text-sm font-medium text-slate-700">Tank Status / Condition</label>
                        <select id="tank_status" name="tank_status" aria-invalid="{{ $errors->has('tank_status') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Functional', 'Partially functional', 'Non-functional', 'Abandoned'] as $option)<option value="{{ $option }}" @selected(old('tank_status', $asset->tank_status) === $option)>{{ $option }}</option>@endforeach</select>
                        <x-input-error class="mt-1" :messages="$errors->get('tank_status')" />
                    </div>
                </div>
            </div>

            <div class="irrigation-form-card" x-data="{ initialCount: {{ count($members) }}, extraCount: 0, addMember() { this.extraCount++; $nextTick(() => this.$refs.memberRows.lastElementChild?.scrollIntoView({behavior: 'smooth', block: 'nearest'})); } }">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div><h2 class="text-lg font-semibold text-slate-900">Section B: Cascade Action Committee (CAC)</h2><p class="mt-1 text-sm text-slate-500">Repeat the member details for each CAC member. NIC accepts 10 or 12 digits.</p></div>
                    <button type="button" @click="addMember()" class="tank-rehab-secondary">+ Add member</button>
                </div>
                <div class="mb-5 grid gap-4 sm:grid-cols-2">
                    <div><label for="cmc_name" class="block text-sm font-medium text-slate-700">CMC Name</label><input id="cmc_name" name="cmc_name" value="{{ old('cmc_name', $firstCacMember?->cmc_name) }}" aria-invalid="{{ $errors->has('cmc_name') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('cmc_name')" /></div>
                    <div><label for="cac_name" class="block text-sm font-medium text-slate-700">CAC Name</label><input id="cac_name" name="cac_name" value="{{ old('cac_name', $firstCacMember?->cac_name) }}" aria-invalid="{{ $errors->has('cac_name') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('cac_name')" /></div>
                </div>
                <div x-ref="memberRows" class="space-y-4">
                    @foreach ($members as $i => $member)
                        <div data-cac-member class="irrigation-member-row" x-data="{ rowIndex: {{ $i }} }">
                            <div class="mb-3 flex items-center justify-between"><h3 class="text-sm font-semibold text-slate-800">CAC Member <span x-text="rowIndex + 1">{{ $i + 1 }}</span></h3><button type="button" x-show="$root.querySelectorAll('[data-cac-member]').length > 1" @click="$el.closest('[data-cac-member]').remove()" class="text-sm font-medium text-red-600">Remove</button></div>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div><label class="block text-sm font-medium text-slate-700">CAC Member Name <span class="text-red-600">*</span></label><input name="cac_members[{{ $i }}][member_name]" value="{{ $member['member_name'] ?? '' }}" required aria-invalid="{{ $errors->has("cac_members.$i.member_name") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"><x-input-error class="mt-1" :messages="$errors->get("cac_members.$i.member_name")" /></div>
                                <div><label class="block text-sm font-medium text-slate-700">Member Position <span class="text-red-600">*</span></label><select name="cac_members[{{ $i }}][position]" required aria-invalid="{{ $errors->has("cac_members.$i.position") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm">@foreach (['Member', 'Co-chairperson'] as $option)<option value="{{ $option }}" @selected(($member['position'] ?? 'Member') === $option)>{{ $option }}</option>@endforeach</select><x-input-error class="mt-1" :messages="$errors->get("cac_members.$i.position")" /></div>
                                <div><label class="block text-sm font-medium text-slate-700">Member NIC Number</label><input name="cac_members[{{ $i }}][nic_number]" inputmode="numeric" maxlength="12" value="{{ $member['nic_number'] ?? '' }}" aria-invalid="{{ $errors->has("cac_members.$i.nic_number") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get("cac_members.$i.nic_number")" /></div>
                                <div><label class="block text-sm font-medium text-slate-700">Member Gender</label><select name="cac_members[{{ $i }}][gender]" aria-invalid="{{ $errors->has("cac_members.$i.gender") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Male', 'Female'] as $option)<option value="{{ $option }}" @selected(($member['gender'] ?? '') === $option)>{{ $option }}</option>@endforeach</select><x-input-error class="mt-1" :messages="$errors->get("cac_members.$i.gender")" /></div>
                                <div><label for="cac-member-contact-{{ $i }}" class="block text-sm font-medium text-slate-700">Member Contact Number</label><input id="cac-member-contact-{{ $i }}" name="cac_members[{{ $i }}][contact_number]" inputmode="numeric" maxlength="10" value="{{ $member['contact_number'] ?? '' }}" aria-invalid="{{ $errors->has("cac_members.$i.contact_number") ? 'true' : 'false' }}" @if ($errors->has("cac_members.$i.contact_number")) aria-describedby="cac-member-contact-error-{{ $i }}" @endif class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error id="cac-member-contact-error-{{ $i }}" class="mt-1" :messages="$errors->get("cac_members.$i.contact_number")" /></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <template x-for="i in Array.from({length: extraCount}, (_, index) => initialCount + index)" :key="i">
                    <div class="irrigation-member-row">
                        <div class="mb-3 flex items-center justify-between"><h3 class="text-sm font-semibold text-slate-800">CAC Member <span x-text="i + 1"></span></h3><button type="button" @click="extraCount--" class="text-sm font-medium text-red-600">Remove</button></div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div><label class="block text-sm font-medium text-slate-700">CAC Member Name <span class="text-red-600">*</span></label><input :name="`cac_members[${i}][member_name]`" required class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                            <div><label class="block text-sm font-medium text-slate-700">Member Position <span class="text-red-600">*</span></label><select :name="`cac_members[${i}][position]`" required class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option>Member</option><option>Co-chairperson</option></select></div>
                            <div><label class="block text-sm font-medium text-slate-700">Member NIC Number</label><input :name="`cac_members[${i}][nic_number]`" maxlength="12" inputmode="numeric" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                            <div><label class="block text-sm font-medium text-slate-700">Member Gender</label><select :name="`cac_members[${i}][gender]`" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option><option>Male</option><option>Female</option></select></div>
                            <div><label :for="`cac-member-contact-${i}`" class="block text-sm font-medium text-slate-700">Member Contact Number</label><input :id="`cac-member-contact-${i}`" :name="`cac_members[${i}][contact_number]`" maxlength="10" inputmode="numeric" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                        </div>
                    </div>
                </template>
                @foreach ($errors->get('cac_members.*') as $messages)
                    @foreach ($messages as $message)<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@endforeach
                @endforeach
            </div>

            <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('cerc-assets.index', ['tank_type' => $tankType]) }}" class="tank-rehab-secondary">Cancel</a><button type="submit" class="tank-rehab-action">{{ $editing ? 'Save changes' : 'Save scheme and CAC members' }}</button></div>
        </form>
    </section>
</x-app-layout>



