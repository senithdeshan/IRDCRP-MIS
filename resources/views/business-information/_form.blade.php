@php
    $selectedSectors = old('business_sectors', $applicant->business_sectors ?? []);
@endphp

<div class="grid gap-5 xl:grid-cols-[1fr_0.9fr]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(old('province', $applicant->province)), @js(old('district', $applicant->district)), @js(old('ds_division', $applicant->ds_division)))">
    <div class="panel-surface p-6">
        <h2 class="text-base font-semibold text-slate-950">Applicant Basic Information</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="project_reference" value="Project Reference" />
                <x-text-input id="project_reference" name="project_reference" class="mt-2 block w-full" :value="old('project_reference', $applicant->project_reference)" />
                <x-input-error class="mt-2" :messages="$errors->get('project_reference')" />
            </div>

            <div>
                <x-input-label for="eoi_number" value="EOI Number" />
                <x-text-input id="eoi_number" name="eoi_number" class="mt-2 block w-full" placeholder="EOI/YW/2026/0001" :value="old('eoi_number', $applicant->eoi_number)" required />
                <x-input-error class="mt-2" :messages="$errors->get('eoi_number')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="applicant_name" value="Name of the Applicant" />
                <x-text-input id="applicant_name" name="applicant_name" class="mt-2 block w-full" :value="old('applicant_name', $applicant->applicant_name)" required />
                <x-input-error class="mt-2" :messages="$errors->get('applicant_name')" />
            </div>

            <div>
                <x-input-label for="gender" value="Gender" />
                <select id="gender" name="gender" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    @foreach ($genders as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $applicant->gender) === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>

            <div>
                <x-input-label for="nic" value="National Identity Card Number" />
                <x-text-input id="nic" name="nic" class="mt-2 block w-full" :value="old('nic', $applicant->nic)" required />
                <x-input-error class="mt-2" :messages="$errors->get('nic')" />
            </div>

            <div>
                <x-input-label for="date_of_birth" value="Date of Birth" />
                <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-2 block w-full" :value="old('date_of_birth', optional($applicant->date_of_birth)->format('Y-m-d'))" />
                <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
            </div>

            <div>
                <x-input-label for="age_as_at_2026" value="Age as at 2026-01-01" />
                <x-text-input id="age_as_at_2026" name="age_as_at_2026" type="number" class="mt-2 block w-full" :value="old('age_as_at_2026', $applicant->age_as_at_2026)" />
                <x-input-error class="mt-2" :messages="$errors->get('age_as_at_2026')" />
            </div>

            <div>
                <x-input-label for="telephone" value="Telephone Number" />
                <x-text-input id="telephone" name="telephone" class="mt-2 block w-full" :value="old('telephone', $applicant->telephone)" required />
                <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
            </div>

            <div>
                <x-input-label for="whatsapp" value="WhatsApp Number" />
                <x-text-input id="whatsapp" name="whatsapp" class="mt-2 block w-full" :value="old('whatsapp', $applicant->whatsapp)" />
                <x-input-error class="mt-2" :messages="$errors->get('whatsapp')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="email" value="E-mail Address" />
                <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $applicant->email)" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>
    </div>

    <div class="panel-surface p-6">
        <h2 class="text-base font-semibold text-slate-950">Business Information</h2>
        <div class="mt-5 space-y-5">
            <div>
                <x-input-label for="business_name" value="Name of the Business" />
                <x-text-input id="business_name" name="business_name" class="mt-2 block w-full" :value="old('business_name', $applicant->business_name)" required />
                <x-input-error class="mt-2" :messages="$errors->get('business_name')" />
            </div>

            <div>
                <x-input-label for="legal_status" value="Legal Status" />
                <select id="legal_status" name="legal_status" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                    @foreach ($legalStatuses as $status)
                        <option value="{{ $status }}" @selected(old('legal_status', $applicant->legal_status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('legal_status')" />
            </div>

            <div>
                <x-input-label for="business_registered_address" value="Registered Address of the Business" />
                <textarea id="business_registered_address" name="business_registered_address" rows="3" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('business_registered_address', $applicant->business_registered_address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('business_registered_address')" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="province" value="Province" />
                    <select id="province" name="province" x-model="province" @change="district = ''; dsDivision = ''" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Select</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province }}" @selected(old('province', $applicant->province) === $province)>{{ $province }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="district" value="District" />
                    <select id="district" name="district" x-model="district" @change="dsDivision = ''" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Select</option>
                        <template x-for="districtName in districts" :key="districtName">
                            <option :value="districtName" x-text="districtName"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <x-input-label for="ds_division" value="DS Division" />
                    <select id="ds_division" name="ds_division" x-model="dsDivision" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Select</option>
                        <template x-for="division in dsDivisions" :key="division">
                            <option :value="division" x-text="division"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="business_registration_number" value="Business Registration Number" />
                    <x-text-input id="business_registration_number" name="business_registration_number" class="mt-2 block w-full" :value="old('business_registration_number', $applicant->business_registration_number)" />
                </div>
                <div>
                    <x-input-label for="business_registration_date" value="Business Registration Date" />
                    <x-text-input id="business_registration_date" name="business_registration_date" type="date" class="mt-2 block w-full" :value="old('business_registration_date', optional($applicant->business_registration_date)->format('Y-m-d'))" />
                </div>
            </div>

            <div>
                <x-input-label value="Business Sector" />
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($businessSectors as $sector)
                        <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="business_sectors[]" value="{{ $sector }}" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500" @checked(in_array($sector, $selectedSectors, true))>
                            <span>{{ $sector }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="proposed_total_investment" value="Proposed Total Investment Rs." />
                    <x-text-input id="proposed_total_investment" name="proposed_total_investment" type="number" step="0.01" class="mt-2 block w-full" :value="old('proposed_total_investment', $applicant->proposed_total_investment ?? 0)" required />
                </div>
                <div>
                    <x-input-label for="initial_screening_result" value="Initial Screening Result" />
                    <select id="initial_screening_result" name="initial_screening_result" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Pending</option>
                        @foreach ($screeningResults as $result)
                            <option value="{{ $result }}" @selected(old('initial_screening_result', $applicant->initial_screening_result) === $result)>{{ $result }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

@include('business-information.partials.admin-division-script')

<div class="mt-5 flex justify-end gap-3">
    <a href="{{ route('business-information.index') }}" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
    <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">{{ $mode === 'create' ? 'Create Applicant' : 'Update Applicant' }}</button>
</div>
