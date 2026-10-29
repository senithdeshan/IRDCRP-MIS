<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Selected for Proposal Preparation</h1>
            <p class="text-sm text-slate-500">Track draft proposal progression from FPO submission through agreement signing.</p>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-none space-y-5">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Selected for Proposal Preparation</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Received Draft Proposals</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['received_draft']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">ISF Process Completed</p><p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['isf_completed']) }}</p>
                </div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">IEC Evaluation Completed</p><p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['iec_completed']) }}</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">NSC Submission Completed</p><p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['nsc_completed']) }}</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Agreement Signed</p><p class="mt-3 text-3xl font-semibold text-cyan-700">{{ number_format($summary['agreement_signed']) }}</p></div>
            </div>

            @include('productive-partnership-eois.partials.geographic-summary')

            <form method="GET" action="{{ route('approved-farmer-producer-groups.index') }}" class="filter-panel grid-cols-2 sm:grid-cols-3 xl:grid-cols-4" x-data="adminDivisionPicker(@js(config('admin_divisions.provinces', [])), @js(request('province')), @js(request('district')), '')">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, organization, contact, proposal" class="filter-control col-span-2 sm:col-span-3 xl:col-span-2">
                <select name="eoi_year" class="filter-control"><option value="">All years</option>@foreach ($eoiYears as $year)<option value="{{ $year }}" @selected((string) request('eoi_year') === (string) $year)>{{ $year }}</option>@endforeach</select>
                <select name="eoi_call_number" class="filter-control"><option value="">All calls</option>@foreach ($eoiCallNumbers as $call)<option value="{{ $call }}" @selected((string) request('eoi_call_number') === (string) $call)>Call {{ $call }}</option>@endforeach</select>
                <select name="province" x-model="province" @change="district = ''" class="filter-control"><option value="">All provinces</option>@foreach (array_keys(config('admin_divisions.provinces', [])) as $province)<option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>@endforeach</select>
                <select name="district" x-model="district" class="filter-control"><option value="">All districts</option><template x-for="districtName in districts" :key="districtName"><option :value="districtName" x-text="districtName"></option></template></select>
                <select name="sector" class="filter-control"><option value="">All sectors</option>@foreach (\App\Http\Controllers\YouthWomenApplicantController::BUSINESS_SECTORS as $sector)<option value="{{ $sector }}" @selected(request('sector') === $sector)>{{ $sector }}</option>@endforeach</select>
                <select name="proposal_status" class="filter-control"><option value="">All proposal statuses</option>@foreach ($trackingSteps as $key => $label)<option value="{{ $key }}" @selected(request('proposal_status') === $key)>{{ $label }}</option>@endforeach</select>
                <div class="col-span-2 flex gap-2 sm:col-span-3 xl:col-span-4"><button class="filter-action">Search</button><a href="{{ route('approved-farmer-producer-groups.index') }}" class="filter-action-secondary">Reset</a></div>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="approved-proposal-table min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach (['eoi_number' => 'EOI Number', 'organization_name' => 'Organization Name', 'province' => 'Province / District', 'sector' => 'Sector', 'proposal_tracking' => 'Proposal Tracking'] as $column => $label)
                                    @php
                                        $sortDirection = request('sort') === $column && request('direction') !== 'desc' ? 'desc' : 'asc';
                                    @endphp
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"><a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $sortDirection, 'page' => 1]) }}">{{ $label }} <span aria-hidden="true">{{ request('sort') === $column ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a></th>
                                @endforeach
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                @php
                                    $trackingRows = $eoi->agreementTrackingRows();
                                    $currentTrackingStage = $eoi->currentAgreementTrackingStage();
                                    $completedTrackingStages = $eoi->agreementTrackingCompletedCount();
                                    $totalTrackingStages = count($trackingRows);
                                    $trackingPercent = $eoi->agreementTrackingProgressPercent();
                                @endphp
                                <tr x-data="{ trackingOpen: @js($errors->any() && (string) old('tracking_eoi_id') === (string) $eoi->id) }">
                                    <td data-label="EOI Number" class="px-5 py-4 font-semibold text-slate-950">{{ $eoi->eoi_number }}</td>
                                    <td data-label="Organization Name" class="px-5 py-4 text-sm font-medium text-slate-800">{{ $eoi->organization_name ?: 'N/A' }}</td>
                                    <td data-label="Province / District" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province ?: 'N/A' }} / {{ $eoi->district ?: 'N/A' }}</td>
                                    <td data-label="Sector" class="px-5 py-4 text-sm text-slate-600">{{ $eoi->sector ?: 'N/A' }}</td>
                                    <td data-label="Proposal Tracking" class="px-5 py-4 align-top">
                                        <div class="fop-tracker-card">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Current Step</p>
                                                    <p class="mt-1 text-sm font-semibold leading-5 text-slate-950">{{ $currentTrackingStage['label'] }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $currentTrackingStage['date'] ?: 'No date entered' }}</p>
                                                </div>
                                                <span class="rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-semibold text-cyan-800">{{ $currentTrackingStage['number'] }}/{{ $totalTrackingStages }}</span>
                                            </div>
                                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                                <div class="h-full rounded-full bg-cyan-600" style="width: {{ $trackingPercent }}%;"></div>
                                            </div>
                                        </div>

                                        <div x-show="trackingOpen" x-cloak class="fop-tracker-modal" @keydown.escape.window="trackingOpen = false">
                                            <div class="fop-tracker-modal-backdrop" @click="trackingOpen = false"></div>
                                            <div class="fop-tracker-modal-panel">
                                                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Proposal Tracking</p>
                                                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Draft Proposal to Agreement Signing Path</h2>
                                                        <p class="text-sm text-slate-500">{{ $eoi->eoi_number }} · {{ $eoi->organization_name ?: 'N/A' }}</p>
                                                    </div>
                                                    <button type="button" @click="trackingOpen = false" class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Close</button>
                                                </div>

                                                <form method="POST" action="{{ route('approved-farmer-producer-groups.agreement-tracking', ['eoi' => $eoi] + request()->query()) }}" class="fop-tracker-modal-body grid min-h-0 gap-0 lg:grid-cols-[0.92fr_1.08fr]" data-preserve-scroll>
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="tracking_eoi_id" value="{{ $eoi->id }}">

                                                    <div class="fop-tracker-summary-pane border-b border-slate-200 p-5 lg:border-b-0 lg:border-r">
                                                        <div class="rounded-md bg-slate-950 px-4 py-3 text-white">
                                                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-100">Now in</p>
                                                            <p class="mt-1 text-base font-semibold">{{ $currentTrackingStage['label'] }}</p>
                                                            <p class="mt-1 text-xs text-slate-300">{{ $completedTrackingStages }} of {{ $totalTrackingStages }} steps completed</p>
                                                        </div>

                                                        <div class="fop-timeline mt-5">
                                                            @foreach ($trackingRows as $row)
                                                                <div @class([
                                                                    'fop-timeline-item',
                                                                    'is-complete' => $row['complete'],
                                                                    'is-current' => $currentTrackingStage['key'] === $row['key'],
                                                                ])>
                                                                    <span class="fop-timeline-dot"></span>
                                                                    <div class="min-w-0">
                                                                        <p class="text-sm font-semibold text-slate-800">{{ $row['label'] }}</p>
                                                                        <p class="mt-1 text-xs text-slate-500">
                                                                            {{ $row['date'] ?: 'Date pending' }}
                                                                            @if ($row['value'])
                                                                                <span class="text-slate-300">|</span> {{ $row['value'] }}
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="fop-tracker-form-pane p-5">
                                                        <div class="grid gap-3">
                                                            @foreach ($trackingRows as $row)
                                                                <div @class([
                                                                    'fop-tracker-entry',
                                                                    'is-complete' => $row['complete'],
                                                                    'is-current' => $currentTrackingStage['key'] === $row['key'],
                                                                ])>
                                                                    <div>
                                                                        <p class="text-sm font-semibold text-slate-950">{{ $row['label'] }}</p>
                                                                        <p class="text-xs text-slate-500">Enter the date and value/note for this step.</p>
                                                                    </div>
                                                                    <div class="mt-3 grid gap-3 sm:grid-cols-[10rem_1fr]">
                                                                        <input name="fop_agreement_tracking[{{ $row['key'] }}][date]" type="date" value="{{ old('fop_agreement_tracking.'.$row['key'].'.date', $row['date']) }}" class="h-9 rounded-md border-slate-300 text-xs shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                                                        <input name="fop_agreement_tracking[{{ $row['key'] }}][value]" value="{{ old('fop_agreement_tracking.'.$row['key'].'.value', $row['value']) }}" placeholder="Value / reference / note" class="h-9 rounded-md border-slate-300 text-xs shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                                                    </div>
                                                                    <x-input-error class="mt-2" :messages="$errors->get('fop_agreement_tracking.'.$row['key'].'.date')" />
                                                                    <x-input-error class="mt-2" :messages="$errors->get('fop_agreement_tracking.'.$row['key'].'.value')" />
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="sticky bottom-0 mt-5 flex justify-end gap-2 border-t border-slate-200 bg-white pt-4">
                                                            <button type="button" @click="trackingOpen = false" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                                                            <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Save Path</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Actions" class="px-5 py-4 text-left align-top">
                                        <div class="record-actions">
                                            <a href="{{ route('selected-eois.show', [$eoi, 'from' => request()->fullUrl()]) }}" data-preserve-scroll-link aria-label="View {{ $eoi->eoi_number }}" title="View" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                            <button type="button" @click="trackingOpen = true" class="record-action-btn record-action-primary">Update</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No EOIs match these proposal tracking filters.</td>
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
</x-app-layout>
