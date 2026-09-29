<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Farmer Group Information</h1>
                <p class="mt-1 text-sm text-slate-500">Farmer group records, business proposals, and initial screening.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('farmer-organizations.template') }}" class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Download Template</a>
                <a href="{{ route('farmer-organizations.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Add Farmer Group</a>
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

            <div class="grid gap-4 md:grid-cols-4">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Farmer Groups</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Investment Rs.</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-950">{{ number_format($summary['investment'], 2) }}</p>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="panel-surface p-5">
                    <h2 class="text-base font-semibold text-slate-950">Bulk Upload</h2>
                    <p class="mt-1 text-sm text-slate-500">Upload the completed Excel template or a CSV with the same columns. Existing EOI numbers will be updated.</p>

                    <form method="POST" action="{{ route('farmer-organizations.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <input type="file" name="eoi_file" accept=".xlsx,.csv,.txt" required aria-label="Farmer group import file" class="block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                        <x-input-error class="mt-2" :messages="$errors->get('eoi_file')" />
                        <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">Import Farmer Groups</button>
                    </form>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Legal Status</h3>
                        <div class="mt-4 space-y-2">
                            @foreach ($legalStatusCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label ?: 'Unknown' }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Screening</h3>
                        <div class="mt-4 space-y-2">
                            @foreach ($screeningCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label ?: 'Pending' }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Top Provinces</h3>
                        <div class="mt-4 space-y-2">
                            @foreach ($provinceCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('farmer-organizations.records') }}" class="panel-surface grid gap-3 p-4 md:grid-cols-[1.4fr_0.7fr_0.8fr_0.8fr_0.8fr_auto]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, group, registration, contact, proposal" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <select name="legal_status" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All legal statuses</option>
                    @foreach ($legalStatusCounts->keys()->filter() as $legalStatus)
                        <option value="{{ $legalStatus }}" @selected(request('legal_status') === $legalStatus)>{{ $legalStatus }}</option>
                    @endforeach
                </select>
                <select name="province" x-model="province" @change="district = ''" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All provinces</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>
                    @endforeach
                </select>
                <select name="district" x-model="district" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All districts</option>
                    <template x-for="districtName in districts" :key="districtName">
                        <option :value="districtName" x-text="districtName"></option>
                    </template>
                </select>
                <select name="initial_screening_result" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All results</option>
                    @foreach ($screeningResults as $result)
                        <option value="{{ $result }}" @selected(request('initial_screening_result') === $result)>{{ $result }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Search</button>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Farmer Group</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Business</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Investment</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Result</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($applicants as $applicant)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $applicant->organization_name }}</div>
                                        <div class="text-sm text-slate-500">{{ $applicant->eoi_number }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $applicant->registration_number }} / {{ $applicant->contact_person_telephone }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-800">{{ $applicant->business_proposal_title }}</div>
                                        <div class="text-sm text-slate-500">{{ $applicant->legal_status }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        {{ $applicant->province ?: 'N/A' }}<br>
                                        <span class="text-slate-400">{{ $applicant->district ?: 'N/A' }} / {{ $applicant->ds_division ?: 'N/A' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-800">Rs. {{ number_format((float) $applicant->proposed_total_investment, 2) }}</td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-emerald-100 text-emerald-800' => $applicant->farmer_screening_result === 'Selected',
                                            'bg-rose-100 text-rose-800' => $applicant->farmer_screening_result === 'Reject',
                                            'bg-slate-100 text-slate-700' => ! in_array($applicant->farmer_screening_result, ['Selected', 'Reject'], true),
                                        ])>
                                            {{ $applicant->farmer_screening_result ?: 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('farmer-organizations.edit', $applicant) }}" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No farmer group records yet. Upload your Kobo Excel export to start.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $applicants->links() }}
                </div>
            </div>
        </div>
    </section>

    @include('business-information.partials.admin-division-script')
</x-app-layout>
