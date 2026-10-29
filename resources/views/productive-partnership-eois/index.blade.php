<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0 text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Received All EOI</h1>
                <p class="mt-1 text-sm text-slate-500">Productive Partnership EOI registry, screening, and initial stage movement.</p>
                </div>
                <a href="{{ route('selected-eois.create') }}" class="shrink-0 rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">Add EOI</a>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('selected-eois.template') }}" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Generate Sheet</a>
                <a href="{{ route('selected-eois.export', request()->query()) }}" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Export Excel</a>
            </div>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total EOIs Received</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Selected for Review Interview</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['initial_yes']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Not Selected for Review Interview</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['initial_no']) }}</p>
                </div>
            </div>


            @include('productive-partnership-eois.partials.geographic-summary')

            <form method="GET" action="{{ route('selected-eois.index') }}" class="filter-panel grid-cols-2 sm:grid-cols-3 lg:grid-cols-[minmax(13rem,1.25fr)_6rem_6rem_8rem_8rem_7rem_8rem_5.5rem_auto_auto]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, organization, reg no, contact, proposal" class="filter-control col-span-2 sm:col-span-3 lg:col-span-1">
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
                <select name="initial_desk_review_status" class="filter-control">
                    <option value="">Desk review</option>
                    @foreach ($reviewStatuses as $status)
                        <option value="{{ $status }}" @selected(request('initial_desk_review_status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <select name="initial_stage" class="filter-control">
                    <option value="">Interview Selection Status</option>
                    <option value="1" @selected(request('initial_stage') === '1')>Yes</option>
                    <option value="0" @selected(request('initial_stage') === '0')>No</option>
                </select>
                <div class="col-span-2 flex items-center gap-2 sm:col-span-3 lg:col-span-full"><button class="filter-action">Search</button><a href="{{ route('selected-eois.index') }}" class="filter-action-secondary">Reset</a></div>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="eoi-registry-table min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach (['eoi_number' => 'EOI Number', 'organization_name' => 'Organization Name', 'province' => 'Province / District', 'sector' => 'Sector', 'proposed_total_investment' => 'Proposed Investment / Expected Grant', 'initial_stage' => 'Interview Selection Status'] as $column => $label)
                                    @php($sortDirection = request('sort') === $column && request('direction') !== 'desc' ? 'desc' : 'asc')
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $sortDirection, 'page' => 1]) }}">{{ $label }} <span aria-hidden="true">{{ request('sort') === $column ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a></th>
                                @endforeach
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                <tr>
                                    <td data-label="EOI Number" class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                        @if ($eoi->eoi_year)
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $eoi->eoi_year }}</span>
                                                @if ($eoi->eoi_call_number)
                                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Call {{ $eoi->eoi_call_number }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td data-label="Organization" class="px-5 py-4"><div class="font-medium text-slate-800">{{ $eoi->organization_name ?: 'N/A' }}</div><div class="mt-1 text-xs text-slate-500">{{ $eoi->contact_person_name ?: 'No contact person' }} · {{ $eoi->contact_person_telephone ?: 'No phone' }}</div></td>
                                    <td data-label="Province / District" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province ?: 'N/A' }} / {{ $eoi->district ?: 'N/A' }}</td>
                                    <td data-label="Sector" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->sector ?: 'N/A' }}</td>
                                    <td data-label="Investment / Grant" class="px-5 py-4 text-sm text-slate-700">
                                        <div class="font-semibold">Rs. {{ number_format((float) $eoi->proposed_total_investment, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-400">Grant: Rs. {{ number_format((float) $eoi->expected_grant_irdcrp, 2) }}</div>
                                    </td>
                                    <td data-label="Interview Selection" class="px-5 py-4"><form method="POST" action="{{ route('selected-eois.initial-stage', $eoi) }}" data-preserve-scroll>@csrf @method('PATCH')<select aria-label="Interview selection status for {{ $eoi->eoi_number }}" name="initial_stage" onchange="this.form.requestSubmit()" @class(['rounded-md border-0 px-3 py-2 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $eoi->initial_stage, 'bg-rose-100 text-rose-800' => !$eoi->initial_stage])><option value="1" @selected($eoi->initial_stage)>Yes</option><option value="0" @selected(!$eoi->initial_stage)>No</option></select></form></td>
                                    <td data-label="Actions" class="px-5 py-4"><div class="flex justify-end gap-2">
                                        <a href="{{ route('selected-eois.show', [$eoi, 'from' => request()->fullUrl()]) }}" data-preserve-scroll-link aria-label="View {{ $eoi->eoi_number }}" title="View" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                        <a href="{{ route('selected-eois.edit', [$eoi, 'from' => request()->fullUrl()]) }}" aria-label="Edit {{ $eoi->eoi_number }}" title="Edit" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 16.5-.8 4.3 4.3-.8L20 7.5 16.5 4 4 16.5Z"/><path d="m14.5 6 3.5 3.5"/></svg></a>
                                        <form method="POST" action="{{ route('selected-eois.destroy', $eoi) }}" data-confirm-title="Delete this EOI record?" data-confirm-message="This action cannot be undone." data-confirm-action="Delete" data-confirm-tone="rose">@csrf @method('DELETE')<button type="submit" aria-label="Delete {{ $eoi->eoi_number }}" title="Delete" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-rose-200 text-rose-700 hover:bg-rose-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2m3 0-.8 14H5.8L5 6m4 4v6m6-6v6"/></svg></button></form>
                                    </div></td>
                                </tr>
                            @empty
                                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No EOI records yet. Upload your Kobo Excel export to start.</td>
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
