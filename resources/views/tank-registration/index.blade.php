<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Tank Registration</h1>
                <p class="mt-1 text-sm text-slate-500">Excel-based tank progress and payment registry.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('tank-registration.template') }}" class="tank-rehab-secondary">Download Excel Template</a>
                <a href="{{ route('tank-registration.export') }}" class="tank-rehab-secondary">Export Excel</a>
                <a href="{{ route('tank-registration.create') }}" class="tank-rehab-action">Add Tank</a>
            </div>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5" x-data="{ locationMapOpen: false }">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-5">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tanks</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Families</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format((int) $summary['families']) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Avg Progress</p>
                    <p class="mt-3 text-3xl font-semibold text-cyan-700">{{ number_format((float) $summary['averageProgress'], 1) }}%</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Net Value</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-950">Rs. {{ number_format((float) $summary['netValue'], 2) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Grand Total</p>
                    <p class="mt-3 text-2xl font-semibold text-emerald-700">Rs. {{ number_format((float) $summary['grandTotal'], 2) }}</p>
                </div>
            </div>

            <div class="tank-location-strip">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Sri Lanka Location Map</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">View Location</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ number_format($locationSummary['located']) }} tank points ready for the map.</p>
                </div>

                <div class="tank-location-strip-stats">
                    <span><strong>{{ number_format($locationSummary['exact']) }}</strong> GPS</span>
                    <span><strong>{{ number_format($locationSummary['detected']) }}</strong> detected</span>
                    <span><strong>{{ number_format($locationSummary['project']) }}</strong> project</span>
                </div>

                <button
                    type="button"
                    class="tank-location-map-btn"
                    @click="locationMapOpen = true; $nextTick(() => document.dispatchEvent(new CustomEvent('tank-map-modal-opened')))"
                >
                    View Map
                </button>
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
                            'points' => $mapPoints,
                            'emptyMessage' => 'No tank locations could be detected yet.',
                        ])
                    </div>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[0.8fr_1.2fr]">
                <div class="tank-rehab-panel p-5">
                    <h2 class="text-base font-semibold text-slate-950">Excel Upload</h2>
                    <p class="mt-1 text-sm text-slate-500">Upload `.xlsx` files using the Tank Registration column format.</p>

                    <form method="POST" action="{{ route('tank-registration.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <input type="file" name="tank_file" accept=".xlsx" class="tank-rehab-file file:mr-4 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                        <x-input-error class="mt-2" :messages="$errors->get('tank_file')" />
                        <button type="submit" class="tank-rehab-action w-full">Import Excel</button>
                    </form>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Status</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($statusCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label ?: 'Unassigned' }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No records yet.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Payment</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($paymentCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label ?: 'Unassigned' }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No records yet.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="panel-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Top Provinces</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($provinceCounts as $label => $count)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $label }}</span>
                                    <span class="font-semibold text-slate-950">{{ $count }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No records yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('tank-registration.index') }}" class="tank-rehab-panel grid gap-3 p-4 lg:grid-cols-[1.4fr_0.8fr_0.8fr_0.8fr_0.8fr_auto]" x-data="adminDivisionPicker(@js($administrativeDivisions), @js(request('province')), @js(request('district')), '')">
                <input name="search" value="{{ request('search') }}" placeholder="Search tank id, tank, cascade, contractor, open ref" class="tank-rehab-input">
                <select name="province" x-model="province" @change="district = ''" class="tank-rehab-select">
                    <option value="">All provinces</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>
                    @endforeach
                </select>
                <select name="district" x-model="district" class="tank-rehab-select">
                    <option value="">All districts</option>
                    <template x-for="districtName in districts" :key="districtName">
                        <option :value="districtName" x-text="districtName"></option>
                    </template>
                </select>
                <select name="status" class="tank-rehab-select">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <select name="payment" class="tank-rehab-select">
                    <option value="">All payments</option>
                    @foreach ($payments as $payment)
                        <option value="{{ $payment }}" @selected(request('payment') === $payment)>{{ $payment }}</option>
                    @endforeach
                </select>
                <button class="tank-rehab-action">Search</button>
            </form>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-[92rem] divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tank</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Progress</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contract</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Financial</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($tanks as $tank)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $tank->tank_name }}</div>
                                        <div class="text-sm text-slate-500">{{ $tank->tank_id }}</div>
                                        <div class="mt-1 text-xs text-slate-400">{{ $tank->river_basin ?: 'N/A' }} / {{ $tank->cascade_name ?: 'N/A' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        {{ $tank->province ?: 'N/A' }} / {{ $tank->district ?: 'N/A' }}<br>
                                        <span class="text-slate-400">{{ $tank->ds_division ?: 'N/A' }} / {{ $tank->gn_division ?: 'N/A' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                                <span class="block h-full rounded-full bg-cyan-600" style="width: {{ min(100, max(0, (float) $tank->progress)) }}%"></span>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-800">{{ number_format((float) $tank->progress, 1) }}%</span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $tank->status ?: 'No Status' }}</span>
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $tank->payment ?: 'No Payment' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <span class="font-semibold text-slate-800">{{ $tank->contractor ?: 'N/A' }}</span><br>
                                        <span class="text-slate-400">{{ $tank->contractor_cida_grade ?: 'No CIDA grade' }} / {{ $tank->contractor_contact_number ?: 'No contact' }}</span><br>
                                        <span class="text-slate-400">{{ optional($tank->construction_start_date)->format('Y-m-d') ?: optional($tank->awarded_date)->format('Y-m-d') ?: 'No start date' }} / {{ $tank->construction_period_days ?: 0 }} days</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">
                                        <span class="font-semibold text-slate-800">Rs. {{ number_format((float) $tank->grand_total, 2) }}</span><br>
                                        <span class="text-slate-400">Net Rs. {{ number_format((float) $tank->net_value, 2) }} / IPC {{ $tank->recommended_ipc_no ?: 'N/A' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('tank-registration.show', $tank) }}" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">View</a>
                                            <a href="{{ route('tank-registration.show', $tank) }}#view-location" class="rounded-md border border-cyan-200 bg-cyan-50 px-3 py-2 text-sm font-semibold text-cyan-800 transition hover:bg-cyan-100">View Location</a>
                                            <a href="{{ route('tank-registration.edit', $tank) }}" class="tank-rehab-secondary px-3 py-2">Edit</a>
                                            <form method="POST" action="{{ route('tank-registration.destroy', $tank) }}" data-confirm-title="Delete tank record" data-confirm-message="This will permanently delete {{ $tank->tank_name }}." data-confirm-action="Delete" data-confirm-tone="rose">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No tank records yet. Add a tank or import the Excel template to start.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $tanks->links() }}
                </div>
            </div>
        </div>
    </section>

    @include('business-information.partials.admin-division-script')
</x-app-layout>
