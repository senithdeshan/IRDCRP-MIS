<div x-data="adminDivisionPicker(@js($administrativeDivisions), @js(old('province', $tank->province)), @js(old('district', $tank->district)), @js(old('ds_division', $tank->ds_division)))">
    <div class="tank-rehab-panel">
        <div class="bg-gradient-to-b from-white to-emerald-50 px-6 py-6">
            <h2 class="tank-rehab-title">Tank Details</h2>
            <p class="mt-3 text-center text-sm font-medium text-slate-500">All fields follow the Tank Registration Excel sheet order.</p>
        </div>

        <div class="space-y-6 p-6">
            <div class="tank-rehab-section">
                <h3 class="tank-rehab-section-title">Tank And Location</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <x-input-label for="tank_id" value="Tank Id" class="tank-rehab-label" />
                        <x-text-input id="tank_id" name="tank_id" class="mt-2 tank-rehab-input" :value="old('tank_id', $tank->tank_id)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('tank_id')" />
                    </div>
                    <div>
                        <x-input-label for="tank_name" value="Tank Name" class="tank-rehab-label" />
                        <x-text-input id="tank_name" name="tank_name" class="mt-2 tank-rehab-input" :value="old('tank_name', $tank->tank_name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('tank_name')" />
                    </div>
                    <div>
                        <x-input-label for="river_basin" value="River Basin" class="tank-rehab-label" />
                        <x-text-input id="river_basin" name="river_basin" class="mt-2 tank-rehab-input" :value="old('river_basin', $tank->river_basin)" />
                        <x-input-error class="mt-2" :messages="$errors->get('river_basin')" />
                    </div>
                    <div>
                        <x-input-label for="cascade_name" value="Cascade Name" class="tank-rehab-label" />
                        <x-text-input id="cascade_name" name="cascade_name" class="mt-2 tank-rehab-input" :value="old('cascade_name', $tank->cascade_name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('cascade_name')" />
                    </div>
                    <div>
                        <x-input-label for="province" value="Province" class="tank-rehab-label" />
                        <select id="province" name="province" x-model="province" @change="district = ''; dsDivision = ''" class="mt-2 tank-rehab-select">
                            <option value="">Select</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province }}" @selected(old('province', $tank->province) === $province)>{{ $province }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('province')" />
                    </div>
                    <div>
                        <x-input-label for="district" value="District" class="tank-rehab-label" />
                        <select id="district" name="district" x-model="district" @change="dsDivision = ''" class="mt-2 tank-rehab-select">
                            <option value="">Select</option>
                            <template x-for="districtName in districts" :key="districtName">
                                <option :value="districtName" x-text="districtName"></option>
                            </template>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('district')" />
                    </div>
                    <div>
                        <x-input-label for="ds_division" value="DS Division" class="tank-rehab-label" />
                        <select id="ds_division" name="ds_division" x-model="dsDivision" class="mt-2 tank-rehab-select">
                            <option value="">Select</option>
                            <template x-for="division in dsDivisions" :key="division">
                                <option :value="division" x-text="division"></option>
                            </template>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('ds_division')" />
                    </div>
                    <div>
                        <x-input-label for="gn_division" value="GN Division" class="tank-rehab-label" />
                        <x-text-input id="gn_division" name="gn_division" class="mt-2 tank-rehab-input" :value="old('gn_division', $tank->gn_division)" />
                        <x-input-error class="mt-2" :messages="$errors->get('gn_division')" />
                    </div>
                    <div>
                        <x-input-label for="as_centre" value="AS Centre" class="tank-rehab-label" />
                        <x-text-input id="as_centre" name="as_centre" class="mt-2 tank-rehab-input" :value="old('as_centre', $tank->as_centre)" />
                        <x-input-error class="mt-2" :messages="$errors->get('as_centre')" />
                    </div>
                    <div>
                        <x-input-label for="agency" value="Agency" class="tank-rehab-label" />
                        <x-text-input id="agency" name="agency" class="mt-2 tank-rehab-input" :value="old('agency', $tank->agency)" />
                        <x-input-error class="mt-2" :messages="$errors->get('agency')" />
                    </div>
                    <div>
                        <x-input-label for="no_of_family" value="No. of Family" class="tank-rehab-label" />
                        <x-text-input id="no_of_family" name="no_of_family" type="number" min="0" class="mt-2 tank-rehab-input" :value="old('no_of_family', $tank->no_of_family)" />
                        <x-input-error class="mt-2" :messages="$errors->get('no_of_family')" />
                    </div>
                    <div>
                        <x-input-label for="longitude" value="Longitude" class="tank-rehab-label" />
                        <x-text-input id="longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" class="mt-2 tank-rehab-input" :value="old('longitude', $tank->longitude)" />
                        <x-input-error class="mt-2" :messages="$errors->get('longitude')" />
                    </div>
                    <div>
                        <x-input-label for="latitude" value="Latitude" class="tank-rehab-label" />
                        <x-text-input id="latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" class="mt-2 tank-rehab-input" :value="old('latitude', $tank->latitude)" />
                        <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
                    </div>
                </div>
            </div>

            <div class="tank-rehab-section">
                <h3 class="tank-rehab-section-title">Progress And Contract</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <x-input-label for="progress" value="Progress" class="tank-rehab-label" />
                        <x-text-input id="progress" name="progress" type="number" step="0.01" min="0" max="100" class="mt-2 tank-rehab-input" :value="old('progress', $tank->progress)" />
                        <x-input-error class="mt-2" :messages="$errors->get('progress')" />
                    </div>
                    <div>
                        <x-input-label for="contractor" value="Contractor" class="tank-rehab-label" />
                        <x-text-input id="contractor" name="contractor" class="mt-2 tank-rehab-input" :value="old('contractor', $tank->contractor)" />
                        <x-input-error class="mt-2" :messages="$errors->get('contractor')" />
                    </div>
                    <div>
                        <x-input-label for="contractor_contact_number" value="Contractor Contact Number" class="tank-rehab-label" />
                        <x-text-input id="contractor_contact_number" name="contractor_contact_number" class="mt-2 tank-rehab-input" :value="old('contractor_contact_number', $tank->contractor_contact_number)" />
                        <x-input-error class="mt-2" :messages="$errors->get('contractor_contact_number')" />
                    </div>
                    <div>
                        <x-input-label for="contractor_cida_grade" value="Contractor CIDA Grade" class="tank-rehab-label" />
                        <x-text-input id="contractor_cida_grade" name="contractor_cida_grade" class="mt-2 tank-rehab-input" :value="old('contractor_cida_grade', $tank->contractor_cida_grade)" />
                        <x-input-error class="mt-2" :messages="$errors->get('contractor_cida_grade')" />
                    </div>
                    <div>
                        <x-input-label for="construction_start_date" value="Construction Start Date" class="tank-rehab-label" />
                        <x-text-input id="construction_start_date" name="construction_start_date" type="date" class="mt-2 tank-rehab-input" :value="old('construction_start_date', optional($tank->construction_start_date)->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('construction_start_date')" />
                    </div>
                    <div>
                        <x-input-label for="payment" value="Payment" class="tank-rehab-label" />
                        <x-text-input id="payment" name="payment" class="mt-2 tank-rehab-input" list="payment-options" :value="old('payment', $tank->payment)" />
                        <datalist id="payment-options">
                            @foreach ($payments as $payment)
                                <option value="{{ $payment }}">
                            @endforeach
                        </datalist>
                        <x-input-error class="mt-2" :messages="$errors->get('payment')" />
                    </div>
                    <div>
                        <x-input-label for="awarded_date" value="Awarded Date" class="tank-rehab-label" />
                        <x-text-input id="awarded_date" name="awarded_date" type="date" class="mt-2 tank-rehab-input" :value="old('awarded_date', optional($tank->awarded_date)->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('awarded_date')" />
                    </div>
                    <div>
                        <x-input-label for="construction_period_days" value="Construction Period (Days)" class="tank-rehab-label" />
                        <x-text-input id="construction_period_days" name="construction_period_days" type="number" min="0" class="mt-2 tank-rehab-input" :value="old('construction_period_days', $tank->construction_period_days)" />
                        <x-input-error class="mt-2" :messages="$errors->get('construction_period_days')" />
                    </div>
                    <div>
                        <x-input-label for="extension_of_time_months" value="Extension of Time(EOT)(months)" class="tank-rehab-label" />
                        <x-text-input id="extension_of_time_months" name="extension_of_time_months" type="number" min="0" class="mt-2 tank-rehab-input" :value="old('extension_of_time_months', $tank->extension_of_time_months)" />
                        <x-input-error class="mt-2" :messages="$errors->get('extension_of_time_months')" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" class="tank-rehab-label" />
                        <x-text-input id="status" name="status" class="mt-2 tank-rehab-input" list="status-options" :value="old('status', $tank->status)" />
                        <datalist id="status-options">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">
                            @endforeach
                        </datalist>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>
                    <div>
                        <x-input-label for="open_ref_no" value="Open Ref No" class="tank-rehab-label" />
                        <x-text-input id="open_ref_no" name="open_ref_no" class="mt-2 tank-rehab-input" :value="old('open_ref_no', $tank->open_ref_no)" />
                        <x-input-error class="mt-2" :messages="$errors->get('open_ref_no')" />
                    </div>
                    <div class="md:col-span-2 xl:col-span-4">
                        <x-input-label for="contractor_address" value="Contractor Address" class="tank-rehab-label" />
                        <textarea id="contractor_address" name="contractor_address" rows="2" class="mt-2 tank-rehab-textarea">{{ old('contractor_address', $tank->contractor_address) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('contractor_address')" />
                    </div>
                    <div class="md:col-span-2 xl:col-span-4">
                        <x-input-label for="remarks" value="Remarks" class="tank-rehab-label" />
                        <textarea id="remarks" name="remarks" rows="3" class="mt-2 tank-rehab-textarea">{{ old('remarks', $tank->remarks) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('remarks')" />
                    </div>
                </div>
            </div>

            <div class="tank-rehab-section">
                <h3 class="tank-rehab-section-title">Payment And Cost</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <x-input-label for="cumulative_amount" value="Cumulative Amount" class="tank-rehab-label" />
                        <x-text-input id="cumulative_amount" name="cumulative_amount" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('cumulative_amount', $tank->cumulative_amount)" />
                        <x-input-error class="mt-2" :messages="$errors->get('cumulative_amount')" />
                    </div>
                    <div>
                        <x-input-label for="paid_advanced_amount" value="Paid Advanced Amount" class="tank-rehab-label" />
                        <x-text-input id="paid_advanced_amount" name="paid_advanced_amount" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('paid_advanced_amount', $tank->paid_advanced_amount)" />
                        <x-input-error class="mt-2" :messages="$errors->get('paid_advanced_amount')" />
                    </div>
                    <div>
                        <x-input-label for="recommended_ipc_no" value="Recommended IPC No" class="tank-rehab-label" />
                        <x-text-input id="recommended_ipc_no" name="recommended_ipc_no" class="mt-2 tank-rehab-input" :value="old('recommended_ipc_no', $tank->recommended_ipc_no)" />
                        <x-input-error class="mt-2" :messages="$errors->get('recommended_ipc_no')" />
                    </div>
                    <div>
                        <x-input-label for="recommended_ipc_amount" value="Recommended IPC Amount" class="tank-rehab-label" />
                        <x-text-input id="recommended_ipc_amount" name="recommended_ipc_amount" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('recommended_ipc_amount', $tank->recommended_ipc_amount)" />
                        <x-input-error class="mt-2" :messages="$errors->get('recommended_ipc_amount')" />
                    </div>
                    <div>
                        <x-input-label for="base_cost" value="Base Cost" class="tank-rehab-label" />
                        <x-text-input id="base_cost" name="base_cost" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('base_cost', $tank->base_cost)" />
                        <x-input-error class="mt-2" :messages="$errors->get('base_cost')" />
                    </div>
                    <div>
                        <x-input-label for="physical_contingencies" value="Physical Contingencies" class="tank-rehab-label" />
                        <x-text-input id="physical_contingencies" name="physical_contingencies" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('physical_contingencies', $tank->physical_contingencies)" />
                        <x-input-error class="mt-2" :messages="$errors->get('physical_contingencies')" />
                    </div>
                    <div>
                        <x-input-label for="price_contingencies" value="Price Contingencies" class="tank-rehab-label" />
                        <x-text-input id="price_contingencies" name="price_contingencies" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('price_contingencies', $tank->price_contingencies)" />
                        <x-input-error class="mt-2" :messages="$errors->get('price_contingencies')" />
                    </div>
                    <div>
                        <x-input-label for="net_value" value="Net Value" class="tank-rehab-label" />
                        <x-text-input id="net_value" name="net_value" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('net_value', $tank->net_value)" />
                        <x-input-error class="mt-2" :messages="$errors->get('net_value')" />
                    </div>
                    <div>
                        <x-input-label for="vat" value="VAT" class="tank-rehab-label" />
                        <x-text-input id="vat" name="vat" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('vat', $tank->vat)" />
                        <x-input-error class="mt-2" :messages="$errors->get('vat')" />
                    </div>
                    <div>
                        <x-input-label for="grand_total" value="Grand Total" class="tank-rehab-label" />
                        <x-text-input id="grand_total" name="grand_total" type="number" step="0.01" min="0" class="mt-2 tank-rehab-input" :value="old('grand_total', $tank->grand_total)" />
                        <x-input-error class="mt-2" :messages="$errors->get('grand_total')" />
                    </div>
                </div>
            </div>

            <div class="tank-rehab-section">
                <h3 class="tank-rehab-section-title">Construction Images</h3>
                <div class="mt-4 grid gap-5 lg:grid-cols-3">
                    @foreach ([
                        'pre_construction_images' => ['label' => 'Pre Construction Images', 'caption' => 'Before construction starts'],
                        'during_construction_images' => ['label' => 'During Construction Images', 'caption' => 'Work in progress'],
                        'post_construction_images' => ['label' => 'Post Construction Images', 'caption' => 'After completion'],
                    ] as $field => $meta)
                        <div class="rounded-lg border border-emerald-100 bg-white p-4 shadow-sm">
                            <x-input-label :for="$field" :value="$meta['label']" class="tank-rehab-label" />
                            <p class="mt-1 text-xs font-medium text-slate-500">{{ $meta['caption'] }}</p>
                            <input id="{{ $field }}" name="{{ $field }}[]" type="file" accept="image/*" multiple class="tank-rehab-file mt-3 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            <x-input-error class="mt-2" :messages="$errors->get($field)" />
                            <x-input-error class="mt-2" :messages="$errors->get($field.'.*')" />

                            @if (! empty($tank->{$field}))
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    @foreach ($tank->{$field} as $image)
                                        <a href="{{ asset('storage/'.$image) }}" target="_blank" class="group overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                                            <img src="{{ asset('storage/'.$image) }}" alt="{{ $meta['label'] }}" class="h-24 w-full object-cover transition group-hover:scale-105">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-center gap-3 border-t border-emerald-100 bg-emerald-50 px-6 py-5">
            <a href="{{ route('tank-registration.index') }}" class="tank-rehab-secondary">Cancel</a>
            <button type="submit" class="tank-rehab-action">{{ $mode === 'create' ? 'Submit Tank' : 'Update Tank' }}</button>
        </div>
    </div>
</div>

@include('business-information.partials.admin-division-script')
