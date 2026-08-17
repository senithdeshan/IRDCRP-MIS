@php
    $progress = min(100, max(0, (float) ($tank->progress ?? 0)));
    $locationPoint = $mapPoint ?? $tank->sriLankaMapPoint();
    $mapsUrl = $locationPoint['maps_url'] ?? null;
    $contractEnd = $tank->awarded_date && $tank->construction_period_days
        ? $tank->awarded_date->copy()->addDays((int) $tank->construction_period_days + ((int) $tank->extension_of_time_months * 30))
        : null;
    $amountRows = [
        'Base Cost' => $tank->base_cost,
        'Physical Contingencies' => $tank->physical_contingencies,
        'Price Contingencies' => $tank->price_contingencies,
        'Net Value' => $tank->net_value,
        'VAT' => $tank->vat,
        'Grand Total' => $tank->grand_total,
        'Cumulative Amount' => $tank->cumulative_amount,
        'Paid Advanced Amount' => $tank->paid_advanced_amount,
        'Recommended IPC Amount' => $tank->recommended_ipc_amount,
    ];
    $imageGroups = [
        'Pre Construction' => $tank->pre_construction_images ?? [],
        'During Construction' => $tank->during_construction_images ?? [],
        'Post Construction' => $tank->post_construction_images ?? [],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="print-hidden flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Tank View</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $tank->tank_name }} / {{ $tank->tank_id }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tank-registration.index') }}" class="tank-rehab-secondary">Back</a>
                <a href="{{ route('tank-registration.edit', $tank) }}" class="tank-rehab-secondary">Edit</a>
                <button type="button" onclick="window.print()" class="tank-rehab-action">Print Report</button>
            </div>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5 tank-print-report" x-data="{ locationMapOpen: false }">
            <div class="overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-2xl shadow-slate-200/70">
                <div class="relative overflow-hidden bg-slate-950 px-6 py-7 text-white">
                    <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(135deg, #16a34a 0%, #0f766e 45%, #0f172a 100%);"></div>
                    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-200">Tank Rehabilitation Registration</p>
                            <h2 class="mt-3 text-3xl font-bold tracking-normal">{{ $tank->tank_name }}</h2>
                            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                                <span class="rounded-full bg-white/10 px-3 py-1 font-semibold">{{ $tank->tank_id }}</span>
                                <span class="rounded-full bg-emerald-400/20 px-3 py-1 font-semibold text-emerald-100">{{ $tank->status ?: 'No Status' }}</span>
                                <span class="rounded-full bg-cyan-400/20 px-3 py-1 font-semibold text-cyan-100">{{ $tank->agency ?: 'No Agency' }}</span>
                            </div>
                        </div>
                        <div class="w-full max-w-sm rounded-lg border border-white/15 bg-white/10 p-4 backdrop-blur">
                            <div class="flex items-end justify-between">
                                <p class="text-sm font-semibold text-emerald-100">Physical Progress</p>
                                <p class="text-4xl font-bold">{{ number_format($progress, 1) }}%</p>
                            </div>
                            <div class="mt-4 h-4 overflow-hidden rounded-full bg-white/20">
                                <span class="block h-full rounded-full bg-gradient-to-r from-emerald-300 to-cyan-300" style="width: {{ $progress }}%"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-5 lg:grid-cols-[1.05fr_0.95fr]">
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Families</p>
                                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format((int) $tank->no_of_family) }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Grand Total</p>
                                <p class="mt-2 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $tank->grand_total, 2) }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Period</p>
                                <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format((int) $tank->construction_period_days) }}</p>
                                <p class="text-xs font-semibold text-slate-500">Days</p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-bold text-slate-950">Location Profile</h3>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ([
                                    'River Basin' => $tank->river_basin,
                                    'Cascade Name' => $tank->cascade_name,
                                    'Province' => $tank->province,
                                    'District' => $tank->district,
                                    'DS Division' => $tank->ds_division,
                                    'GN Division' => $tank->gn_division,
                                    'AS Centre' => $tank->as_centre,
                                    'Agency' => $tank->agency,
                                ] as $label => $value)
                                    <div class="rounded-md border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $label }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $value ?: 'N/A' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-bold text-slate-950">Contract Details</h3>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ([
                                    'Contractor' => $tank->contractor,
                                    'Contractor Address' => $tank->contractor_address,
                                    'Contractor Contact Number' => $tank->contractor_contact_number,
                                    'Contractor CIDA Grade' => $tank->contractor_cida_grade,
                                    'Construction Start Date' => optional($tank->construction_start_date)->format('Y-m-d'),
                                    'Payment' => $tank->payment,
                                    'Awarded Date' => optional($tank->awarded_date)->format('Y-m-d'),
                                    'Construction Period' => $tank->construction_period_days ? $tank->construction_period_days.' days' : null,
                                    'Extension of Time' => $tank->extension_of_time_months ? $tank->extension_of_time_months.' months' : null,
                                    'Expected Completion' => optional($contractEnd)->format('Y-m-d'),
                                    'Open Ref No' => $tank->open_ref_no,
                                    'Recommended IPC No' => $tank->recommended_ipc_no,
                                ] as $label => $value)
                                    <div class="rounded-md border border-slate-100 bg-slate-50 px-3 py-2">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $label }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $value ?: 'N/A' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div id="view-location" class="tank-location-card">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Location Map</p>
                                    <h3 class="mt-1 text-base font-bold text-slate-950">View Location Map</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $tank->tank_name }} location is ready on Sri Lanka map.</p>
                                </div>
                                <button
                                    type="button"
                                    class="tank-location-map-btn print-hidden"
                                    @click="locationMapOpen = true; $nextTick(() => document.dispatchEvent(new CustomEvent('tank-map-modal-opened')))"
                                >
                                    View Map
                                </button>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-lg border border-emerald-100 bg-white px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Detected Location</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $tank->district ?: 'N/A' }} / {{ $tank->province ?: 'N/A' }}</p>
                                </div>
                                <div class="rounded-lg border border-emerald-100 bg-white px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Map Source</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $locationPoint['source'] ?? 'Not detected' }}</p>
                                </div>
                                <div class="rounded-lg border border-emerald-100 bg-white px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Map Latitude</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ isset($locationPoint['latitude']) ? number_format((float) $locationPoint['latitude'], 6) : 'N/A' }}</p>
                                </div>
                                <div class="rounded-lg border border-emerald-100 bg-white px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Map Longitude</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ isset($locationPoint['longitude']) ? number_format((float) $locationPoint['longitude'], 6) : 'N/A' }}</p>
                                </div>
                                <div class="rounded-lg border border-emerald-100 bg-white px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Raw Latitude</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $tank->latitude ?? 'N/A' }}</p>
                                </div>
                                <div class="rounded-lg border border-emerald-100 bg-white px-4 py-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Raw Longitude</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $tank->longitude ?? 'N/A' }}</p>
                                </div>
                            </div>

                            @if ($mapsUrl)
                                <a href="{{ $mapsUrl }}" target="_blank" class="print-hidden mt-3 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900">Open Google Map</a>
                            @endif
                        </div>

                        <div
                            x-show="locationMapOpen"
                            x-cloak
                            class="tank-map-modal print-hidden"
                            @keydown.escape.window="locationMapOpen = false"
                        >
                            <div class="tank-map-modal-backdrop" @click="locationMapOpen = false"></div>
                            <div class="tank-map-modal-panel">
                                <div class="tank-map-modal-header">
                                    <h2>Tank Locations Map</h2>
                                    <button type="button" class="tank-map-modal-close" @click="locationMapOpen = false" aria-label="Close map">&times;</button>
                                </div>
                                <div class="tank-map-modal-body">
                                    @include('tank-registration._sri-lanka-map', [
                                        'points' => $mapPoints ?? [],
                                        'selectedTankId' => $tank->id,
                                        'emptyMessage' => 'No map point could be detected for this tank.',
                                    ])
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-bold text-slate-950">Financial Summary</h3>
                            <div class="mt-4 divide-y divide-slate-100">
                                @foreach ($amountRows as $label => $value)
                                    <div class="flex items-center justify-between gap-4 py-2.5">
                                        <span class="text-sm font-semibold text-slate-500">{{ $label }}</span>
                                        <span class="text-sm font-bold text-slate-900">Rs. {{ number_format((float) $value, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-bold text-slate-950">Remarks</h3>
                            <p class="mt-3 min-h-24 rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-600">{{ $tank->remarks ?: 'No remarks available.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-white p-5">
                    <h3 class="text-base font-bold text-slate-950">Construction Images</h3>
                    <div class="mt-4 grid gap-4 lg:grid-cols-3">
                        @foreach ($imageGroups as $label => $images)
                            <div class="rounded-xl border border-emerald-100 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold text-slate-900">{{ $label }}</h4>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-emerald-700">{{ count($images) }}</span>
                                </div>

                                @if ($images)
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                        @foreach ($images as $image)
                                            <a href="{{ asset('storage/'.$image) }}" target="_blank" class="group block overflow-hidden rounded-lg border border-slate-200 bg-white">
                                                <img src="{{ asset('storage/'.$image) }}" alt="{{ $label }}" class="h-36 w-full object-cover transition group-hover:scale-105">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-3 flex min-h-36 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-center text-sm font-semibold text-slate-400">
                                        No images uploaded
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 text-center text-xs font-semibold text-slate-500">
                    Generated from IRDCRP MIS on {{ now()->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
