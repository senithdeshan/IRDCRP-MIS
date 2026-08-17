<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Approved Farmer Producer Groups</h1>
            <p class="text-sm text-slate-500">Records approved from the verification field visit pass stage appear here.</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Approved Groups</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Docs Complete</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['documents']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Agreement Signed</p>
                    <p class="mt-3 text-3xl font-semibold text-cyan-700">{{ number_format($summary['agreement_signed']) }}</p>
                </div>
            </div>

            @include('productive-partnership-eois.partials.eoi-period-summary', ['stageLabel' => 'Approved'])

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">EOI / Organization</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Marks</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Construction</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documents</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Agreement Tracking</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
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
                                <tr x-data="{ trackingOpen: false }">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</div>
                                        <div class="text-sm text-slate-600">{{ $eoi->organization_name ?: 'N/A' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ number_format((float) $eoi->interview_marks, 2) }}</span>
                                        <div class="mt-1 text-xs text-slate-400">{{ optional($eoi->approved_at)->format('Y-m-d H:i') ?: 'No approval date' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province }} / {{ $eoi->district }}<br><span class="text-slate-400">{{ $eoi->ds_division }}</span></td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div>Pre: {{ count($eoi->pre_construction_images ?? []) }} images</div>
                                        <div>During: {{ count($eoi->during_construction_images ?? []) }} images</div>
                                        <div>Post: {{ count($eoi->post_construction_images ?? []) }} images</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <div>BR: {{ $eoi->business_registration_image ? 'Uploaded' : 'Pending' }}</div>
                                        <div>Proposal: {{ $eoi->business_proposal_pdf ? 'Uploaded' : 'Pending' }}</div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="fop-tracker-card">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Current Step</p>
                                                    <p class="mt-1 text-sm font-semibold leading-5 text-slate-950">{{ $currentTrackingStage['label'] }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $currentTrackingStage['date'] ?: 'No date entered' }}</p>
                                                </div>
                                                <span class="rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-semibold text-cyan-800">{{ $completedTrackingStages }}/{{ $totalTrackingStages }}</span>
                                            </div>
                                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                                <div class="h-full rounded-full bg-cyan-600" style="width: {{ $trackingPercent }}%;"></div>
                                            </div>
                                            <button type="button" @click="trackingOpen = true" class="mt-3 w-full rounded-md border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-800 transition hover:bg-cyan-100">
                                                View / Update Path
                                            </button>
                                        </div>

                                        <div x-show="trackingOpen" x-cloak class="fop-tracker-modal" @keydown.escape.window="trackingOpen = false">
                                            <div class="fop-tracker-modal-backdrop" @click="trackingOpen = false"></div>
                                            <div class="fop-tracker-modal-panel">
                                                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Agreement path</p>
                                                        <h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $eoi->eoi_number }}</h2>
                                                        <p class="text-sm text-slate-500">{{ $eoi->organization_name ?: 'N/A' }}</p>
                                                    </div>
                                                    <button type="button" @click="trackingOpen = false" class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Close</button>
                                                </div>

                                                <form method="POST" action="{{ route('approved-farmer-producer-groups.agreement-tracking', $eoi) }}" class="fop-tracker-modal-body grid min-h-0 gap-0 lg:grid-cols-[0.92fr_1.08fr]">
                                                    @csrf
                                                    @method('PATCH')

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
                                    <td class="px-5 py-4 text-right align-top">
                                        <div class="record-actions">
                                            <a href="{{ route('selected-eois.show', $eoi) }}" class="record-action-btn record-action-view record-action-wide">View Details</a>
                                            <a href="{{ route('approved-farmer-producer-groups.edit', $eoi) }}" class="record-action-btn record-action-primary record-action-wide">Update</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No approved Farmer Producer Groups yet. Enter interview marks above 50 in Field Visit.</td>
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
