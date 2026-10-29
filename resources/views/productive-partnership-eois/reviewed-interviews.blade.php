<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Selected for Review Interview</h1>
            <p class="text-sm text-slate-500">Records marked `Yes` in Received All EOI appear here for interview review.</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total EOIs Selected for Review Interview</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Selected for Verification Field Visit</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['passed']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Not Selected for Verification Field Visit</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['not_passed']) }}</p>
                </div>
            </div>


            @include('productive-partnership-eois.partials.geographic-summary')

            <div class="grid gap-4">
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
                <select name="sector" class="filter-control"><option value="">All sectors</option>@foreach (\App\Http\Controllers\YouthWomenApplicantController::BUSINESS_SECTORS as $sector)<option value="{{ $sector }}" @selected(request('sector') === $sector)>{{ $sector }}</option>@endforeach</select>
                <select name="interview_status" class="filter-control">
                    <option value="">Interview Review</option>
                    @foreach ($interviewStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('interview_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="next_step" class="filter-control">
                    <option value="">Next Step</option>
                    <option value="verification" @selected(request('next_step') === 'verification')>Selected for Verification Field Visit</option>
                    <option value="not_passed" @selected(request('next_step') === 'not_passed')>Not Passed</option>
                    <option value="pending" @selected(request('next_step') === 'pending')>Pending</option>
                </select>
                <div class="col-span-2 flex items-center gap-2 sm:col-span-3 lg:col-span-full"><button class="filter-action">Search</button><a href="{{ route('reviewed-interviews.index') }}" class="filter-action-secondary">Reset</a></div>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="reviewed-interviews-table min-w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach (['eoi_number' => 'EOI Number', 'organization_name' => 'Organization Name', 'contact_person_name' => 'Contact Person / Number', 'province' => 'Province / District', 'sector' => 'Sector'] as $column => $label)
                                    @php($sortDirection = request('sort') === $column && request('direction') !== 'desc' ? 'desc' : 'asc')
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $sortDirection, 'page' => 1]) }}">{{ $label }} <span aria-hidden="true">{{ request('sort') === $column ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a></th>
                                @endforeach
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                    <span class="block">Review Interview Result</span>
                                    <span class="mt-2 flex gap-3 text-[10px] tracking-normal">
                                        @foreach (['interview_marks' => 'Marks', 'interview_status' => 'Status'] as $column => $label)
                                            @php($sortDirection = request('sort') === $column && request('direction') !== 'desc' ? 'desc' : 'asc')
                                            <a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $sortDirection, 'page' => 1]) }}">{{ $label }} <span aria-hidden="true">{{ request('sort') === $column ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a>
                                        @endforeach
                                    </span>
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($eois as $eoi)
                                <tr @class([
                                    'align-top transition',
                                    'is-reviewed' => filled($eoi->interview_status),
                                ])>
                                    <td data-label="EOI Number" class="px-5 py-4 @if ($eoi->interview_status) border-l-4 border-emerald-500 @endif">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                    </td>
                                    <td data-label="Organization Name" class="px-5 py-4 text-sm font-medium text-slate-800">{{ $eoi->organization_name ?: 'N/A' }}</td>
                                    <td data-label="Contact Person / Number" class="px-5 py-4 text-sm text-slate-600">
                                        {{ $eoi->contact_person_name ?: 'N/A' }} / {{ $eoi->contact_person_telephone ?: 'No phone' }}
                                    </td>
                                    <td data-label="Province / District" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province ?: 'N/A' }} / {{ $eoi->district ?: 'N/A' }}</td>
                                    <td data-label="Sector" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->sector ?: 'N/A' }}</td>
                                    <td data-label="Review Interview Result" class="px-5 py-4">
                                        <form id="interview-{{ $eoi->id }}" method="POST" action="{{ route('reviewed-interviews.interview-marks', $eoi) }}" class="space-y-2" data-preserve-scroll>
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs font-medium text-slate-600">Marks
                                                    <input name="interview_marks" type="number" min="0" max="100" step="0.01" required value="{{ old('interview_marks', $eoi->interview_marks) }}" placeholder="0–100" class="mt-1 h-8 w-24 rounded-md border-slate-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                </label>
                                            </div>
                                            <label class="block text-xs font-medium text-slate-600">Status
                                                <select name="interview_status" required class="mt-1 block w-full rounded-md border-slate-300 text-xs">@foreach ($interviewStatuses as $value => $label)<option value="{{ $value }}" @selected(old('interview_status', $eoi->interview_status) === $value)>{{ $label }}</option>@endforeach</select>
                                            </label>
                                            <x-input-error :messages="$errors->get('interview_marks')" /><x-input-error :messages="$errors->get('interview_status')" />
                                            <input type="hidden" name="interview_notes" value="{{ old('interview_notes', $eoi->interview_notes) }}">
                                            @if ($eoi->was_resubmitted_for_interview)
                                                <div class="rounded-md bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">
                                                    Resubmitted before: {{ optional($eoi->interview_resubmitted_at)->format('Y-m-d H:i') }}
                                                </div>
                                            @endif
                                        </form>
                                    </td>
                                    <td data-label="Actions" class="px-5 py-4"><div class="flex flex-wrap items-center gap-2"><button type="submit" form="interview-{{ $eoi->id }}" aria-label="Save interview review for {{ $eoi->eoi_number }}" title="Save" class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-emerald-700 text-white hover:bg-emerald-800"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3h12l4 4v14H3V3h2Zm2 0v6h10V3M7 21v-8h10v8"/></svg></button><a href="{{ route('selected-eois.show', [$eoi, 'from' => request()->fullUrl()]) }}" data-preserve-scroll-link aria-label="View {{ $eoi->eoi_number }}" title="View" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></div></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No records selected for reviewed interview yet.</td>
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
