<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Selected for Full Proposal Preparation</h1>
            <p class="text-sm text-slate-500">Approved Farmer Producer Groups are reviewed here for full proposal approval or rejection.</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Rejected</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['rejected']) }}</p>
                </div>
            </div>

            @include('productive-partnership-eois.partials.eoi-period-summary', ['stageLabel' => 'Full Proposal'])

            <form method="GET" action="{{ route('full-proposals.index') }}" class="filter-panel md:grid-cols-[minmax(14rem,1fr)_10rem_auto]">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, organization, proposal, contact" class="filter-control">
                <select name="full_proposal_status" class="filter-control">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('full_proposal_status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="filter-action">Search</button>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">EOI / Organization</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Proposal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Marks</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documents</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                        <div class="text-sm text-slate-600">{{ $eoi->organization_name ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $eoi->province }} / {{ $eoi->district }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div class="font-medium text-slate-800">{{ $eoi->business_proposal_title ?: 'N/A' }}</div>
                                        <span class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $eoi->sector ?: 'No sector' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ number_format((float) $eoi->interview_marks, 2) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div>BR: {{ $eoi->business_registration_image ? 'Uploaded' : 'Pending' }}</div>
                                        <div>Proposal PDF: {{ $eoi->business_proposal_pdf ? 'Uploaded' : 'Pending' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-amber-100 text-amber-800' => $eoi->full_proposal_status === 'pending',
                                            'bg-emerald-100 text-emerald-800' => $eoi->full_proposal_status === 'approved',
                                            'bg-rose-100 text-rose-800' => $eoi->full_proposal_status === 'rejected',
                                        ])>{{ $statuses[$eoi->full_proposal_status] ?? 'Pending' }}</span>
                                        @if ($eoi->full_proposal_status === 'approved')
                                            <div class="mt-2">
                                                <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">Pass to Agreement Sign FOP</span>
                                            </div>
                                        @endif
                                        <div class="mt-1 text-xs text-slate-400">{{ optional($eoi->full_proposal_reviewed_at)->format('Y-m-d H:i') ?: 'Not reviewed' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('selected-eois.show', $eoi) }}" class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View</a>

                                            @foreach (['approved' => 'Approved', 'rejected' => 'Rejected'] as $status => $label)
                                                <form method="POST" action="{{ route('full-proposals.status', $eoi) }}" class="flex gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="full_proposal_status" value="{{ $status }}">
                                                    <input name="full_proposal_notes" value="{{ $eoi->full_proposal_notes }}" placeholder="Notes" class="h-8 w-28 rounded-md border-slate-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                                    <button @class([
                                                        'h-8 rounded-md px-3 text-xs font-semibold text-white transition',
                                                        'bg-emerald-700 hover:bg-emerald-800' => $status === 'approved',
                                                        'bg-rose-700 hover:bg-rose-800' => $status === 'rejected',
                                                    ])>{{ $label }}</button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No records ready for full proposal preparation yet.</td>
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
