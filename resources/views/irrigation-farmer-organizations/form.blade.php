<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center sm:gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Irrigation Infrastructure · Sections C and D</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $editing ? 'Edit Farmer Organization' : 'Add Farmer Organization' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Record organization details and its members in this separate submodule.</p>
            </div>
            <a href="{{ route('irrigation-farmer-organizations.index') }}" class="tank-rehab-secondary">Back to records</a>
        </div>
    </x-slot>

    @php
        $members = old('members', $organization->exists ? $organization->members->map(fn ($member) => $member->only([
            'name_with_initials', 'gender', 'nic_number', 'phone_number', 'address', 'land_extent_acres', 'role_in_organization',
        ]))->all() : [[
            'name_with_initials' => '', 'gender' => '', 'nic_number' => '', 'phone_number' => '', 'address' => '',
            'land_extent_acres' => '', 'role_in_organization' => '',
        ]]);
        if (count($members) === 0) $members = [['name_with_initials' => '', 'gender' => '', 'nic_number' => '', 'phone_number' => '', 'address' => '', 'land_extent_acres' => '', 'role_in_organization' => '']];
        $memberTextFields = [
            'name_with_initials' => 'Name with Initials',
        ];
    @endphp

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8">
        <form method="POST" action="{{ $editing ? route('irrigation-farmer-organizations.update', $organization) : route('irrigation-farmer-organizations.store') }}" class="irrigation-asset-form mx-auto max-w-7xl space-y-6">
            @csrf
            @if ($editing) @method('PUT') @endif
            @if ($errors->any())
                <div class="irrigation-validation-summary" role="alert" aria-live="assertive"><strong>Please review the highlighted fields.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <section class="irrigation-form-card">
                <div class="mb-6 border-b border-slate-100 pb-4"><h2 class="text-lg font-semibold">Section C: Farmer Organization (FO)</h2><p class="mt-1 text-sm text-slate-500">Link the Farmer Organization to its scheme and enter its registration information.</p></div>
                @if ($availableAssets->isEmpty())
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">All available schemes already have a Farmer Organization, or no Section A scheme has been entered yet.</div>
                @endif
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="irrigation_infrastructure_asset_id">Related Scheme <span class="text-red-600">*</span></label>
                        <select id="irrigation_infrastructure_asset_id" name="irrigation_infrastructure_asset_id" required aria-invalid="{{ $errors->has('irrigation_infrastructure_asset_id') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm">
                            <option value="">Select a scheme</option>
                            @foreach ($availableAssets as $asset)
                                <option value="{{ $asset->id }}" @selected((string) old('irrigation_infrastructure_asset_id', $organization->irrigation_infrastructure_asset_id) === (string) $asset->id)>{{ $asset->tank_name }}{{ $asset->tank_registration_number ? ' · '.$asset->tank_registration_number : '' }}{{ $asset->district ? ' · '.$asset->district : '' }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('irrigation_infrastructure_asset_id')" />
                    </div>
                    <div><label for="name">Name of Farmer Organization <span class="text-red-600">*</span></label><input id="name" name="name" required value="{{ old('name', $organization->name) }}" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('name')" /></div>
                    <div><label for="legal_registration_status">Legal / Registration Status <span class="text-red-600">*</span></label><select id="legal_registration_status" name="legal_registration_status" required aria-invalid="{{ $errors->has('legal_registration_status') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Registered', 'Not registered', 'Registration in progress'] as $option)<option value="{{ $option }}" @selected(old('legal_registration_status', $organization->legal_registration_status) === $option)>{{ $option }}</option>@endforeach</select><x-input-error class="mt-1" :messages="$errors->get('legal_registration_status')" /></div>
                    <div><label for="registration_number">Registration Number</label><input id="registration_number" name="registration_number" value="{{ old('registration_number', $organization->registration_number) }}" aria-invalid="{{ $errors->has('registration_number') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('registration_number')" /></div>
                    <div><label for="registration_date">Registration Date</label><input id="registration_date" name="registration_date" type="date" value="{{ old('registration_date', $organization->registration_date?->format('Y-m-d')) }}" aria-invalid="{{ $errors->has('registration_date') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('registration_date')" /></div>
                    <div class="sm:col-span-2 lg:col-span-3"><label for="address">Address</label><textarea id="address" name="address" rows="2" aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm">{{ old('address', $organization->address) }}</textarea><x-input-error class="mt-1" :messages="$errors->get('address')" /></div>
                    <div><label for="contact_number">Organization Contact Number</label><input id="contact_number" name="contact_number" value="{{ old('contact_number', $organization->contact_number) }}" aria-invalid="{{ $errors->has('contact_number') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('contact_number')" /></div>
                    <div><label for="email">Email Address</label><input id="email" name="email" type="email" value="{{ old('email', $organization->email) }}" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('email')" /></div>
                    <div><label for="total_members">Total Number of Members above available</label><input id="total_members" name="total_members" type="number" min="0" step="1" value="{{ old('total_members', $organization->total_members) }}" aria-invalid="{{ $errors->has('total_members') ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get('total_members')" /></div>
                </div>
            </section>

            <section class="irrigation-form-card" x-data="{ initialCount: {{ count($members) }}, extraCount: 0, addMember() { this.extraCount++; $nextTick(() => this.$refs.memberRows.lastElementChild?.scrollIntoView({behavior: 'smooth', block: 'nearest'})); } }">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div><h2 class="text-lg font-semibold">Section D: Farmer Organization Member Details</h2><p class="mt-1 text-sm text-slate-500">Add a separate row for each member. NIC can be 9 digits followed by V or X, or 12 digits; phone must contain 10 digits.</p></div>
                    <button type="button" @click="addMember()" class="tank-rehab-secondary">+ Add member</button>
                </div>
                <div x-ref="memberRows" class="space-y-4">
                    @foreach ($members as $i => $member)
                        <div data-cac-member class="irrigation-member-row">
                            <div class="mb-4 flex items-center justify-between"><h3 class="text-sm font-semibold">Member {{ $i + 1 }}</h3><button type="button" x-show="$root.querySelectorAll('[data-cac-member]').length > 1" @click="$el.closest('[data-cac-member]').remove()" class="text-sm font-medium text-red-600">Remove</button></div>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($memberTextFields as $field => $label)
                                    <div><label for="member-{{ $i }}-{{ $field }}">{{ $label }} @if ($field === 'name_with_initials')<span class="text-red-600">*</span>@endif</label><input id="member-{{ $i }}-{{ $field }}" name="members[{{ $i }}][{{ $field }}]" value="{{ $member[$field] ?? '' }}" @required($field === 'name_with_initials') aria-invalid="{{ $errors->has("members.$i.$field") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get("members.$i.$field")" /></div>
                                @endforeach
                                <div><label for="member-{{ $i }}-gender">Gender</label><select id="member-{{ $i }}-gender" name="members[{{ $i }}][gender]" aria-invalid="{{ $errors->has("members.$i.gender") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Male', 'Female'] as $option)<option value="{{ $option }}" @selected(($member['gender'] ?? '') === $option)>{{ $option }}</option>@endforeach</select><x-input-error class="mt-1" :messages="$errors->get("members.$i.gender")" /></div>
                                <div><label for="member-{{ $i }}-nic">NIC</label><input id="member-{{ $i }}-nic" name="members[{{ $i }}][nic_number]" inputmode="text" maxlength="12" value="{{ $member['nic_number'] ?? '' }}" aria-invalid="{{ $errors->has("members.$i.nic_number") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get("members.$i.nic_number")" /></div>
                                <div><label for="member-{{ $i }}-phone">Phone Number</label><input id="member-{{ $i }}-phone" name="members[{{ $i }}][phone_number]" inputmode="numeric" maxlength="10" value="{{ $member['phone_number'] ?? '' }}" aria-invalid="{{ $errors->has("members.$i.phone_number") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get("members.$i.phone_number")" /></div>
                                <div class="sm:col-span-2 lg:col-span-4"><label for="member-{{ $i }}-address">Address</label><textarea id="member-{{ $i }}-address" name="members[{{ $i }}][address]" rows="2" aria-invalid="{{ $errors->has("members.$i.address") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm">{{ $member['address'] ?? '' }}</textarea><x-input-error class="mt-1" :messages="$errors->get("members.$i.address")" /></div>
                                <div><label for="member-{{ $i }}-land">Land Extent (acres)</label><input id="member-{{ $i }}-land" name="members[{{ $i }}][land_extent_acres]" type="number" min="0" step="0.001" value="{{ $member['land_extent_acres'] ?? '' }}" aria-invalid="{{ $errors->has("members.$i.land_extent_acres") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><x-input-error class="mt-1" :messages="$errors->get("members.$i.land_extent_acres")" /></div>
                                <div><label for="member-{{ $i }}-role">Role in Farmer Organization</label><select id="member-{{ $i }}-role" name="members[{{ $i }}][role_in_organization]" aria-invalid="{{ $errors->has("members.$i.role_in_organization") ? 'true' : 'false' }}" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option>@foreach (['Member', 'President', 'Secretary', 'Treasurer', 'Other'] as $option)<option value="{{ $option }}" @selected(($member['role_in_organization'] ?? '') === $option)>{{ $option }}</option>@endforeach</select><x-input-error class="mt-1" :messages="$errors->get("members.$i.role_in_organization")" /></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <template x-for="i in Array.from({length: extraCount}, (_, index) => initialCount + index)" :key="i">
                    <div class="irrigation-member-row">
                        <div class="mb-4 flex items-center justify-between"><h3 class="text-sm font-semibold">Member <span x-text="i + 1"></span></h3><button type="button" @click="extraCount--" class="text-sm font-medium text-red-600">Remove</button></div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div><label>Name with Initials <span class="text-red-600">*</span></label><input :name="`members[${i}][name_with_initials]`" required class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                            <div><label>Gender</label><select :name="`members[${i}][gender]`" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option><option>Male</option><option>Female</option></select></div>
                            <div><label>NIC</label><input :name="`members[${i}][nic_number]`" maxlength="12" inputmode="text" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                            <div><label>Phone Number</label><input :name="`members[${i}][phone_number]`" maxlength="10" inputmode="numeric" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                            <div class="sm:col-span-2 lg:col-span-4"><label>Address</label><textarea :name="`members[${i}][address]`" rows="2" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></textarea></div>
                            <div><label>Land Extent (acres)</label><input :name="`members[${i}][land_extent_acres]`" type="number" min="0" step="0.001" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"></div>
                            <div><label>Role in Farmer Organization</label><select :name="`members[${i}][role_in_organization]`" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm"><option value="">Select</option><option>Member</option><option>President</option><option>Secretary</option><option>Treasurer</option><option>Other</option></select></div>
                        </div>
                    </div>
                </template>
                @foreach ($errors->get('members.*') as $messages)@foreach ($messages as $message)<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@endforeach @endforeach
            </section>

            <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('irrigation-farmer-organizations.index') }}" class="tank-rehab-secondary">Cancel</a><button type="submit" @disabled($availableAssets->isEmpty() && ! $editing) class="tank-rehab-action disabled:cursor-not-allowed disabled:opacity-50">{{ $editing ? 'Save changes' : 'Save organization and members' }}</button></div>
        </form>
    </section>
</x-app-layout>

