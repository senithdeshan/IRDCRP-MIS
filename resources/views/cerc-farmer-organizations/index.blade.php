<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">CERC · Sections C and D</p>
                <h1 class="text-2xl font-semibold text-slate-950">Farmer Organization & Members · {{ $tankType === 'minor' ? 'Minor Tank' : 'Major Tanks' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Farmer Organization details and the members linked to each scheme.</p>
            </div>
            <a href="{{ route('cerc-farmer-organizations.create', ['tank_type' => $tankType]) }}" class="tank-rehab-action">Add Farmer Organization</a>
        </div>
    </x-slot>

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8" x-data="{ deleteOpen: false, deleteUrl: '', deleteName: '', openDelete(url, name) { this.deleteUrl = url; this.deleteName = name; this.deleteOpen = true; }, closeDelete() { this.deleteOpen = false; this.deleteUrl = ''; this.deleteName = ''; } }" @keydown.escape.window="closeDelete()">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))<div class="irrigation-notice">{{ session('status') }}</div>@endif
            <div class="irrigation-records-card">
                <div class="overflow-x-auto">
                    <table class="irrigation-records-table min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Farmer Organization</th><th class="px-5 py-3">Related Scheme</th><th class="px-5 py-3">Registration Status</th><th class="px-5 py-3">Members</th><th class="px-5 py-3">District</th><th class="px-5 py-3"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($organizations as $organization)
                                <tr>
                                    <td class="px-5 py-4"><div class="font-semibold text-slate-900">{{ $organization->name }}</div><div class="text-xs text-slate-500">{{ $organization->registration_number ?: 'Registration number not provided' }}</div></td>
                                    <td class="px-5 py-4"><div class="font-medium text-slate-800">{{ $organization->asset?->tank_name ?: 'Scheme unavailable' }}</div><div class="text-xs text-slate-500">{{ $organization->asset?->tank_registration_number ?: '—' }}</div></td>
                                    <td class="px-5 py-4">{{ $organization->legal_registration_status }}</td>
                                    <td class="px-5 py-4">{{ $organization->members->count() }}</td>
                                    <td class="px-5 py-4">{{ $organization->asset?->district ?: '—' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a class="font-semibold text-slate-700 hover:text-slate-950" href="{{ route('cerc-farmer-organizations.show', [$organization, 'tank_type' => $tankType]) }}">View</a>
                                            <a class="font-semibold text-emerald-700 hover:text-emerald-900" href="{{ route('cerc-farmer-organizations.edit', [$organization, 'tank_type' => $tankType]) }}">Edit</a>
                                            <button type="button" class="irrigation-delete-button" data-delete-url="{{ route('cerc-farmer-organizations.destroy', [$organization, 'tank_type' => $tankType]) }}" data-delete-name="{{ $organization->name }}" @click="openDelete($el.dataset.deleteUrl, $el.dataset.deleteName)">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No Farmer Organizations have been recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $organizations->links() }}</div>
            </div>
        </div>

        <div x-show="deleteOpen" x-cloak class="irrigation-delete-modal" role="presentation" @click.self="closeDelete()">
            <div class="irrigation-delete-dialog" role="alertdialog" aria-modal="true" aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-description" @click.stop>
                <div class="irrigation-delete-dialog-icon" aria-hidden="true">!</div>
                <h2 id="delete-dialog-title">Delete this Farmer Organization?</h2>
                <p class="irrigation-delete-scheme" x-text="deleteName"></p>
                <p id="delete-dialog-description">This will also delete all linked Farmer Organization member records. This action cannot be undone.</p>
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




