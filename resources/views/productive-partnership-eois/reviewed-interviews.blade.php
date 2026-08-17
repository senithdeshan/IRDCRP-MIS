<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Selected for Reviewed Interview</h1>
            <p class="text-sm text-slate-500">Records marked `Yes` in Selected All EOI appear here for interview review.</p>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Reviewed Interview List</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Field Visit Pass</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['passed']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Not Passed</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['not_passed']) }}</p>
                </div>
            </div>

            @include('productive-partnership-eois.partials.eoi-period-summary', ['stageLabel' => 'Reviewed Interview'])

            <div class="grid gap-4 lg:grid-cols-[1fr_1fr]">
                <div class="panel-surface p-5">
                    <h2 class="text-base font-semibold text-slate-950">Interview Review Summary</h2>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($interviewStatuses as $value => $label)
                            <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2 text-sm">
                                <span class="font-semibold text-slate-600">{{ $label }}</span>
                                <span class="font-semibold text-slate-950">{{ number_format($interviewStatusCounts[$value] ?? 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel-surface p-5">
                    <h2 class="text-base font-semibold text-slate-950">Next Step Summary</h2>
                    <div class="mt-4 grid gap-2 sm:grid-cols-3">
                        <div class="rounded-md bg-emerald-50 px-3 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Field Visit Pass</p>
                            <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ number_format($nextStepSummary['verification']) }}</p>
                        </div>
                        <div class="rounded-md bg-rose-50 px-3 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-rose-700">Not Passed</p>
                            <p class="mt-2 text-2xl font-semibold text-rose-700">{{ number_format($nextStepSummary['not_passed']) }}</p>
                        </div>
                        <div class="rounded-md bg-amber-50 px-3 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-amber-700">Pending</p>
                            <p class="mt-2 text-2xl font-semibold text-amber-700">{{ number_format($nextStepSummary['pending']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('reviewed-interviews.index') }}" class="filter-panel grid-cols-2 sm:grid-cols-3 lg:grid-cols-[minmax(13rem,1.25fr)_6rem_6rem_8rem_8rem_11rem_10rem_auto_auto]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
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
                <select name="next_step" class="filter-control">
                    <option value="">Next Step</option>
                    <option value="verification" @selected(request('next_step') === 'verification')>Selected For Verification Field Visit Pass</option>
                    <option value="not_passed" @selected(request('next_step') === 'not_passed')>Not Passed</option>
                    <option value="pending" @selected(request('next_step') === 'pending')>Pending</option>
                </select>
                <button class="filter-action">Search</button>
                <a href="{{ route('reviewed-interviews.index') }}" class="filter-action-secondary">Clear</a>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="reviewed-interviews-table min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">EOI / Organization</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contact</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Proposal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Interview Review</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Next Step</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($eois as $eoi)
                                <tr @class([
                                    'align-top transition',
                                    'is-reviewed' => filled($eoi->interview_status),
                                ])>
                                    <td class="px-5 py-4 @if ($eoi->interview_status) border-l-4 border-emerald-500 @endif">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                        <div class="text-sm text-slate-600">{{ $eoi->organization_name }}</div>
                                        <a href="{{ route('selected-eois.show', $eoi) }}" class="mt-3 inline-flex rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View Details</a>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        {{ $eoi->contact_person_name ?: 'N/A' }}<br>
                                        <span class="text-slate-400">{{ $eoi->contact_person_telephone ?: 'No phone' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province }} / {{ $eoi->district }}<br><span class="text-slate-400">{{ $eoi->ds_division }}</span></td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $eoi->business_proposal_title ?: 'N/A' }}<br><span class="text-slate-400">{{ $eoi->sector }}</span></td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('reviewed-interviews.interview-marks', $eoi) }}" class="min-w-64 space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center gap-2">
                                                <input name="interview_marks" type="number" min="0" max="100" step="0.01" value="{{ old('interview_marks', $eoi->interview_marks) }}" placeholder="0-100" class="h-8 w-16 rounded-md border-slate-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                <button type="submit" class="h-8 rounded-md bg-slate-950 px-3 text-xs font-semibold text-white transition hover:bg-slate-800">Save</button>
                                            </div>
                                            <div class="grid gap-1">
                                                @foreach ($interviewStatuses as $value => $label)
                                                    <label @class([
                                                        'flex cursor-pointer items-center gap-2 rounded-md border px-2 py-1 text-[11px] font-semibold transition',
                                                        'border-emerald-300 bg-emerald-50 text-emerald-800' => old('interview_status', $eoi->interview_status) === $value,
                                                        'border-slate-200 text-slate-600 hover:border-emerald-200 hover:bg-emerald-50' => old('interview_status', $eoi->interview_status) !== $value,
                                                    ])>
                                                        <input type="radio" name="interview_status" value="{{ $value }}" required class="h-3.5 w-3.5 border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('interview_status', $eoi->interview_status) === $value)>
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <input name="interview_notes" value="{{ old('interview_notes', $eoi->interview_notes) }}" placeholder="Notes" class="block h-8 w-full rounded-md border-slate-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            @if ($eoi->was_resubmitted_for_interview)
                                                <div class="rounded-md bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">
                                                    Resubmitted before: {{ optional($eoi->interview_resubmitted_at)->format('Y-m-d H:i') }}
                                                </div>
                                            @endif
                                        </form>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($eoi->interview_status_label)
                                            <div class="mb-2">
                                                <span @class([
                                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                                    'bg-emerald-100 text-emerald-800' => $eoi->interview_status === 'likely_mature',
                                                    'bg-amber-100 text-amber-800' => in_array($eoi->interview_status, ['likely_immature', 'likely_immature_possible_to_improve', 'resubmit'], true),
                                                    'bg-rose-100 text-rose-800' => $eoi->interview_status === 'ineligible',
                                                ])>{{ $eoi->interview_status_label }}</span>
                                            </div>
                                        @endif
                                        @if ((float) $eoi->interview_marks > 50)
                                            <div class="space-y-2">
                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Selected For Verification Field Visit Pass</span>
                                                <a href="{{ route('field-visits.index') }}" class="inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100">Open next step</a>
                                            </div>
                                        @elseif (filled($eoi->interview_marks))
                                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">Not passed interview</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Interview marks pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No records selected for reviewed interview yet.</td>
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
