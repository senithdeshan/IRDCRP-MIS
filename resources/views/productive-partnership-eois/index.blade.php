<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Selected All EOI</h1>
                <p class="mt-1 text-sm text-slate-500">Productive Partnership EOI registry, screening, and initial stage movement.</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">All EOI</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Initial Yes</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['initial_yes']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Initial No</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['initial_no']) }}</p>
                </div>
            </div>

            @include('productive-partnership-eois.partials.eoi-period-summary', ['stageLabel' => 'All EOI'])

            <div class="grid gap-5 xl:grid-cols-[0.75fr_1.25fr]">
                <div class="panel-surface p-5">
                    <h2 class="text-base font-semibold text-slate-950">Bulk Upload</h2>
                    <p class="mt-1 text-sm text-slate-500">Upload Kobo exported `.xlsx` Excel sheet only. Existing EOI numbers will be updated.</p>

                    <form method="POST" action="{{ route('selected-eois.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <input type="file" name="eoi_file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                        <x-input-error class="mt-2" :messages="$errors->get('eoi_file')" />
                        <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">Import EOI Sheet</button>
                    </form>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Initial Stage</h3>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Yes</span>
                                <span class="font-semibold text-emerald-700">{{ $stageCounts[1] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">No</span>
                                <span class="font-semibold text-rose-700">{{ $stageCounts[0] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Desk Review</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($reviewStatusCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label ?: 'Pending' }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No records yet.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">All Provinces</h3>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                            @forelse ($provinceCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label }}</span>
                                    <span class="font-semibold text-slate-950">{{ number_format($count) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No records yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

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
                <input name="sector" value="{{ request('sector') }}" placeholder="Sector" class="filter-control">
                <select name="initial_desk_review_status" class="filter-control">
                    <option value="">Desk review</option>
                    @foreach ($reviewStatuses as $status)
                        <option value="{{ $status }}" @selected(request('initial_desk_review_status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <select name="initial_stage" class="filter-control">
                    <option value="">Stage</option>
                    <option value="1" @selected(request('initial_stage') === '1')>Yes</option>
                    <option value="0" @selected(request('initial_stage') === '0')>No</option>
                </select>
                <button class="filter-action">Search</button>
                <a href="{{ route('selected-eois.index') }}" class="filter-action-secondary">Clear</a>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">EOI / Organization</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Members</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contact</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Proposal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Investment / Grant</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Desk Review</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                        @if ($eoi->eoi_year)
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $eoi->eoi_year }}</span>
                                                @if ($eoi->eoi_call_number)
                                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Call {{ $eoi->eoi_call_number }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="mt-1 max-w-xs text-sm font-medium text-slate-700">{{ $eoi->organization_name ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $eoi->legal_status ?: 'No legal status' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $eoi->number_of_members ?: 'N/A' }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div class="font-medium text-slate-800">{{ $eoi->contact_person_name ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $eoi->contact_person_designation ?: 'No designation' }}</div>
                                        <div class="mt-1">{{ $eoi->contact_person_telephone ?: 'No phone' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div>{{ $eoi->province ?: 'N/A' }} / {{ $eoi->district ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $eoi->ds_division ?: 'No DS division' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div class="max-w-xs font-medium text-slate-800">{{ $eoi->business_proposal_title ?: 'N/A' }}</div>
                                        <span class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $eoi->sector ?: 'No sector' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-700">
                                        <div class="font-semibold">Rs. {{ number_format((float) $eoi->proposed_total_investment, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-400">Grant: Rs. {{ number_format((float) $eoi->expected_grant_irdcrp, 2) }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $eoi->initial_desk_review_status ?: 'Pending' }}</span>
                                        <div class="mt-1 text-xs text-slate-400">{{ optional($eoi->initial_screening_date)->format('Y-m-d') ?: 'No date' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top">
                                        <div class="record-actions">
                                            <span @class([
                                                'record-status-pill',
                                                'is-yes' => $eoi->initial_stage,
                                                'is-no' => ! $eoi->initial_stage,
                                            ])>{{ $eoi->initial_stage ? 'Initial: Yes' : 'Initial: No' }}</span>
                                            <div class="record-action-grid">
                                                <a href="{{ route('selected-eois.show', $eoi) }}" class="record-action-btn record-action-view record-action-wide">View Details</a>
                                                <form method="POST" action="{{ route('selected-eois.initial-stage', $eoi) }}" class="record-action-form" data-confirm-title="Mark as Initial Stage Yes?" data-confirm-message="This EOI will move to the Field Visit list." data-confirm-action="Yes, move" data-confirm-tone="emerald">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="initial_stage" value="1">
                                                    <button type="submit" class="record-action-btn record-action-yes">Yes</button>
                                            </form>
                                                <form method="POST" action="{{ route('selected-eois.initial-stage', $eoi) }}" class="record-action-form" data-confirm-title="Mark as Initial Stage No?" data-confirm-message="This EOI will be removed from the Field Visit list." data-confirm-action="Yes, mark no" data-confirm-tone="rose">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="initial_stage" value="0">
                                                    <button type="submit" class="record-action-btn record-action-no">No</button>
                                            </form>
                                                <form method="POST" action="{{ route('selected-eois.destroy', $eoi) }}" class="record-action-form record-action-wide" data-confirm-title="Delete this EOI record?" data-confirm-message="This action cannot be undone." data-confirm-action="Delete" data-confirm-tone="rose">
                                                @csrf
                                                @method('DELETE')
                                                    <button type="submit" class="record-action-btn record-action-danger">Delete</button>
                                            </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">No EOI records yet. Upload your Kobo Excel export to start.</td>
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
