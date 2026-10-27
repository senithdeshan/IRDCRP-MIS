@php
    $memberDetails = [
        'FOP / Organization' => $eoi->organization_name,
        'Declared Number of Members' => $eoi->number_of_members,
        'Legal Status' => $eoi->legal_status,
        'Registration Number' => $eoi->registration_number,
        'Contact Person' => $eoi->contact_person_name,
        'Contact Person Designation' => $eoi->contact_person_designation,
        'Contact Telephone' => $eoi->contact_person_telephone,
        'Contact Email' => $eoi->contact_person_email,
        'Registered Address' => $eoi->organization_registered_address,
        'Province' => $eoi->province,
        'District' => $eoi->district,
        'DS Division' => $eoi->ds_division,
        'GN Division' => $eoi->gn_division,
        'Agrarian Service Centre' => $eoi->as_centre,
    ];
@endphp

<x-modal name="member-details-{{ $eoi->id }}" focusable>
    <div role="dialog" aria-modal="true" aria-labelledby="member-details-title-{{ $eoi->id }}">
        <div class="flex items-start justify-between gap-4 bg-cyan-900 px-6 py-5 text-white">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-cyan-200">Component 1.2 - Agreement Sign FOP</p>
                <h2 id="member-details-title-{{ $eoi->id }}" class="mt-2 text-xl font-semibold">View Member Details</h2>
                <p class="mt-1 text-sm text-cyan-100">{{ $eoi->eoi_number }} | {{ $eoi->organization_name ?: 'Farmer organization' }}</p>
            </div>
            <button type="button" @click="$dispatch('close')" aria-label="Close member details" class="rounded-md px-3 py-1 text-2xl hover:bg-cyan-800">&times;</button>
        </div>
        <div class="max-h-[70vh] overflow-y-auto bg-slate-50 p-4 sm:p-6">
            <div class="mb-5 rounded-xl border border-cyan-200 bg-cyan-50 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-cyan-800">FOP Membership</p>
                <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $eoi->number_of_members ?? 'N/A' }}</p>
                <p class="mt-1 text-sm text-slate-600">Declared number of organization members</p>
            </div>
            <dl class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
                @foreach ($memberDetails as $label => $value)
                    <div @class(['sm:col-span-2' => in_array($label, ['Registered Address'], true)])>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                        <dd class="mt-1 break-words text-sm font-medium text-slate-900">{{ filled($value) ? $value : 'N/A' }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="mt-4 text-xs leading-5 text-slate-500">This module stores the FOP's declared member count and organization/contact details. Individual member records are not stored here.</p>
        </div>
        <div class="flex justify-end border-t border-slate-200 bg-white px-6 py-4">
            <button type="button" @click="$dispatch('close')" class="rounded-md bg-cyan-800 px-5 py-2 text-sm font-semibold text-white transition hover:bg-cyan-900">Close</button>
        </div>
    </div>
</x-modal>
