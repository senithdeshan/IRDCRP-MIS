<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Selected For Verification Field Visit Pass</h1>
            <p class="text-sm text-slate-500">Interview-passed EOIs appear here for Approved / Not Approved field decisions.</p>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Field Visit Pass List</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Pending</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ number_format($summary['pending']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Approved</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['approved']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Not Approved</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['not_approved']) }}</p>
                </div>
            </div>

            @include('productive-partnership-eois.partials.eoi-period-summary', ['stageLabel' => 'Verification Field Visit Pass'])

            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-5 py-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Selected For Verification Field Visit Pass</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Ready for Approved / Not Approved decision</h2>
                        <p class="mt-1 text-sm text-slate-600">These EOIs have moved from interview review to the field visit decision stage.</p>
                    </div>
                    <div class="rounded-md bg-white px-4 py-3 text-center shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Field Visit Pass Queue</p>
                        <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ number_format($summary['total']) }}</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('field-visits.index') }}" class="filter-panel grid-cols-2 sm:grid-cols-3 lg:grid-cols-[minmax(13rem,1.25fr)_6rem_6rem_8rem_8rem_11rem_10rem_auto_auto]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, organization, contact, proposal" class="filter-control col-span-2 sm:col-span-3 lg:col-span-1">
                <select name="eoi_year" class="filter-control">
                    <option value="">All years</option>
                    @foreach ($eoiYears as $year)
                        <option value="{{ $year }}" @selected((string) request('eoi_year') === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
                <select name="eoi_call_number" class="filter-control">
                    <option value="">All calls</option>
                    @foreach ($eoiCallNumbers as $callNumber)
                        <option value="{{ $callNumber }}" @selected((string) request('eoi_call_number') === (string) $callNumber)>Call {{ $callNumber }}</option>
                    @endforeach
                </select>
                <select name="province" x-model="province" @change="district = ''" class="filter-control">
                    <option value="">All provinces</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>
                    @endforeach
                </select>
                <select name="district" x-model="district" class="filter-control">
                    <option value="">All districts</option>
                    <template x-for="districtName in districts" :key="districtName">
                        <option :value="districtName" x-text="districtName"></option>
                    </template>
                </select>
                <select name="interview_status" class="filter-control">
                    <option value="">Interview Review</option>
                    @foreach ($interviewStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('interview_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="verification_status" class="filter-control">
                    <option value="">Verification</option>
                    @foreach ($verificationStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('verification_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="filter-action">Search</button>
                <a href="{{ route('field-visits.index') }}" class="filter-action-secondary">Clear</a>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Passed Candidate</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Interview Result</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Field Visit Area</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Business Proposal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Verification Status</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                <tr class="align-top transition hover:bg-emerald-100/55" style="background-color: #ecfdf5;">
                                    <td class="border-l-4 border-emerald-500 px-5 py-4">
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Passed to next stage</span>
                                        <div class="mt-3 font-semibold text-slate-950">{{ $eoi->organization_name ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500">{{ $eoi->eoi_number }}</div>
                                        <div class="mt-3 text-sm text-slate-600">
                                            {{ $eoi->contact_person_name ?: 'N/A' }}<br>
                                            <span class="text-slate-400">{{ $eoi->contact_person_telephone ?: 'No phone' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="w-56 rounded-md border border-emerald-200 bg-emerald-50 p-3">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Interview Passed</p>
                                            <div class="mt-2 flex items-end gap-2">
                                                <span class="text-3xl font-semibold leading-none text-emerald-700">{{ number_format((float) $eoi->interview_marks, 2) }}</span>
                                                <span class="pb-1 text-xs font-semibold text-slate-500">/ 100</span>
                                            </div>
                                            @if ($eoi->interview_status_label)
                                                <div class="mt-3 rounded-md bg-white px-2 py-1 text-xs font-semibold text-slate-700">{{ $eoi->interview_status_label }}</div>
                                            @endif
                                            @if ($eoi->was_resubmitted_for_interview)
                                                <div class="mt-2 rounded-md bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700">Resubmitted before: {{ optional($eoi->interview_resubmitted_at)->format('Y-m-d H:i') }}</div>
                                            @endif
                                            <div class="mt-2 text-xs leading-5 text-slate-500">{{ $eoi->interview_notes ?: 'No interview notes' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-slate-800">{{ $eoi->province ?: 'N/A' }}</div>
                                        <div class="mt-1 text-sm text-slate-600">{{ $eoi->district ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $eoi->ds_division ?: 'N/A' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="max-w-60 text-sm font-semibold leading-5 text-slate-800">{{ $eoi->business_proposal_title ?: 'N/A' }}</div>
                                        <div class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $eoi->sector ?: 'No sector' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-amber-100 text-amber-800' => $eoi->verification_status === 'pending',
                                            'bg-emerald-100 text-emerald-800' => $eoi->verification_status === 'approved',
                                            'bg-rose-100 text-rose-800' => $eoi->verification_status === 'not_approved',
                                        ])>{{ $verificationStatuses[$eoi->verification_status] ?? 'Pending' }}</span>
                                        <div class="mt-2 text-xs font-semibold text-slate-500">{{ optional($eoi->verified_at)->format('Y-m-d H:i') ?: 'Awaiting field decision' }}</div>
                                        @if ($eoi->verification_notes)
                                            <div class="mt-2 max-w-52 rounded-md bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600">{{ $eoi->verification_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right align-top">
                                        <div class="record-actions">
                                            <a href="{{ route('selected-eois.show', $eoi) }}" class="record-action-btn record-action-view record-action-wide">View Details</a>
                                            @foreach (['approved' => 'Approved', 'not_approved' => 'Not Approved'] as $status => $label)
                                                <form method="POST" action="{{ route('field-visits.verification-status', $eoi) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="verification_status" value="{{ $status }}">
                                                    <input name="verification_notes" value="{{ $eoi->verification_notes }}" placeholder="Verification notes" class="block h-8 w-full rounded-md border-slate-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                    <button type="submit" @class([
                                                        'record-action-btn record-action-wide',
                                                        'record-action-yes' => $status === 'approved',
                                                        'record-action-no' => $status === 'not_approved',
                                                    ])>{{ $label }}</button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No records selected for verification field visit pass yet. Save interview marks above 50 in Reviewed Interview.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $eois->links() }}
                </div>
            </div>
        </div>
    </section>

    @include('business-information.partials.admin-division-script')
</x-app-layout>
