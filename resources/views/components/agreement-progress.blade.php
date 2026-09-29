@props(['stage', 'data' => [], 'live' => false])
@php
    $tranches = ['tr1', 'tr2', 'tr3'];
    $sum = fn ($key) => collect(['own', 'loan', 'grant'])->sum(fn ($source) => (int) round((float) data_get($data, "$key.$source", 0) * 100));
    $budget = $sum('investment');
    $isTranche = in_array($stage, $tranches, true);
    $amount = $sum($stage);
    $cumulative = $isTranche ? collect(array_slice($tranches, 0, array_search($stage, $tranches) + 1))->sum($sum) : $amount;
    $percent = $budget > 0 ? $amount / $budget * 100 : null;
    $cumulativePercent = $budget > 0 ? $cumulative / $budget * 100 : null;
@endphp
<div class="mt-3 space-y-2 rounded-lg border border-emerald-100 bg-white p-3 text-xs">
    <div class="flex items-center justify-between gap-3">
        <span class="text-slate-600">{{ $stage === 'investment' ? 'Total Investment baseline' : ($stage === 'revised' ? 'Revised / Total Investment' : strtoupper($stage).' / Total Investment') }}</span>
        <strong class="text-emerald-800" @if ($live) x-text="percentageLabel('{{ $stage }}')" @endif>{{ $percent === null ? 'Set Total Investment first' : number_format($percent, 2).'%' }}</strong>
    </div>
    @if ($isTranche)
        <div class="flex items-center justify-between gap-3">
            <span class="text-slate-600">Cumulative through {{ strtoupper($stage) }}</span>
            <strong class="text-emerald-800" @if ($live) x-text="percentageLabel('{{ $stage }}', true)" @endif>{{ $cumulativePercent === null ? 'Set Total Investment first' : number_format($cumulativePercent, 2).'%' }}</strong>
        </div>
    @endif
    <div class="h-2 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
        <div class="h-full rounded-full bg-emerald-600 transition-all" style="width: {{ min(100, max(0, $cumulativePercent ?? 0)) }}%" @if ($live) :style="{ width: barWidth('{{ $stage }}', {{ $isTranche ? 'true' : 'false' }}) }" @endif></div>
    </div>
    @if ($live)
        <p x-show="percentage('{{ $stage }}', {{ $isTranche ? 'true' : 'false' }}) > 100" x-cloak class="text-amber-700">Amount exceeds Total Investment.</p>
    @elseif ($cumulativePercent > 100)
        <p class="text-amber-700">Amount exceeds Total Investment.</p>
    @endif
</div>
