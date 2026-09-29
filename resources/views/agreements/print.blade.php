<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $eoi->eoi_number }} — {{ $stage === 'all' ? 'Full Payment Report' : $labels[$stage].' Approval Sheet' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #17202a; background: #eef2f5; font: 12px/1.45 Arial, sans-serif; }
        .toolbar { padding: 14px; text-align: center; background: #fff; border-bottom: 1px solid #ccd5dc; }
        button { padding: 10px 20px; border: 0; border-radius: 5px; background: #12634d; color: white; cursor: pointer; }
        main { max-width: 210mm; margin: 18px auto; background: white; padding: 14mm; }
        header { border-bottom: 2px solid #12634d; padding-bottom: 10px; margin-bottom: 16px; }
        h1 { font-size: 20px; margin: 5px 0; } h2 { font-size: 15px; margin: 0 0 10px; }
        .muted { color: #53616c; font-size: 11px; } .reference { font-size: 14px; font-weight: bold; }
        dl { display: grid; grid-template-columns: 1fr 1fr; gap: 7px 18px; margin: 14px 0; }
        dl div { border-bottom: 1px solid #e1e6e9; padding-bottom: 5px; overflow-wrap: anywhere; }
        dt { font-size: 10px; color: #53616c; } dd { margin: 2px 0 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; table-layout: fixed; }
        th, td { border: 1px solid #bdc8ce; padding: 7px; overflow-wrap: anywhere; }
        th { background: #edf4f1; text-align: left; } .number { text-align: right; font-variant-numeric: tabular-nums; }
        .payment { margin-top: 22px; border-top: 2px solid #12634d; padding-top: 12px; break-inside: avoid; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 15px; }
        .signatures div { border: 1px solid #bdc8ce; padding: 9px; }
        .line { margin-top: 14px; border-bottom: 1px dotted #7a8790; min-height: 10px; }
        .transfer { margin-top: 12px; border: 1px solid #bdc8ce; padding: 10px; }
        footer { border-top: 1px solid #bdc8ce; padding-top: 9px; margin-top: 20px; font-size: 10px; }
        @page { size: A4; margin: 12mm; }
        @media print { body { background: white; } .toolbar { display: none; } main { margin: 0; padding: 0; max-width: none; } thead { display: table-header-group; } h2 { break-after: avoid; } }
        @if ($pdf ?? false)
        body { background: white; font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        main { max-width: none; padding: 0; margin: 0; }
        dl { display: block; }
        dl div { display: inline-block; width: 47%; vertical-align: top; margin-bottom: 6px; }
        .signatures { display: block; }
        .signatures div { display: inline-block; width: 29%; margin-right: 1%; vertical-align: top; }
        .payment { page-break-inside: avoid; }
        @endif
    </style>
</head>
<body>
    @unless ($pdf ?? false)
    <div class="toolbar"><button type="button" onclick="window.print()">Print</button> <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" style="display:inline-block;padding:10px 20px;background:#17202a;color:white;border-radius:5px;text-decoration:none">Download PDF</a><p class="muted">Saved data only. Complete the approval and transfer confirmation fields on the printed sheet.</p></div>
    @endunless
    <main>
        <header>
            <strong>IRDCRP · Management Information System</strong>
            <h1>{{ $stage === 'all' ? 'EOI Payment & Approval Report' : $labels[$stage].' — Payment & Approval Sheet' }}</h1>
            <p>{{ $component }}</p>
            <p class="reference">EOI: {{ $eoi->eoi_number }}</p>
            <span>Agreement sign date: {{ $data['agreement_sign_date'] ?? 'Not recorded' }}</span>
        </header>
        <h2>EOI / beneficiary details</h2>
        <dl>@foreach ($identity as $label => $value)<div><dt>{{ $label }}</dt><dd>{{ filled($value) ? $value : 'Not recorded' }}</dd></div>@endforeach</dl>
        <p><strong>Total Investment baseline: LKR {{ number_format($budget / 100, 2) }}</strong></p>
        @if ($stage === 'all')
            <h2>Investment & expenditure summary</h2>
            <table>
                <thead><tr><th>Stage</th><th>Recorded date</th><th class="number">Own</th><th class="number">Loan</th><th class="number">Grant</th><th class="number">Total (LKR)</th></tr></thead>
                <tbody>@foreach ($labels as $key => $label)<tr>
                    <td>{{ $label }}</td><td>{{ $key === 'investment' ? ($data['agreement_sign_date'] ?? 'Not recorded') : data_get($data, "$key.date", 'Not recorded') }}</td>
                    @foreach (['own', 'loan', 'grant'] as $source)<td class="number">{{ filled(data_get($data, "$key.$source")) ? number_format((float) data_get($data, "$key.$source"), 2) : '—' }}</td>@endforeach
                    <td class="number">{{ number_format($cents($key) / 100, 2) }}</td>
                </tr>@endforeach</tbody>
            </table>
            <p class="muted">TR1–TR3 cumulative expenditure: LKR {{ number_format(($cents('tr1') + $cents('tr2') + $cents('tr3')) / 100, 2) }}. Revised Investment is shown separately and is not added to tranche expenditure.</p>
        @endif
        @foreach ($sections as $key => $label)
            @php
                $recorded = collect(['own', 'loan', 'grant'])->contains(fn ($source) => filled(data_get($data, "$key.$source")));
                $date = $key === 'investment' ? ($data['agreement_sign_date'] ?? null) : data_get($data, "$key.date");
            @endphp
            <section class="payment">
                <h2>{{ $label }} — Approval details</h2>
                <p><strong>EOI: {{ $eoi->eoi_number }}</strong> · {{ reset($identity) }}</p>
                <p>Recorded {{ $key === 'investment' ? 'agreement' : 'expenditure' }} date: <strong>{{ $date ?: 'Not recorded' }}</strong></p>
                @if (! $recorded)<p><strong>No amounts recorded for this stage.</strong></p>@endif
                <table><thead><tr><th>Own (LKR)</th><th>Loan (LKR)</th><th>Grant (LKR)</th><th>Total (LKR)</th></tr></thead><tbody><tr>
                    @foreach (['own', 'loan', 'grant'] as $source)<td class="number">{{ filled(data_get($data, "$key.$source")) ? number_format((float) data_get($data, "$key.$source"), 2) : 'Not recorded' }}</td>@endforeach
                    <td class="number"><strong>{{ $recorded ? number_format($cents($key) / 100, 2) : 'Not recorded' }}</strong></td>
                </tr></tbody></table>
                <p>Percentage of Total Investment: {{ $budget > 0 && $recorded ? number_format($cents($key) / $budget * 100, 2).'%' : 'Not available' }}</p>
                <p class="muted">Approval decision and actual payment/transfer confirmation are to be completed below; the recorded amounts alone do not confirm payment.</p>
                <div class="signatures">
                    @foreach (['Prepared by', 'Checked / recommended by', 'Approved by'] as $role)
                        <div><strong>{{ $role }}</strong><p class="line">Name:</p><p class="line">Designation:</p><p class="line">Signature:</p><p class="line">Date:</p></div>
                    @endforeach
                </div>
                <div class="transfer">
                    <strong>Approval / transfer confirmation</strong>
                    <p>Decision: □ Approved &nbsp; □ Returned &nbsp; □ Rejected</p>
                    <p class="line">Approved transfer amount (LKR):</p>
                    <p class="line">Payment / bank transfer reference:</p>
                    <p class="line">Actual payment / transfer date:</p>
                    <p class="line">Remarks:</p>
                </div>
            </section>
        @endforeach
        @if ($stage === 'all')
            <section class="payment"><h2>Supporting documents</h2>
                <ul>
                    @if (isset($data['full_proposal']))<li>Full Proposal: {{ $data['full_proposal']['name'] }}</li>@endif
                    @foreach ($data['attachments'] ?? [] as $file)<li>{{ $file['name'] }}</li>@endforeach
                    @if (empty($data['full_proposal']) && empty($data['attachments']))<li>No supporting documents saved.</li>@endif
                </ul>
            </section>
        @endif
        <footer>EOI: {{ $eoi->eoi_number }} · Generated by {{ $printedBy }} · {{ $printedAt->format('Y-m-d H:i T') }} · IRDCRP MIS</footer>
    </main>
</body>
</html>
