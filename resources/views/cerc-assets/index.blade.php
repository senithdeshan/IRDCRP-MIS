<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC Â· Minor Tank</p>
                <h1 class="text-2xl font-semibold text-slate-950">Scheme Details</h1>
                <p class="mt-1 text-sm text-slate-500">Review and manage registered Minor Tank schemes.</p>
            </div>
            <a href="{{ route('cerc-assets.create') }}" class="tank-rehab-action">Register scheme</a>
        </div>
    </x-slot>

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8" x-data="{ deleteOpen: false, deleteUrl: '', deleteName: '', openDelete(url, name) { this.deleteUrl = url; this.deleteName = name; this.deleteOpen = true; }, closeDelete() { this.deleteOpen = false; this.deleteUrl = ''; this.deleteName = ''; } }" @keydown.escape.window="closeDelete()">
        <div class="space-y-5">
            @if (session('status'))<div class="irrigation-notice">{{ session('status') }}</div>@endif
            @if ($errors->has('excel_file'))<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first('excel_file') }}</div>@endif

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([['Registered schemes', $summary['total'], 'All Minor Tank scheme records', 'emerald'], ['In progress', $summary['in_progress'], 'Progress reported below 100%', 'sky'], ['Completed', $summary['completed'], 'Progress at 100%', 'violet']] as [$title, $count, $caption, $tone])
                    <article class="cerc-summary-card cerc-summary-{{ $tone }}"><div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-slate-600">{{ $title }}</p><p class="mt-2 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($count) }}</p></div><span class="cerc-summary-dot" aria-hidden="true"></span></div><p class="mt-3 text-xs text-slate-500">{{ $caption }}</p></article>
                @endforeach
            </div>

            <section class="cerc-filter-card">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-2"><div><h2 class="font-semibold text-slate-950">Filter schemes</h2><p class="mt-0.5 text-xs text-slate-500">Narrow results by location, contract status, or a scheme keyword.</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ number_format($assets->total()) }} results</span></div>
                <form method="GET" action="{{ route('cerc-assets.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    <label class="cerc-filter-field lg:col-span-2"><span>Search</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Scheme, contract no., contractor"></label>
                    <label class="cerc-filter-field"><span>Province</span><select name="province"><option value="">All provinces</option>@foreach ($provinces as $province)<option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>@endforeach</select></label>
                    <label class="cerc-filter-field"><span>District</span><select name="district"><option value="">All districts</option>@foreach ($districts as $district)<option value="{{ $district }}" @selected(request('district') === $district)>{{ $district }}</option>@endforeach</select></label>
                    <label class="cerc-filter-field"><span>Contract status</span><select name="contract_status"><option value="">All statuses</option>@foreach ($contractStatuses as $status)<option value="{{ $status }}" @selected(request('contract_status') === $status)>{{ $status }}</option>@endforeach</select></label>
                    <div class="flex items-end gap-2"><button type="submit" class="tank-rehab-action flex-1 justify-center">Apply</button><a href="{{ route('cerc-assets.index') }}" class="cerc-filter-reset" aria-label="Clear filters" title="Clear filters">Clear</a></div>
                </form>
            </section>

            <section class="cerc-excel-card">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3"><span class="cerc-excel-icon" aria-hidden="true">XLS</span><div><h2 class="text-sm font-semibold text-slate-950">Excel bulk tools</h2><p class="text-xs text-slate-500">Use the template to import or export scheme records.</p></div></div>
                    <div class="flex flex-wrap gap-2"><a class="cerc-excel-link" href="{{ route('cerc-assets.template', ['tank_type' => 'minor']) }}">Download template</a><a class="cerc-excel-link" href="{{ route('cerc-assets.export', ['tank_type' => 'minor']) }}">Export records</a></div>
                </div>
                <form class="mt-3 flex flex-col gap-2 border-t border-slate-100 pt-3 sm:flex-row sm:items-center" method="POST" action="{{ route('cerc-assets.import', ['tank_type' => 'minor']) }}" enctype="multipart/form-data">
                    @csrf
                    <label for="cerc-excel-file" class="sr-only">Select completed Excel workbook</label><input id="cerc-excel-file" name="excel_file" type="file" accept=".xlsx" required class="cerc-excel-input">
                    <button type="submit" class="cerc-excel-upload">Upload workbook</button>
                </form>
            </section>

            @php($columns = [
                'institution' => 'Institution', 'programme_type' => 'Programme Type', 'contract_status' => 'Contract Status',
                'province' => 'Province', 'district' => 'District', 'ds_division' => 'DSD', 'asc' => 'ASC', 'tank_name' => 'Scheme Name', 'progress' => 'Progress',
                'civil_cost' => 'Civil Cost (LKR)', 'preliminaries_cost' => 'Preliminaries (LKR)', 'direct_cost' => 'Direct Cost (LKR)',
                'vat_18' => 'VAT 18% (LKR)', 'contingencies_cost' => 'Contingencies (LKR)', 'total_cost_estimate' => 'Total Cost Estimate (LKR)',
                'awarded_civil_cost' => 'Awarded Amount - Civil Cost (LKR)', 'awarded_preliminaries_cost' => 'Awarded Amount - Preliminaries (LKR)',
                'awarded_amount_without_vat_contingencies' => 'Awarded Amount without VAT & Contingencies (LKR)', 'advance_imprest' => 'Advance / Imprest (LKR)',
                'provisional_sum' => 'Provisional Sum (LKR)', 'category' => 'Category', 'year' => 'Year', 'size' => 'Size',
                'beneficiary_families' => 'Beneficiary Families', 'command_area_ac' => 'Command Area (Ac)', 'lot' => 'Lot', 'cluster' => 'Cluster',
                'date_of_award' => 'Date of Award', 'agreement_sign_date' => 'Agreement Sign Date', 'contract_period_days' => 'Contract Period (Days)',
                'completion_date' => 'Completion Date', 'contract_number' => 'Contract Number', 'contractor_name' => 'Contractor Name',
                'contractor_address' => 'Contractor Address', 'contractor_contact_number' => 'Contractor Contact Number',
                'emp_clearance_received_date' => 'EMP Clearance Received Date', 'smp_clearance_received_date' => 'SMP Clearance Received Date',
                'esmp_submitted_to_procurement_date' => 'Date of ESMP Submitted to Procurement Division',
            ])
            @php($currencyFields = ['civil_cost', 'preliminaries_cost', 'direct_cost', 'vat_18', 'contingencies_cost', 'total_cost_estimate', 'awarded_civil_cost', 'awarded_preliminaries_cost', 'awarded_amount_without_vat_contingencies', 'advance_imprest', 'provisional_sum'])
            @php($dateFields = ['date_of_award', 'agreement_sign_date', 'completion_date', 'emp_clearance_received_date', 'smp_clearance_received_date', 'esmp_submitted_to_procurement_date'])
            <div class="irrigation-records-card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-semibold text-slate-900">Registered schemes</h2><p class="mt-1 text-sm text-slate-500">Scroll horizontally to view every field.</p></div><form method="GET" action="{{ route('cerc-assets.index') }}" class="cerc-page-size-form">@foreach (request()->except(['per_page', 'page']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach<label for="cerc-page-size">Rows per page</label><select id="cerc-page-size" name="per_page" onchange="this.form.submit()"><option value="25" @selected($perPage === 25)>25</option><option value="50" @selected($perPage === 50)>50</option><option value="100" @selected($perPage === 100)>100</option></select></form></div>
                <div class="w-full overflow-x-auto"><table class="min-w-[3200px] divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr>@foreach ($columns as $field => $label)<th class="px-4 py-3 align-top {{ $field === 'contract_number' ? 'min-w-[150px] max-w-[150px] whitespace-normal break-words' : ($field === 'contractor_name' ? 'min-w-[180px] max-w-[180px] whitespace-normal break-words' : 'whitespace-nowrap') }}">{{ $label }}</th>@endforeach<th class="sticky right-0 bg-slate-50 px-4 py-3">Actions</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assets as $asset)
                            <tr>@foreach ($columns as $field => $label)<td class="px-4 py-3 align-top {{ $field === 'contract_number' ? 'min-w-[150px] max-w-[150px] whitespace-normal break-words' : ($field === 'contractor_name' ? 'min-w-[180px] max-w-[180px] whitespace-normal break-words' : 'max-w-72 whitespace-nowrap') }}">@if ($field === 'tank_name')<a class="font-semibold text-emerald-800 hover:underline" href="{{ route('cerc-assets.show', $asset) }}">{{ $asset->{$field} }}</a>@elseif ($field === 'progress' && filled($asset->{$field})){{ number_format((float) $asset->{$field}, 1) }}%@elseif (in_array($field, $currencyFields, true) && filled($asset->{$field}))LKR {{ number_format((float) $asset->{$field}, 2) }}@elseif (in_array($field, $dateFields, true) && $asset->{$field}){{ $asset->{$field}->format('Y-m-d') }}@else{{ filled($asset->{$field}) ? $asset->{$field} : '-' }}@endif</td>@endforeach
                                <td class="sticky right-0 whitespace-nowrap bg-white px-4 py-3"><div class="flex items-center gap-3"><a class="font-semibold text-slate-700 hover:text-slate-950" href="{{ route('cerc-assets.show', $asset) }}">View</a><a class="font-semibold text-emerald-700 hover:text-emerald-900" href="{{ route('cerc-assets.edit', $asset) }}">Update</a><button type="button" class="irrigation-delete-button" data-delete-url="{{ route('cerc-assets.destroy', $asset) }}" data-delete-name="{{ $asset->tank_name }}" @click="openDelete($el.dataset.deleteUrl, $el.dataset.deleteName)">Delete</button></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columns) + 1 }}" class="px-5 py-12 text-center text-slate-500">No Minor Tank schemes have been registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
                <div class="cerc-pagination-footer"><p>Showing <strong>{{ $assets->firstItem() ?? 0 }}</strong>–<strong>{{ $assets->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($assets->total()) }}</strong> schemes</p>{{ $assets->links() }}</div>
            </div>
        </div>

        @if (session('import_summary'))
            <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="presentation" @click.self="open = false" @keydown.escape.window="open = false">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" role="alertdialog" aria-modal="true"><h2 class="text-center text-xl font-semibold text-slate-950">Import complete</h2><button type="button" class="tank-rehab-action mt-5 w-full justify-center" @click="open = false">Close</button></div>
            </div>
        @endif

        <div x-show="deleteOpen" x-cloak class="irrigation-delete-modal" role="presentation" @click.self="closeDelete()">
            <div class="irrigation-delete-dialog" role="alertdialog" aria-modal="true" aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-description" @click.stop>
                <div class="irrigation-delete-dialog-icon" aria-hidden="true">!</div><h2 id="delete-dialog-title">Delete this scheme?</h2><p class="irrigation-delete-scheme" x-text="deleteName"></p><p id="delete-dialog-description">This permanently deletes the scheme and cannot be undone.</p>
                <div class="irrigation-delete-dialog-actions"><button type="button" class="irrigation-delete-cancel" @click="closeDelete()">Cancel</button><form method="POST" :action="deleteUrl">@csrf @method('DELETE')<button type="submit" class="irrigation-delete-confirm">Delete scheme</button></form></div>
            </div>
        </div>
    </section>
</x-app-layout>
