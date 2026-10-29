<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 text-left">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Agreement Signed FPO</h1>
            <p class="text-sm text-slate-500">Financial information for FPOs whose proposal has completed review.</p>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))
                <div role="status" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Agreement Signed FPO</p><p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p></div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Investment (Rs.)</p>
                    <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($summary['financials']['investment'], 2) }}</p>
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600"><span>Own: Rs. {{ number_format($summary['financials']['own'], 2) }}</span><span>Loan: Rs. {{ number_format($summary['financials']['loan'], 2) }}</span><span>Grant: Rs. {{ number_format($summary['financials']['grant'], 2) }}</span></div>
                </div>
            </div>

            @include('productive-partnership-eois.partials.geographic-summary', ['summaryContext' => 'agreement signed FPOs'])

            <form method="GET" action="{{ route('agreement-sign-fop.index') }}" class="filter-panel grid-cols-2 sm:grid-cols-3 xl:grid-cols-4" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
                <input name="search" value="{{ request('search') }}" placeholder="Search EOI, organization, proposal, contact" class="filter-control col-span-2 sm:col-span-3 xl:col-span-2">
                <select name="eoi_year" class="filter-control"><option value="">All years</option>@foreach ($eoiYears as $year)<option value="{{ $year }}" @selected((string) request('eoi_year') === (string) $year)>{{ $year }}</option>@endforeach</select>
                <select name="eoi_call_number" class="filter-control"><option value="">All calls</option>@foreach ($eoiCallNumbers as $call)<option value="{{ $call }}" @selected((string) request('eoi_call_number') === (string) $call)>Call {{ $call }}</option>@endforeach</select>
                <select name="province" x-model="province" @change="district = ''" class="filter-control"><option value="">All provinces</option>@foreach ($provinces as $province)<option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>@endforeach</select>
                <select name="district" x-model="district" class="filter-control"><option value="">All districts</option><template x-for="districtName in districts" :key="districtName"><option :value="districtName" x-text="districtName"></option></template></select>
                <select name="sector" class="filter-control"><option value="">All sectors</option>@foreach (\App\Http\Controllers\YouthWomenApplicantController::BUSINESS_SECTORS as $sector)<option value="{{ $sector }}" @selected(request('sector') === $sector)>{{ $sector }}</option>@endforeach</select>
                <select name="payment_status" class="filter-control"><option value="">All payment statuses</option><option value="awaiting_tranche_1" @selected(request('payment_status') === 'awaiting_tranche_1')>Awaiting Tranche 1</option><option value="tranche_1_completed" @selected(request('payment_status') === 'tranche_1_completed')>Tranche 1 Completed</option><option value="tranche_2_completed" @selected(request('payment_status') === 'tranche_2_completed')>Tranche 2 Completed</option><option value="tranche_3_completed" @selected(request('payment_status') === 'tranche_3_completed')>Tranche 3 Completed</option></select>
                <div class="col-span-2 flex items-center gap-2 sm:col-span-3 xl:col-span-4"><button class="filter-action">Search</button><a href="{{ route('agreement-sign-fop.index') }}" class="filter-action-secondary">Reset</a></div>
            </form>

            <div class="panel-surface overflow-hidden"><div class="overflow-x-auto">
                <table class="agreement-signed-fpo-table min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr>
                        @foreach (['eoi_number' => 'EOI Number', 'organization_name' => 'Organization Name', 'province' => 'Province / District', 'sector' => 'Sector', 'agreement_signed_date' => 'Agreement Signed Date'] as $column => $label)
                            @php($nextDirection = request('sort') === $column && request('direction') !== 'desc' ? 'desc' : 'asc')
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500"><a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection, 'page' => 1]) }}" class="hover:text-slate-900">{{ $label }} <span aria-hidden="true">{{ request('sort') === $column ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a></th>
                        @endforeach
                        @php($paymentDirection = request('sort') === 'payment_status' && request('direction') !== 'desc' ? 'desc' : 'asc')
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500"><a href="{{ request()->fullUrlWithQuery(['sort' => 'payment_status', 'direction' => $paymentDirection, 'page' => 1]) }}">Payment Status <span aria-hidden="true">{{ request('sort') === 'payment_status' ? (request('direction') === 'desc' ? '↓' : '↑') : '↕' }}</span></a></th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($eois as $eoi)
                        @php($data = $eoi->agreement_eoi_data ?? [])
                        @php($tr1 = filled(data_get($data, 'tr1.date')))
                        @php($tr2 = $tr1 && filled(data_get($data, 'tr2.date')))
                        @php($tr3 = $tr2 && filled(data_get($data, 'tr3.date')))
                        @php($paymentStatus = $tr3 ? 'Tranche 3 Completed' : ($tr2 ? 'Tranche 2 Completed' : ($tr1 ? 'Tranche 1 Completed' : 'Awaiting Tranche 1')))
                        <tr id="eoi-row-{{ $eoi->id }}">
                            <td class="px-5 py-4"><span class="font-semibold text-slate-950">{{ $eoi->eoi_number }}</span></td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $eoi->organization_name ?: 'N/A' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ $eoi->province ?: 'N/A' }} / {{ $eoi->district ?: 'N/A' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ $eoi->sector ?: 'N/A' }}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ data_get($eoi->fop_agreement_tracking, 'agreement_sign.date') ?: 'Pending' }}</td>
                            <td class="px-5 py-4"><span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $tr3, 'bg-cyan-100 text-cyan-800' => !$tr3 && $tr2, 'bg-blue-100 text-blue-800' => !$tr2 && $tr1, 'bg-amber-100 text-amber-800' => !$tr1])>{{ $paymentStatus }}</span></td>
                            <td class="px-5 py-4 text-right"><div class="flex flex-wrap justify-end gap-2">
                                <button type="button" x-data @click="$dispatch('open-modal', 'eoi-data-{{ $eoi->id }}')" class="rounded-md bg-emerald-700 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-800">Update Financial Data</button>
                                <a href="{{ route('selected-eois.show', [$eoi, 'from' => request()->fullUrl()]) }}" data-preserve-scroll-link aria-label="View EOI {{ $eoi->eoi_number }}" title="View details" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No FPOs match the selected filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div><div class="border-t border-slate-100 px-5 py-4">{{ $eois->links() }}</div></div>
            @foreach ($eois as $eoi) @include('productive-partnership-eois.partials.agreement-eoi-data', ['returnUrl' => request()->fullUrl().'#eoi-row-'.$eoi->id]) @endforeach
        </div>
    </section>
    @include('business-information.partials.admin-division-script')
</x-app-layout>
