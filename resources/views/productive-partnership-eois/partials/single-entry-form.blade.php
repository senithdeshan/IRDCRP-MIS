@php
    $entryValues = isset($eoi) ? $eoi->attributesToArray() : [];
    if (isset($eoi)) {
        foreach (['registration_date', 'initial_screening_date'] as $dateField) {
            $entryValues[$dateField] = $eoi->$dateField?->format('Y-m-d');
        }
        $entryValues['submission_time'] = $eoi->submission_time?->format('Y-m-d\TH:i');
        $entryValues['screening_result'] = $eoi->farmer_screening_result;
    }
    $entryValues = array_replace($entryValues, old());
    $fields = [
        'EOI details' => [
            'eoi_number' => ['EOI Number', 'text', true],
        ],
        'Organization details' => [
            'organization_name' => ['Organization Name', 'text', true],
            'number_of_members' => ['Number of Members', 'number', true],
            'legal_status' => ['Legal Status', 'suggest', true],
            'place_of_registration' => ['Place of Registration', 'text', false],
            'registration_number' => ['Registration Number', 'text', false],
            'registration_date' => ['Registration Date', 'date', false],
            'organization_registered_address' => ['Registered Address', 'textarea', true],
        ],
        'Contact person' => [
            'contact_person_name' => ['Contact Person Name', 'text', true],
            'contact_person_designation' => ['Contact Person Designation', 'suggest', true],
            'contact_person_telephone' => ['Contact Telephone', 'tel', true],
            'contact_person_email' => ['Contact Email', 'email', true],
        ],
        'Administrative location' => [
            'province' => ['Province', 'select', true],
            'district' => ['District', 'select', true],
            'ds_division' => ['DS Division (DSD)', 'select', true],
            'as_centre' => ['Agrarian Service Centre (ASC)', 'location-suggest', false],
            'gn_division' => ['GN Division (GND)', 'location-suggest', false],
            'proposed_business_location_address' => ['Proposed Business Address', 'textarea', true],
        ],
        'Business proposal and funding' => [
            'business_proposal_title' => ['Business Proposal Title', 'text', true],
            'sector' => ['Sector', 'select', true],
            'proposed_total_investment' => ['Proposed Total Investment (Rs)', 'money', true],
            'expected_grant_irdcrp' => ['Expected Grant from IRDCRP (Rs)', 'money', true],
        ],
        'Screening and notes (optional)' => [
            'completeness_mandatory_requirement' => ['Completeness of Mandatory Requirements', 'suggest', false],
            'initial_desk_review_status' => ['Initial Desk Review Status', 'suggest', false],
            'initial_screening_date' => ['Initial Screening Date', 'date', false],
            'notes' => ['Notes', 'textarea', false],
        ],
        'Source references (optional)' => [
            'kobo_id' => ['Kobo Record ID', 'text', false],
            'kobo_uuid' => ['Kobo UUID', 'text', false],
            'submission_time' => ['Submission Date & Time', 'datetime-local', false],
            'validation_status' => ['Source Validation Status', 'text', false],
            'status' => ['Source Record Status', 'text', false],
        ],
    ];
    $suggestions = [
        'legal_status' => ['Registered Society', 'Cooperative Society', 'Farmer Organization', 'Company', 'Unregistered'],
        'contact_person_designation' => ['Chairperson', 'Secretary', 'Treasurer', 'Manager', 'President'],
        'completeness_mandatory_requirement' => ['Complete', 'Incomplete', 'Pending'],
        'initial_desk_review_status' => ['Pending', 'Selected', 'Rejected', 'Resubmit'],
    ];
    if ($farmerWorkflow ?? false) {
        unset($fields['Screening and notes (optional)']['initial_desk_review_status']);
        $fields['Screening and notes (optional)']['screening_result'] = ['Initial Screening Result', 'screening', true];
    }
@endphp

