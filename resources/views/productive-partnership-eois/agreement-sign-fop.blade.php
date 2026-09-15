<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Agreement Sign FOP</h1>
            <p class="text-sm text-slate-500">Only records approved in Full Proposal Preparation appear here for agreement signing.</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Ready for Agreement</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documents Complete</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['documents']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documents Pending</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ number_format($summary['pending_documents']) }}</p>
                </div>
            </div>

            @include('productive-partnership-eois.partials.eoi-period-summary', ['stageLabel' => 'Agreement Sign FOP'])

            <form method="GET" action="{{ route('agreement-sign-fop.index') }}" class="filter-panel md:grid-cols-[minmax(14rem,1fr)_auto_auto]">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, organization, proposal, contact" class="filter-control">
                <button class="filter-action">Search</button>
                <a href="{{ route('agreement-sign-fop.index') }}" class="filter-action-secondary">Clear</a>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">EOI / FOP</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Proposal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Previous Reviews</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documents</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($eois as $eoi)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                        <div class="text-sm text-slate-600">{{ $eoi->organization_name ?: 'N/A' }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $eoi->province }} / {{ $eoi->district }} / {{ $eoi->ds_division }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div class="font-medium text-slate-800">{{ $eoi->business_proposal_title ?: 'N/A' }}</div>
                                        <span class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $eoi->sector ?: 'No sector' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div>Interview: {{ filled($eoi->interview_marks) ? number_format((float) $eoi->interview_marks, 2) : 'Pending' }}</div>
                                        <div>Field Visit: {{ ucfirst(str_replace('_', ' ', $eoi->verification_status ?? 'pending')) }}</div>
                                        <div>Full Proposal: Approved</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ optional($eoi->full_proposal_reviewed_at)->format('Y-m-d H:i') ?: 'No approval date' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div>BR: {{ $eoi->business_registration_image ? 'Uploaded' : 'Pending' }}</div>
                                        <div>Proposal PDF: {{ $eoi->business_proposal_pdf ? 'Uploaded' : 'Pending' }}</div>
                                        @if ($eoi->full_proposal_notes)
                                            <div class="mt-2 max-w-64 text-xs leading-5 text-slate-400">{{ $eoi->full_proposal_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <button type="button" x-data @click="$dispatch('open-modal', 'eoi-data-{{ $eoi->id }}')" class="mb-3 inline-flex rounded-md bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800">Add EOI Data</button>
                                        <div></div>
                                        <a href="{{ route('selected-eois.show', $eoi) }}" class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">View Previous Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No full proposal approved FOP records ready for agreement signing yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $eois->links() }}
                </div>
            </div>
            @foreach ($eois as $eoi)
                @include('productive-partnership-eois.partials.agreement-eoi-data')
            @endforeach
        </div>
    </section>
</x-app-layout>
