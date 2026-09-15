@php
    $agreementData = $eoi->agreement_eoi_data ?? [];
    $canDownload = $eoi->verification_status === 'approved' && $eoi->full_proposal_status === 'approved';
@endphp
<x-modal name="eoi-data-view-{{ $eoi->id }}" focusable>
    <div role="dialog" aria-modal="true" aria-labelledby="eoi-data-view-title-{{ $eoi->id }}">
        <div class="flex items-start justify-between gap-4 bg-emerald-900 px-6 py-5 text-white">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-200">Component 1.2 · Agreement Sign FOP</p>
                <h2 id="eoi-data-view-title-{{ $eoi->id }}" class="mt-2 text-xl font-semibold">EOI Data View Details</h2>
                <p class="mt-1 text-sm text-emerald-100">{{ $eoi->eoi_number }} · {{ $eoi->organization_name ?: 'Farmer organization' }}</p>
            </div>
            <button type="button" @click="$dispatch('close')" aria-label="Close EOI data details" class="rounded-md px-3 py-1 text-2xl hover:bg-emerald-800">&times;</button>
        </div>
        <div class="max-h-[65vh] space-y-5 overflow-y-auto bg-slate-50 p-4 sm:p-6">
            @if (empty($agreementData))
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center">
                    <p class="font-semibold text-slate-800">No EOI agreement data saved yet</p>
                    <p class="mt-2 text-sm text-slate-500">Use Add EOI Data in Agreement Sign FOP to enter agreement and expenditure details.</p>
                </div>
            @else
                <section class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">Agreement Sign Date</p>
                    <p class="mt-2 text-xl font-semibold text-emerald-950">{{ $agreementData['agreement_sign_date'] ?? 'Date pending' }}</p>
                </section>
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-slate-900">EOI Agreement &amp; Expenditure Path</h3>
                    <p class="mt-1 text-xs text-slate-500">Agreement investment, TR1–TR3 and revised investment, with recorded dates. All amounts in LKR.</p>
                    <ol class="mt-5 space-y-5 border-l-2 border-emerald-100 pl-5">
                        @foreach (['investment' => 'Total Investment', 'tr1' => 'TR1', 'tr2' => 'TR2', 'tr3' => 'TR3', 'revised' => 'Revised Investment'] as $stage => $label)
                            @php
                                $entry = $agreementData[$stage] ?? [];
                                $date = $stage === 'investment' ? ($agreementData['agreement_sign_date'] ?? null) : ($entry['date'] ?? null);
                                $hasAmounts = collect(['own', 'loan', 'grant'])->contains(fn ($source) => filled($entry[$source] ?? null));
                            @endphp
                            <li class="relative rounded-lg border border-slate-200 p-4">
                                <span class="absolute -left-7 top-5 h-3 w-3 rounded-full border-2 border-white {{ $date ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <h4 class="font-semibold text-slate-900">{{ $label }}</h4>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $date ?: 'Date pending' }}</span>
                                </div>
                                <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                                    @foreach (['own' => 'Own', 'loan' => 'Loan', 'grant' => 'Grant'] as $source => $sourceLabel)
                                        <div><dt class="text-xs text-slate-500">{{ $sourceLabel }}</dt><dd class="mt-1 break-all text-sm font-semibold text-slate-800">{{ filled($entry[$source] ?? null) ? number_format((float) $entry[$source], 2) : 'Not entered' }}</dd></div>
                                    @endforeach
                                </dl>
                                <div class="mt-4 flex flex-wrap justify-between gap-2 rounded-md bg-emerald-50 px-3 py-2 text-sm">
                                    <span class="text-emerald-800">{{ $stage === 'investment' ? 'Total Investment' : 'Total Expenditure' }}</span>
                                    <span class="font-semibold text-emerald-900">{{ $hasAmounts ? 'LKR '.number_format((float) ($entry['total'] ?? 0), 2) : 'Not entered' }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-slate-900">Proposal &amp; attachments</h3>
                    @foreach (['Full Proposal PDF' => isset($agreementData['full_proposal']) ? ['proposal' => $agreementData['full_proposal']] : [], 'Other attachments' => $agreementData['attachments'] ?? []] as $group => $documents)
                        <h4 class="mt-4 text-sm font-medium text-slate-700">{{ $group }}</h4>
                        @forelse ($documents as $document => $file)
                            @if ($canDownload)
                                <a href="{{ route('agreement-sign-fop.document', [$eoi, $document]) }}" class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm text-emerald-700 hover:bg-emerald-50"><span class="min-w-0 break-all">{{ $file['name'] }}</span><span class="shrink-0 text-xs font-semibold">Download</span></a>
                            @else
                                <p class="mt-2 break-all text-sm text-slate-500">{{ $file['name'] }} (download available when approved)</p>
                            @endif
                        @empty
                            <p class="mt-2 text-sm text-slate-400">No files uploaded.</p>
                        @endforelse
                    @endforeach
                </section>
            @endif
        </div>
        <div class="flex justify-end border-t border-slate-200 bg-white px-6 py-4">
            <button type="button" @click="$dispatch('close')" class="rounded-md bg-emerald-700 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Close</button>
        </div>
    </div>
</x-modal>
