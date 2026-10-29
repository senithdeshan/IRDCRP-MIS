<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $eoi->eoi_number }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $eoi->organization_name ?: 'EOI full record' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('selected-eois.edit', [$eoi, 'from' => request('from', route('selected-eois.index'))]) }}" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Edit</a>
                <a href="{{ request('from', route('selected-eois.index')) }}" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Back</a>
                <form method="POST" action="{{ route('selected-eois.destroy', $eoi) }}" data-confirm-title="Delete this EOI record?" data-confirm-message="This action cannot be undone." data-confirm-action="Delete" data-confirm-tone="rose">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @php
                $agreementData = $eoi->agreement_eoi_data ?? [];
                $agreementDate = data_get($eoi->fop_agreement_tracking, 'agreement_sign.date');
                $currentTrackingStage = $eoi->currentAgreementTrackingStage();
                $completedTrackingStages = $eoi->agreementTrackingCompletedCount();
                $totalTrackingStages = count($eoi->agreementTrackingRows());
                $currentStageLabel = $agreementDate ? 'Agreement Signed' : ($eoi->verification_status === 'approved' ? 'Selected for Proposal Preparation' : ($eoi->passesInterviewReview() ? 'Selected for Verification Field Visit' : ($eoi->initial_stage ? 'Selected for Review Interview' : 'EOI Received')));
                $tranche1Complete = filled(data_get($agreementData, 'tr1.date'));
                $tranche2Complete = $tranche1Complete && filled(data_get($agreementData, 'tr2.date'));
                $tranche3Complete = $tranche2Complete && filled(data_get($agreementData, 'tr3.date'));
                $paymentLabel = $tranche3Complete ? 'Tranche 3 Completed' : ($tranche2Complete ? 'Tranche 2 Completed' : ($tranche1Complete ? 'Tranche 1 Completed' : 'Awaiting Tranche 1'));
                $financialProgressLabel = $tranche3Complete ? 'Completed' : (($tranche1Complete || $tranche2Complete) ? 'In Progress' : 'Not Started');
            @endphp
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Current Stage</p>
                    <p class="mt-3 text-lg font-semibold text-slate-950">{{ $currentStageLabel }}</p>
                </div>
                @if ($agreementDate)
                    <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Investment Details</p><p class="mt-3 text-sm font-semibold text-slate-950">Total Rs. {{ number_format((float) data_get($agreementData, 'investment.total', 0), 2) }}</p><p class="mt-1 text-xs text-slate-600">Own {{ number_format((float) data_get($agreementData, 'investment.own', 0), 2) }} · Loan {{ number_format((float) data_get($agreementData, 'investment.loan', 0), 2) }} · Grant {{ number_format((float) data_get($agreementData, 'investment.grant', 0), 2) }}</p></div>
                    <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Payment Status</p><p class="mt-3 text-lg font-semibold text-slate-950">{{ $paymentLabel }}</p></div>
                    <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Financial Progress</p><p class="mt-3 text-lg font-semibold text-emerald-700">{{ $financialProgressLabel }}</p></div>
                @endif
            </div>

            @php
                $sections = [
                    'Organization Information' => [
                        'EOI Number' => $eoi->eoi_number,
                        'Name of the Organization' => $eoi->organization_name,
                        'Number of Members' => $eoi->number_of_members,
                        'Legal Status' => $eoi->legal_status,
                        'Place of Registration' => $eoi->place_of_registration,
                        'Registration Number' => $eoi->registration_number,
                        'Registration Date' => optional($eoi->registration_date)->format('Y-m-d'),
                    ],
                    'Contact and Address' => [
                        'Name of the Contact Person' => $eoi->contact_person_name,
                        'Designation of the Contact Person' => $eoi->contact_person_designation,
                        'Telephone Number of the Contact Person' => $eoi->contact_person_telephone,
                        'Email Address of the Contact Person' => $eoi->contact_person_email,
                        'Registered Address of the Organization' => $eoi->organization_registered_address,
                        'Proposed Business Location Address' => $eoi->proposed_business_location_address,
                    ],
                    'Administrative Location' => [
                        'Province' => $eoi->province,
                        'District' => $eoi->district,
                        'Divisional Secretariat DS Division' => $eoi->ds_division,
                        'GN Division (GND)' => $eoi->gn_division,
                        'Agrarian Service Centre (ASC)' => $eoi->as_centre,
                    ],
                    'Business Proposal' => [
                        'Title of the Business Proposal' => $eoi->business_proposal_title,
                        'Sector' => $eoi->sector,
                        'Proposed Total Investment (Rs)' => number_format((float) $eoi->proposed_total_investment, 2),
                        'Expected Grant from IRDCRP (Rs.)' => number_format((float) $eoi->expected_grant_irdcrp, 2),
                    ],
                    'Initial Screening' => [
                        'Completeness of Application with Mandatory Requirement' => $eoi->completeness_mandatory_requirement,
                        'Status after Initial Desk Review' => $eoi->initial_desk_review_status,
                        'Date of Initial Screening' => optional($eoi->initial_screening_date)->format('Y-m-d'),
                        'Initial Stage' => $eoi->initial_stage ? 'Yes' : 'No',
                    ],
                    'Review Interview' => ['Marks' => filled($eoi->interview_marks) ? number_format((float) $eoi->interview_marks, 2) : 'Pending', 'Status' => $eoi->interview_status_label],
                    'Verification Field Visit' => ['Note' => $eoi->verification_notes, 'Status' => $eoi->verification_status === 'approved' ? 'Selected' : ($eoi->verification_status === 'not_approved' ? 'Not Selected' : 'Pending')],
                    'Proposal Preparation' => ['Current Step' => $currentTrackingStage['label'], 'Date' => $currentTrackingStage['date'], 'Step Number' => $completedTrackingStages.' / '.$totalTrackingStages, 'Progress' => $eoi->agreementTrackingProgressPercent().'%'],
                    'Agreement' => ['Agreement Signed Date' => $agreementDate],
                ];
            @endphp

            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($sections as $title => $items)
                    <div class="panel-surface p-6">
                        <h2 class="text-base font-semibold text-slate-950">{{ $title }}</h2>
                        <dl class="mt-5 divide-y divide-slate-100">
                            @foreach ($items as $label => $value)
                                <div class="grid gap-2 py-3 sm:grid-cols-[0.45fr_0.55fr]">
                                    <dt class="text-sm font-semibold text-slate-500">{{ $label }}</dt>
                                    <dd class="text-sm leading-6 text-slate-800">{{ filled($value) ? $value : 'N/A' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endforeach
            </div>

            @php
                $trackingRows = $eoi->agreementTrackingRows();
                $currentTrackingStage = $eoi->currentAgreementTrackingStage();
                $completedTrackingStages = $eoi->agreementTrackingCompletedCount();
                $totalTrackingStages = count($trackingRows);
            @endphp

            <div class="panel-surface p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Draft Proposal to Agreement Signing Path</h2>
                        <p class="mt-1 text-sm text-slate-500">Draft proposal to agreement signing progress for this record.</p>
                    </div>
                    <span class="w-fit rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">{{ $completedTrackingStages }} / {{ $totalTrackingStages }} completed</span>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-[0.42fr_0.58fr]">
                    <div class="rounded-md bg-slate-950 px-4 py-4 text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-100">Current step</p>
                        <p class="mt-2 text-lg font-semibold">{{ $currentTrackingStage['label'] }}</p>
                        <p class="mt-1 text-sm text-slate-300">{{ $currentTrackingStage['date'] ?: 'No date entered' }}</p>
                        @if ($currentTrackingStage['value'])
                            <p class="mt-3 rounded-md bg-white/10 px-3 py-2 text-sm text-slate-100">{{ $currentTrackingStage['value'] }}</p>
                        @endif
                    </div>

                    <div class="fop-timeline">
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
            </div>

<div class="panel-surface p-6">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Photos & Documentations</h2>
                        <p class="mt-1 text-sm text-slate-500">Pre, during, and post construction uploads with BR and proposal documents.</p>
                    </div>
                    @if ($eoi->verification_status === 'approved')
                        <a href="{{ route('approved-farmer-producer-groups.edit', $eoi) }}" class="w-fit rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Update Uploads</a>
                    @endif
                </div>

                @php
                    $uploadStages = [
                        'Pre Construction' => [
                            'date' => optional($eoi->pre_construction_date)->format('Y-m-d'),
                            'notes' => $eoi->pre_construction_notes,
                            'images' => $eoi->pre_construction_images ?? [],
                        ],
                        'During Construction' => [
                            'date' => optional($eoi->during_construction_date)->format('Y-m-d'),
                            'notes' => $eoi->during_construction_notes,
                            'images' => $eoi->during_construction_images ?? [],
                        ],
                        'Post Construction' => [
                            'date' => optional($eoi->post_construction_date)->format('Y-m-d'),
                            'notes' => $eoi->post_construction_notes,
                            'images' => $eoi->post_construction_images ?? [],
                        ],
                    ];
                @endphp

                <div class="mt-6 grid gap-5 lg:grid-cols-3">
                    @foreach ($uploadStages as $stageTitle => $stage)
                        <div class="rounded-md border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-sm font-semibold text-slate-950">{{ $stageTitle }}</h3>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($stage['images']) }} images</span>
                            </div>
                            <dl class="mt-4 space-y-2 text-sm">
                                <div>
                                    <dt class="font-semibold text-slate-500">Date</dt>
                                    <dd class="text-slate-800">{{ $stage['date'] ?: 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-slate-500">Data / Notes</dt>
                                    <dd class="whitespace-pre-line leading-6 text-slate-800">{{ $stage['notes'] ?: 'N/A' }}</dd>
                                </div>
                            </dl>

                            @if (count($stage['images']))
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    @foreach ($stage['images'] as $image)
                                        <a href="{{ url('storage/'.$image) }}" target="_blank" class="group overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                                            <img src="{{ url('storage/'.$image) }}" alt="{{ $stageTitle }}" class="h-32 w-full object-cover transition group-hover:scale-105">
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4 rounded-md border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
                                    No images uploaded.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <div class="rounded-md border border-slate-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-slate-950">Business Registration Image</h3>
                        @if ($eoi->business_registration_image)
                            <a href="{{ url('storage/'.$eoi->business_registration_image) }}" target="_blank" class="mt-4 block overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                                <img src="{{ url('storage/'.$eoi->business_registration_image) }}" alt="Business Registration" class="max-h-96 w-full object-contain">
                            </a>
                        @else
                            <div class="mt-4 rounded-md border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
                                BR image not uploaded.
                            </div>
                        @endif
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white p-4">
                        <h3 class="text-sm font-semibold text-slate-950">Business Proposal PDF</h3>
                        @if ($eoi->business_proposal_pdf)
                            <div class="mt-4 space-y-3">
                                <a href="{{ url('storage/'.$eoi->business_proposal_pdf) }}" target="_blank" class="inline-flex rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Open PDF</a>
                                <iframe src="{{ url('storage/'.$eoi->business_proposal_pdf) }}" class="h-96 w-full rounded-md border border-slate-200"></iframe>
                            </div>
                        @else
                            <div class="mt-4 rounded-md border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
                                Business Proposal PDF not uploaded.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if ($agreementDate)
            <section class="panel-surface p-6">
                <h2 class="text-base font-semibold text-slate-950">Financial Information</h2>
                <p class="mt-1 text-sm text-slate-500">Full investment, tranche amounts, completion dates, and recorded progress.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach (['investment' => 'Total Investment', 'tr1' => 'Tranche 1', 'tr2' => 'Tranche 2', 'tr3' => 'Tranche 3', 'revised' => 'Revised Investment'] as $stage => $label)
                        @php($entry = data_get($agreementData, $stage, []))
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-semibold text-slate-900">{{ $label }}</h3>
                                @if (in_array($stage, ['tr1', 'tr2', 'tr3'], true))
                                    @php($trancheComplete = filled($entry['date'] ?? null))
                                    <span @class(['rounded-full px-2 py-1 text-[0.65rem] font-semibold', 'bg-emerald-100 text-emerald-800' => $trancheComplete, 'bg-slate-100 text-slate-600' => !$trancheComplete])>{{ $trancheComplete ? 'Completed' : 'Pending' }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-xs text-slate-500">{{ $stage === 'investment' ? $agreementDate : ($entry['date'] ?? 'Date pending') }}</p>
                            @foreach (['own' => 'Own', 'loan' => 'Loan', 'grant' => 'Grant'] as $source => $sourceLabel)
                                <p class="mt-1 text-xs text-slate-600">{{ $sourceLabel }}: Rs. {{ number_format((float) ($entry[$source] ?? 0), 2) }}</p>
                            @endforeach
                            <p class="mt-2 text-sm font-semibold text-slate-900">Total: Rs. {{ number_format((float) ($entry['total'] ?? 0), 2) }}</p>
                            <x-agreement-progress :stage="$stage" :data="$agreementData" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
                @if (auth()->user()?->role === 'super_admin')
            <div class="panel-surface p-6">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Change History</h2>
                        <p class="mt-1 text-sm text-slate-500">Every saved interview status change for this EOI.</p>
                    </div>
                    @if ($eoi->was_resubmitted_for_interview)
                        <span class="w-fit rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Resubmitted before</span>
                    @endif
                </div>

                @if ($eoi->interviewHistories->isNotEmpty())
                    <div class="mt-5 overflow-hidden rounded-md border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Changed At</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Change</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Marks</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Notes</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($eoi->interviewHistories as $history)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ optional($history->changed_at)->format('Y-m-d H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $history->from_interview_status_label ?: 'No previous status' }}</span>
                                                <span class="text-slate-400">to</span>
                                                <span @class([
                                                    'rounded-full px-3 py-1',
                                                    'bg-emerald-100 text-emerald-800' => $history->to_interview_status === 'likely_mature',
                                                    'bg-amber-100 text-amber-800' => in_array($history->to_interview_status, ['likely_immature', 'likely_immature_possible_to_improve', 'resubmit'], true),
                                                    'bg-rose-100 text-rose-800' => $history->to_interview_status === 'ineligible',
                                                ])>{{ $history->to_interview_status_label ?: $history->to_interview_status }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ filled($history->interview_marks) ? number_format((float) $history->interview_marks, 2) : 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm leading-6 text-slate-600">{{ $history->interview_notes ?: 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $history->changedBy?->name ?: 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mt-5 rounded-md border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        No interview status changes saved yet.
                    </div>
                @endif
            </div>
            @endif


</section>
</x-app-layout>
