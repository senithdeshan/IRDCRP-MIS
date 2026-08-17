<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.3</p>
                <h1 class="text-2xl font-semibold text-slate-950">Youth and Women Entrepreneurs</h1>
                <p class="mt-1 text-sm text-slate-500">Individual entrepreneur records, screening, and business information workflow.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('business-information.template') }}" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Download Template</a>
                <a href="{{ route('business-information.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Add Applicant</a>
            </div>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            <div class="grid gap-4 md:grid-cols-5">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Individuals</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Selected</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['selected']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Rejected</p>
                    <p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($summary['rejected']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Pending</p>
                    <p class="mt-3 text-3xl font-semibold text-amber-700">{{ number_format($summary['pending']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Investment Rs.</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-950">{{ number_format($summary['investment'], 2) }}</p>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[0.72fr_1.28fr]">
                <div class="panel-surface p-6">
                    <h2 class="text-base font-semibold text-slate-950">Individual Workflow</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ([
                            ['label' => 'Business Information', 'caption' => 'Applicant profile and business details', 'count' => $summary['total'], 'route' => 'business-information.index', 'tone' => 'bg-sky-100 text-sky-800'],
                            ['label' => 'Initial Screening', 'caption' => 'Selected, rejected, and pending individuals', 'count' => $summary['selected'] + $summary['rejected'] + $summary['pending'], 'route' => 'business-information.index', 'tone' => 'bg-amber-100 text-amber-800'],
                            ['label' => 'Selected Entrepreneurs', 'caption' => 'Individuals marked selected for next action', 'count' => $summary['selected'], 'route' => 'business-information.index', 'tone' => 'bg-emerald-100 text-emerald-800'],
                        ] as $step)
                            <a href="{{ route($step['route']) }}" class="flex items-center justify-between gap-4 rounded-md border border-slate-200 bg-white px-4 py-3 transition hover:border-emerald-200 hover:bg-emerald-50">
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-950">{{ $step['label'] }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $step['caption'] }}</span>
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $step['tone'] }}">{{ number_format($step['count']) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Gender Summary</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($genderCounts as $label => $count)
                                <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2 text-sm">
                                    <span class="font-semibold text-slate-600">{{ $label ?: 'Unknown' }}</span>
                                    <span class="font-semibold text-slate-950">{{ number_format($count) }}</span>
                                </div>
                            @empty
                                <p class="rounded-md border border-dashed border-slate-200 px-3 py-8 text-center text-sm text-slate-500">No gender data yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="panel-surface p-5 lg:col-span-2">
                        <h3 class="text-sm font-semibold text-slate-950">Province Distribution</h3>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @forelse ($provinceCounts as $label => $count)
                                <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2 text-sm">
                                    <span class="font-semibold text-slate-600">{{ $label ?: 'Unknown' }}</span>
                                    <span class="font-semibold text-slate-950">{{ number_format($count) }}</span>
                                </div>
                            @empty
                                <p class="rounded-md border border-dashed border-slate-200 px-3 py-8 text-center text-sm text-slate-500 sm:col-span-2">No province data yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-surface overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Recent Individual Records</h2>
                        <p class="mt-1 text-sm text-slate-500">Latest youth and women entrepreneur records entered into Business Information.</p>
                    </div>
                    <a href="{{ route('business-information.index') }}" class="w-fit rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Open Business Information</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Individual</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Business</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Screening</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentApplicants as $applicant)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $applicant->applicant_name }}</div>
                                        <div class="text-sm text-slate-500">{{ $applicant->eoi_number }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-800">{{ $applicant->business_name }}</div>
                                        <div class="text-sm text-slate-500">{{ $applicant->legal_status }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        {{ $applicant->province ?: 'N/A' }}<br>
                                        <span class="text-slate-400">{{ $applicant->district ?: 'N/A' }} / {{ $applicant->ds_division ?: 'N/A' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-emerald-100 text-emerald-800' => $applicant->initial_screening_result === 'Selected',
                                            'bg-rose-100 text-rose-800' => $applicant->initial_screening_result === 'Reject',
                                            'bg-slate-100 text-slate-700' => ! in_array($applicant->initial_screening_result, ['Selected', 'Reject'], true),
                                        ])>
                                            {{ $applicant->initial_screening_result ?: 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">No individual entrepreneur records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
