@php
    $years = collect($periodSummary['years']);
    $calls = collect($periodSummary['calls']);
    $yearMax = max((int) $years->max('total'), 1);
    $callMax = max((int) $calls->max('total'), 1);
@endphp

<div class="period-summary-grid">
    <section class="period-summary-panel">
        <div class="period-summary-head">
            <div>
                <p class="period-summary-kicker">{{ $stageLabel }}</p>
                <h3 class="period-summary-title">Year Summary</h3>
            </div>
            <p class="period-summary-total">{{ number_format($years->sum('total')) }}</p>
        </div>

        <div class="period-summary-list">
            @forelse ($years as $year)
                <div class="period-summary-row">
                    <div class="period-summary-main">
                        <span class="period-summary-label">{{ $year->eoi_year }}</span>
                        <span class="period-summary-track">
                            <span class="period-summary-fill period-summary-fill-slate" style="--bar-width: {{ ((int) $year->total / $yearMax) * 100 }}%;"></span>
                        </span>
                    </div>
                    <span class="period-summary-count">{{ number_format($year->total) }}</span>
                </div>
            @empty
                <p class="period-summary-empty">No year data yet.</p>
            @endforelse
        </div>
    </section>

    <section class="period-summary-panel period-summary-panel-accent">
        <div class="period-summary-head">
            <div>
                <p class="period-summary-kicker">{{ $stageLabel }}</p>
                <h3 class="period-summary-title">Call Summary</h3>
            </div>
            <p class="period-summary-total">{{ number_format($calls->sum('total')) }}</p>
        </div>

        <div class="period-summary-list">
            @forelse ($calls as $call)
                <div class="period-summary-row">
                    <div class="period-summary-main">
                        <span class="period-summary-label">
                            {{ $call->eoi_year }} / {{ $call->eoi_call_number ? 'Call '.$call->eoi_call_number : 'No call' }}
                        </span>
                        <span class="period-summary-track">
                            <span class="period-summary-fill period-summary-fill-emerald" style="--bar-width: {{ ((int) $call->total / $callMax) * 100 }}%;"></span>
                        </span>
                    </div>
                    <span class="period-summary-count">{{ number_format($call->total) }}</span>
                </div>
            @empty
                <p class="period-summary-empty">No call data yet.</p>
            @endforelse
        </div>
    </section>
</div>
