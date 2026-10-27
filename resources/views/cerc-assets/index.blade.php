<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC · Sections A and B</p>
                <h1 class="text-2xl font-semibold text-slate-950">CERC · {{ $tankType === 'minor' ? 'Minor Tank' : 'Major Tanks' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Scheme details and Cascade Action Committee member records.</p>
            </div>
            <a href="{{ route('cerc-assets.create', ['tank_type' => $tankType]) }}" class="tank-rehab-action">Add scheme</a>
        </div>
    </x-slot>

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8" x-data="{ deleteOpen: false, deleteUrl: '', deleteName: '', openDelete(url, name) { this.deleteUrl = url; this.deleteName = name; this.deleteOpen = true; }, closeDelete() { this.deleteOpen = false; this.deleteUrl = ''; this.deleteName = ''; } }" @keydown.escape.window="closeDelete()">
        <div class="w-full space-y-5">
            @if (session('status'))
                <div class="irrigation-notice">{{ session('status') }}</div>
            @endif
            @if ($errors->has('excel_file'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first('excel_file') }}</div>
            @endif
            <div class="irrigation-form-card mx-auto w-full max-w-5xl p-5 sm:p-6">
                <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Excel bulk tools</h2>
                        <p class="mt-1 text-sm text-slate-600">Download the four-sheet template, export current {{ $tankType }} tank records, or upload a completed workbook.</p>
                        <p class="mt-2 text-xs text-slate-500">Use the A_Scheme, B_CAC, C_Organization and D_Members sheets. Keep the template column headers unchanged.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a class="tank-rehab-action" href="{{ route('cerc-assets.template', ['tank_type' => $tankType]) }}">Download template</a>
                        <a class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('cerc-assets.export', ['tank_type' => $tankType]) }}">Export current records</a>
                    </div>
                </div>
                <form class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-end" method="POST" action="{{ route('cerc-assets.import', ['tank_type' => $tankType]) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tank_type" value="{{ $tankType }}">
                    <div class="flex-1">
                        <label for="cerc-excel-file" class="mb-1 block text-sm font-medium text-slate-700">Completed Excel workbook (.xlsx)</label>
                        <input id="cerc-excel-file" name="excel_file" type="file" accept=".xlsx" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-emerald-800">
                    </div>
                    <button type="submit" class="tank-rehab-action">Upload workbook</button>
                </form>
            </div>
            <div class="irrigation-records-card w-full">
                <div class="w-full overflow-x-auto">
                    <table class="irrigation-records-table w-full min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr><th class="px-5 py-3">Tank / Scheme</th><th class="px-5 py-3">River Basin</th><th class="px-5 py-3">Location</th><th class="px-5 py-3">CAC Members</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($assets as $asset)
                                <tr>
                                    <td class="px-5 py-4"><div class="font-semibold text-slate-900">{{ $asset->tank_name }}</div><div class="text-xs text-slate-500">{{ $asset->tank_registration_number ?: 'No registration number' }}</div></td>
                                    <td class="px-5 py-4">{{ $asset->river_basin ?: '—' }}</td>
                                    <td class="px-5 py-4">{{ collect([$asset->district, $asset->province])->filter()->join(', ') ?: '—' }}</td>
                                    <td class="px-5 py-4">{{ $asset->cac_members_count }}</td>
                                    <td class="px-5 py-4">{{ $asset->tank_status ?: '—' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a class="font-semibold text-slate-700 hover:text-slate-950" href="{{ route('cerc-assets.show', [$asset, 'tank_type' => $tankType]) }}">View</a>
                                            <a class="font-semibold text-emerald-700 hover:text-emerald-900" href="{{ route('cerc-assets.edit', [$asset, 'tank_type' => $tankType]) }}">Edit</a>
                                            <button type="button" class="irrigation-delete-button" data-delete-url="{{ route('cerc-assets.destroy', [$asset, 'tank_type' => $tankType]) }}" data-delete-name="{{ $asset->tank_name }}" @click="openDelete($el.dataset.deleteUrl, $el.dataset.deleteName)">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No irrigation infrastructure records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $assets->links() }}</div>
            </div>
        </div>

        @if (session('import_summary'))
            @php($importSummary = session('import_summary'))
            <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" role="presentation" @click.self="open = false" @keydown.escape.window="open = false">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" role="alertdialog" aria-modal="true" aria-labelledby="cerc-import-success-title">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-700" aria-hidden="true">✓</div>
                    <h2 id="cerc-import-success-title" class="mt-4 text-center text-xl font-semibold text-slate-950">Successfully</h2>
                    <button type="button" class="tank-rehab-action mt-5 w-full justify-center" @click="open = false">Close</button>
                </div>
            </div>
        @endif

        <div x-show="deleteOpen" x-cloak class="irrigation-delete-modal" role="presentation" @click.self="closeDelete()">
            <div class="irrigation-delete-dialog" role="alertdialog" aria-modal="true" aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-description" @click.stop>
                <div class="irrigation-delete-dialog-icon" aria-hidden="true">!</div>
                <h2 id="delete-dialog-title">Delete this scheme?</h2>
                <p class="irrigation-delete-scheme" x-text="deleteName"></p>
                <p id="delete-dialog-description">This will also delete its CAC members, Farmer Organization and FO members. This action cannot be undone.</p>
                <div class="irrigation-delete-dialog-actions">
                    <button type="button" class="irrigation-delete-cancel" @click="closeDelete()">No, keep it</button>
                    <form method="POST" :action="deleteUrl">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="irrigation-delete-confirm">Yes, delete</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>