<section class="px-4 pb-6 sm:px-6 lg:px-8" aria-labelledby="single-entry-title">
    <form method="POST" action="{{ $formAction ?? route('selected-eois.store') }}" novalidate class="eoi-entry-form panel-surface mx-auto max-w-7xl overflow-hidden" x-data="eoiEntryLocation(@js($administrativeDivisions), @js($locationRecords), @js(collect($entryValues)->only(['province', 'district', 'ds_division', 'as_centre', 'gn_division'])))">
        @csrf
        @if (($formMethod ?? 'POST') === 'PATCH') @method('PATCH') @endif
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="single-entry-title" class="text-lg font-semibold text-slate-950">{{ $formTitle ?? 'Single EOI Entry' }}</h2>
                <span class="rounded-full border border-emerald-200 bg-white px-3 py-1 text-xs font-medium text-emerald-800">Component 1.2 · Productive Partnership</span>
            </div>
            <p class="mt-1 text-xs text-slate-600">Register one received EOI. Fields marked <span class="font-semibold text-rose-600">*</span> are required.</p>
            @if ($errors->any() && ! $errors->has('eoi_file'))
                <p role="alert" class="mt-3 text-sm font-medium text-red-600">Please correct the highlighted fields below and save again.</p>
            @endif
        </div>

        @foreach ($fields as $heading => $group)
            <fieldset class="min-w-0 border-b border-slate-100 px-5 pb-5 pt-3">
                <legend class="flex items-center gap-2 pt-4 text-sm font-semibold text-slate-900"><span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-50 text-xs text-emerald-700">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $heading }}</legend>
                @if ($heading === 'Administrative location')
                    <p class="mb-3 text-xs text-slate-500">Select Province, District and DSD first. ASC and GND suggest matching existing records; you can also type a new value.</p>
                @endif
                <div class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($group as $name => [$label, $type, $required])
                        @php
                            $control = 'eoi-entry-control '.($errors->has($name) ? 'border-red-500' : 'border-slate-200');
                        @endphp
                        <div class="min-w-0 {{ $type === 'textarea' ? 'sm:col-span-2' : '' }}">
                            <label for="single_{{ $name }}" class="block text-xs font-medium text-slate-600">{{ $label }} @if ($required)<span class="text-rose-600">*</span>@endif</label>
                            @if ($type === 'screening')
                                <select id="single_{{ $name }}" name="{{ $name }}" class="{{ $control }}" required aria-describedby="error_{{ $name }}">
                                    @foreach (['Pending', 'Selected', 'Reject'] as $result)
                                        <option value="{{ $result }}" @selected(($entryValues[$name] ?? 'Pending') === $result)>{{ $result }}</option>
                                    @endforeach
                                </select>
                            @elseif ($type === 'select')
                                <select id="single_{{ $name }}" name="{{ $name }}" class="{{ $control }}" required aria-describedby="error_{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
                                    @if ($name === 'province') x-model="province" @change="changeProvince()"
                                    @elseif ($name === 'district') x-model="district" @change="changeDistrict()" :disabled="!province"
                                    @elseif ($name === 'ds_division') x-model="dsDivision" @change="changeDsDivision()" :disabled="!district"
                                    @endif>
                                    <option value="">Select {{ $label }}</option>
                                    @if ($name === 'province' || $name === 'sector')
                                        @foreach ($name === 'province' ? array_keys($administrativeDivisions) : $sectors as $option)
                                            <option value="{{ $option }}" @selected(($entryValues[$name] ?? '') === $option)>{{ $option }}</option>
                                        @endforeach
                                    @else
                                        <template x-for="option in {{ $name === 'district' ? 'districts' : 'dsDivisions' }}" :key="option">
                                            <option :value="option" x-text="option" :selected="option === {{ $name === 'district' ? 'district' : 'dsDivision' }}"></option>
                                        </template>
                                    @endif
                                </select>
                            @elseif ($type === 'suggest' || $type === 'location-suggest')
                                <input id="single_{{ $name }}" name="{{ $name }}" type="text" list="options_{{ $name }}" maxlength="255" value="{{ ($entryValues[$name] ?? '') }}" placeholder="Select or type…" class="{{ $control }}" @required($required) aria-describedby="error_{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
                                    @if ($name === 'as_centre') x-model="asCentre" @input="gnDivision = ''" :disabled="!dsDivision"
                                    @elseif ($name === 'gn_division') x-model="gnDivision" :disabled="!dsDivision"
                                    @endif>
                                <datalist id="options_{{ $name }}">
                                    @if ($type === 'location-suggest')
                                        <template x-for="option in {{ $name === 'as_centre' ? 'asCentres' : 'gnDivisions' }}" :key="option"><option :value="option"></option></template>
                                    @else
                                        @foreach ($suggestions[$name] as $option)<option value="{{ $option }}"></option>@endforeach
                                    @endif
                                </datalist>
                            @elseif ($type === 'textarea')
                                <textarea id="single_{{ $name }}" name="{{ $name }}" rows="2" maxlength="5000" class="{{ $control }}" @required($required) aria-describedby="error_{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">{{ ($entryValues[$name] ?? '') }}</textarea>
                            @else
                                <input id="single_{{ $name }}" name="{{ $name }}" type="{{ $type === 'money' ? 'number' : $type }}" value="{{ ($entryValues[$name] ?? '') }}" class="{{ $control }}" @required($required) aria-describedby="error_{{ $name }}" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
                                    @if ($name === 'eoi_number') placeholder="EOI/PP/2026/1/131" maxlength="255"
                                    @elseif ($type === 'money') min="{{ $name === 'expected_grant_irdcrp' ? '0' : '0.01' }}" max="9999999999999.99" step="0.01"
                                    @elseif ($type === 'number') min="1" max="4294967295" step="1"
                                    @elseif ($type === 'date') max="{{ now()->format('Y-m-d') }}"
                                    @elseif ($type === 'datetime-local') max="{{ now()->format('Y-m-d\TH:i') }}"
                                    @else maxlength="{{ $type === 'tel' ? '30' : '255' }}"
                                    @endif>
                            @endif
                            <x-input-error id="error_{{ $name }}" class="mt-2" :messages="$errors->get($name)" />
                            @if ($name === 'eoi_number')
                                <p class="mt-1 text-xs text-slate-500">Example: EOI/PP/2026/1/131. Year 2026 and Call 1 are automatically used for filtering.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <div class="flex flex-wrap items-center justify-end gap-4 bg-slate-50 px-5 py-4">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">{{ $submitLabel ?? 'Save EOI' }}</button>
            <a href="{{ $cancelUrl ?? route('selected-eois.index') }}" class="text-sm font-semibold text-slate-600">Cancel</a>
        </div>
    </form>
</section>
