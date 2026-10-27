<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">One-time information · Irrigation Infrastructure</p>
                <h1 class="text-2xl font-semibold text-slate-950">Irrigation Infrastructure Assets Rehabilitated / Modernized</h1>
                <p class="mt-1 text-sm text-slate-500">Scheme details and Cascade Action Committee member records.</p>
            </div>
            <a href="{{ route('irrigation-assets.create') }}" class="tank-rehab-action">Add scheme</a>
        </div>
    </x-slot>

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8" x-data="{ deleteOpen: false, deleteUrl: '', deleteName: '', openDelete(url, name) { this.deleteUrl = url; this.deleteName = name; this.deleteOpen = true; }, closeDelete() { this.deleteOpen = false; this.deleteUrl = ''; this.deleteName = ''; } }" @keydown.escape.window="closeDelete()">
        <div class="w-full space-y-5">
            @if (session('status'))
                <div class="irrigation-notice">{{ session('status') }}</div>
            @endif
            @if ($errors->has('excel_file'))
                <div class="irrigation-validation-summary" role="alert"><strong>Excel upload needs attention.</strong><ul>@foreach ($errors->get('excel_file') as $message)<li>{{ $message }}</li>@endforeach</ul></div>
            @endif
            <div class="irrigation-form-card mx-auto w-full max-w-7xl">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Excel data entry</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Download the template or upload a completed workbook</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">Use the four sheets A_Scheme, B_CAC, C_Organization and D_Members. Enter Tank Registration Number in each sheet to link related records.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('irrigation-assets.template') }}" class="tank-rehab-secondary">Download Excel Template</a>
                            <a href="{{ route('irrigation-assets.export') }}" class="tank-rehab-secondary">Download Current Records</a>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('irrigation-assets.import') }}" enctype="multipart/form-data" class="irrigation-excel-upload w-full xl:max-w-xl">
                        @csrf
                        <label for="excel_file" class="block text-sm font-semibold text-slate-700">Upload completed .xlsx workbook</label>
                        <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                            <input id="excel_file" name="excel_file" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required class="irrigation-excel-file min-w-0 flex-1">
                            <button type="submit" class="tank-rehab-action">Upload workbook</button>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Maximum file size: 20 MB. Uploading the same registration numbers updates their records.</p>
                    </form>
                </div>
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
                                            <a class="font-semibold text-slate-700 hover:text-slate-950" href="{{ route('irrigation-assets.show', $asset) }}">View</a>
                                            <a class="font-semibold text-emerald-700 hover:text-emerald-900" href="{{ route('irrigation-assets.edit', $asset) }}">Edit</a>
                                            <button type="button" class="irrigation-delete-button" data-delete-url="{{ route('irrigation-assets.destroy', $asset) }}" data-delete-name="{{ $asset->tank_name }}" @click="openDelete($el.dataset.deleteUrl, $el.dataset.deleteName)">Delete</button>
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
