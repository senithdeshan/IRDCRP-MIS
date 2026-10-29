<div class="grid gap-4 md:grid-cols-3">
    @foreach (['provinces' => 'Province-wise summary', 'districts' => 'District-wise summary', 'sectors' => 'Sector-wise summary'] as $key => $title)
        <section class="period-summary-panel">
            <div class="period-summary-head"><h3 class="period-summary-title">{{ $title }}{{ isset($summaryContext) ? ' of '.$summaryContext : '' }}</h3><p class="period-summary-total">{{ number_format(collect($geographicSummary[$key] ?? [])->sum('total')) }}</p></div>
            <div class="period-summary-list">
                @forelse (($geographicSummary[$key] ?? []) as $item)
                    <div class="period-summary-row"><span class="period-summary-label">{{ $item->name }}</span><span class="period-summary-count">{{ number_format($item->total) }}</span></div>
                @empty
                    <p class="period-summary-empty">No data for this selection.</p>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
