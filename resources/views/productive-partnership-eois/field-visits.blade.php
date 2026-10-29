<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Selected for Verification Field Visit</h1>
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

            <div class="grid gap-4 md:grid-cols-3">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Selected for Verification Field Visit</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Selected for Draft Proposal Preparation</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['approved']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Not Selected for Draft Proposal Preparation</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['not_approved']) }}</p>
                </div>
            </div>


            @include('productive-partnership-eois.partials.geographic-summary')

            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-5 py-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Selected for Verification Field Visit</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Ready for Approved / Not Approved decision</h2>
                        <p class="mt-1 text-sm text-slate-600">These EOIs have moved from interview review to the field visit decision stage.</p>
                    </div>
                    <div class="rounded-md bg-white px-4 py-3 text-center shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Field Visit Pass Queue</p>
                        <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ number_format($summary['total']) }}</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('field-visits.index') }}" class="filter-panel grid-cols-2 sm:grid-cols-3 lg:grid-cols-[minmax(13rem,1.25fr)_6rem_6rem_8rem_8rem_10rem_auto_auto]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
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
                <select name="verification_status" class="filter-control"><option value="">Field Visit Selection Status</option><option value="approved" @selected(request('verification_status') === 'approved')>Selected</option><option value="not_approved" @selected(request('verification_status') === 'not_approved')>Not Selected</option></select>
                <div class="col-span-2 flex items-center gap-2 sm:col-span-3 lg:col-span-full"><button class="filter-action">Search</button><a href="{{ route('field-visits.index') }}" class="filter-action-secondary">Reset</a></div>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="field-visits-table min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach (['eoi_number' => 'EOI Number', 'organization_name' => 'Organization Name', 'province' => 'Province / District', 'sector' => 'Sector', 'interview_status' => 'Review Interview Result', 'verification_status' => 'Verification Field Visit Status'] as $column => $label)
                                    @php($sortDirection = request('sort') === $column && request('direction') !== 'desc' ? 'desc' : 'asc')
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $sortDirection, 'page' => 1]) }}">{{ $label }} <span aria-hidden="true">{{ request('sort') === $column ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a></th>
                                @endforeach
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                <tr class="align-top transition">
                                    <td data-label="EOI Number" class="border-l-4 border-emerald-500 px-5 py-4 font-semibold text-slate-950">{{ $eoi->eoi_number }}</td>
                                    <td data-label="Organization Name" class="px-5 py-4 text-sm font-medium text-slate-800">{{ $eoi->organization_name ?: 'N/A' }}</td>
                                    <td data-label="Province / District" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province ?: 'N/A' }} / {{ $eoi->district ?: 'N/A' }}</td>
                                    <td data-label="Sector" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->sector ?: 'N/A' }}</td>
                                    <td data-label="Review Interview Result" class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">{{ $eoi->interview_status_label ?: 'N/A' }}</span>
                                    </td>
                                    <td data-label="Verification Field Visit Status" class="px-5 py-4">
                                        <form id="verification-{{ $eoi->id }}" method="POST" action="{{ route('field-visits.verification-status', ['eoi' => $eoi] + request()->query()) }}" data-preserve-scroll class="space-y-2">@csrf @method('PATCH')
                                            <label class="block text-xs font-semibold text-slate-600">Field visit notes
                                                <textarea name="verification_notes" rows="2" maxlength="5000" placeholder="Add field visit notes or remarks" class="mt-1 block w-full rounded-md border-slate-300 text-xs">{{ old('verification_notes', $eoi->verification_notes) }}</textarea>
                                            </label>
                                            <label class="block text-xs font-semibold text-slate-600">Status
                                                <select name="verification_status" required class="mt-1 block w-full rounded-md border-slate-300 text-xs"><option value="">Select status</option><option value="approved" @selected($eoi->verification_status === 'approved')>Selected</option><option value="not_approved" @selected($eoi->verification_status === 'not_approved')>Not Selected</option></select>
                                            </label>
                                        </form>
                                    </td>
                                    <td data-label="Actions" class="px-5 py-4 text-right align-top">
                                        <div class="flex justify-end gap-2"><button type="submit" form="verification-{{ $eoi->id }}" aria-label="Save verification for {{ $eoi->eoi_number }}" title="Save" class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-emerald-700 text-white hover:bg-emerald-800"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3h12l4 4v14H3V3h2Zm2 0v6h10V3M7 21v-8h10v8"/></svg></button><a href="{{ route('selected-eois.show', [$eoi, 'from' => request()->fullUrl()]) }}" data-preserve-scroll-link aria-label="View {{ $eoi->eoi_number }}" title="View" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No records have a review interview status that advances them to field visit yet.</td>
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
