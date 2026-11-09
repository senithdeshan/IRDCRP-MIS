<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $asset->tank_name }} - Scheme details</title>
    <style>
        @page { margin: 28px 30px; }
        body { color: #172b25; font: 10px DejaVu Sans, sans-serif; }
        h1 { margin: 0 0 4px; color: #075b46; font-size: 20px; }
        h2 { margin: 17px 0 7px; padding-bottom: 5px; border-bottom: 1px solid #b8d9cc; color: #075b46; font-size: 13px; }
        .sub { color: #64748b; font-size: 9px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { width: 33.33%; padding: 6px 7px; border: 1px solid #dce8e3; vertical-align: top; }
        .label { display: block; margin-bottom: 3px; color: #64748b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .value { overflow-wrap: anywhere; font-size: 9px; }
        .photos { width: 100%; border-collapse: collapse; }
        .photos td { width: 50%; padding: 6px; text-align: center; vertical-align: top; }
        .photos img { max-width: 235px; max-height: 180px; }
        .photo-label { margin-bottom: 5px; font-weight: bold; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <h1>{{ $asset->tank_name }}</h1>
    <div class="sub">CERC · Minor Tank Scheme details · Printed {{ now()->format('Y-m-d H:i') }}</div>

    @php
        $sections = [
            'Location' => ['Institution' => $asset->institution, 'Programme Type' => $asset->programme_type, 'Contract Status' => $asset->contract_status, 'Province' => $asset->province, 'District' => $asset->district, 'DSD' => $asset->ds_division, 'ASC' => $asset->asc, 'Latitude' => $asset->latitude, 'Longitude' => $asset->longitude],
            'Scheme information' => ['Scheme Name' => $asset->tank_name, 'Category' => $asset->category, 'Year' => $asset->year, 'Size' => $asset->size, 'Beneficiary Families' => $asset->beneficiary_families, 'Command Area (Ac)' => $asset->command_area_ac, 'Lot' => $asset->lot, 'Cluster' => $asset->cluster],
            'Estimated costs' => ['Civil Cost (LKR)' => $asset->civil_cost, 'Preliminaries (LKR)' => $asset->preliminaries_cost, 'Direct Cost (LKR)' => $asset->direct_cost, 'VAT 18% (LKR)' => $asset->vat_18, 'Contingencies (LKR)' => $asset->contingencies_cost, 'Total Cost Estimate (LKR)' => $asset->total_cost_estimate],
            'Award and contract' => ['Awarded Amount - Civil Cost (LKR)' => $asset->awarded_civil_cost, 'Awarded Amount - Preliminaries (LKR)' => $asset->awarded_preliminaries_cost, 'Awarded Amount without VAT & Contingencies (LKR)' => $asset->awarded_amount_without_vat_contingencies, 'Advance / Imprest (LKR)' => $asset->advance_imprest, 'Provisional Sum (LKR)' => $asset->provisional_sum, 'Date of Award' => $asset->date_of_award?->format('Y-m-d'), 'Agreement Sign Date' => $asset->agreement_sign_date?->format('Y-m-d'), 'Contract Period (Days)' => $asset->contract_period_days, 'Completion Date' => $asset->completion_date?->format('Y-m-d'), 'Contract Number' => $asset->contract_number, 'Contractor Name' => $asset->contractor_name, 'Contractor Address' => $asset->contractor_address, 'Contractor Contact Number' => $asset->contractor_contact_number],
            'Environmental and social clearances' => ['EMP Clearance Received Date' => $asset->emp_clearance_received_date?->format('Y-m-d'), 'SMP Clearance Received Date' => $asset->smp_clearance_received_date?->format('Y-m-d'), 'Date of ESMP Submitted to Procurement Division' => $asset->esmp_submitted_to_procurement_date?->format('Y-m-d')],
            'Construction progress' => ['Progress' => filled($asset->progress) ? number_format((float) $asset->progress, 1).'%' : null],
        ];
        $photoLabels = ['before_work_photo' => 'Before work photo', 'current_photo_1' => 'Current photo 1', 'current_photo_2' => 'Current photo 2', 'contractor_board_photo' => 'Contractor information board photo'];
    @endphp

    @foreach ($sections as $heading => $fields)
        <h2>{{ $heading }}</h2>
        <table class="grid"><tbody>
            @foreach (array_chunk($fields, 3, true) as $row)
                <tr>@foreach ($row as $label => $value)<td><span class="label">{{ $label }}</span><span class="value">{{ filled($value) ? $value : '—' }}</span></td>@endforeach @for ($i = count($row); $i < 3; $i++)<td></td>@endfor</tr>
            @endforeach
        </tbody></table>
    @endforeach

    <h2>Site photos</h2>
    <table class="photos"><tbody>
        @foreach (array_chunk(array_keys($photoLabels), 2) as $row)
            <tr>@foreach ($row as $key)<td><div class="photo-label">{{ $photoLabels[$key] }}</div>@if ($photos[$key])<img src="{{ $photos[$key] }}">@else<span class="muted">No photo uploaded</span>@endif</td>@endforeach @if (count($row) === 1)<td></td>@endif</tr>
        @endforeach
    </tbody></table>
</body>
</html>
