<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Workspace / Access control</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">User Management</h1>
                <p class="mt-1 text-sm text-slate-500">Approve staff registration, then manage access after the staff member creates an account.</p>
            </div>
            <div class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm sm:self-auto">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-50 text-xs font-bold text-emerald-800">{{ $profiles->count() }}</span>
                Staff {{ \Illuminate\Support\Str::plural('member', $profiles->count()) }}
            </div>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))
                <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="w-20 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">ID</th>
                                <th scope="col" class="min-w-52 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                                <th scope="col" class="min-w-60 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                                <th scope="col" class="min-w-48 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Designation</th>
                                <th scope="col" class="min-w-44 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Registration</th>
                                <th scope="col" class="min-w-52 px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($profiles as $profile)
                                @php
                                    $account = $profile->user ?? ($profile->official_email ? $usersByEmail->get(mb_strtolower($profile->official_email)) : null);
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm tabular-nums text-slate-500">{{ $profile->id }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-900">{{ $profile->name_with_initials }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $profile->official_email ?: 'No official email recorded' }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $profile->designation ?: 'Not provided' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-800' => (bool) $account, 'bg-blue-50 text-blue-800' => !$account && $profile->registration_enabled && $profile->employment_status === 'In service', 'bg-slate-100 text-slate-600' => !$account && (!$profile->registration_enabled || $profile->employment_status !== 'In service')])>{{ $account ? 'Registered' : ($profile->registration_enabled && $profile->employment_status === 'In service' ? 'Registration enabled' : 'Not enabled') }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                        @if ($account && ! $account->isProtectedSuperAdmin() && $canManageUsers)
                                            <a href="{{ route('staff.edit', $account) }}" class="mr-2 inline-flex items-center justify-center rounded-lg border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Manage Account</a>
                                        @endif
                                        @if ($account && ! $account->isProtectedSuperAdmin() && $canManagePermissions)
                                            <a href="{{ route('staff.permissions.edit', $account) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-700/20">Manage Permissions</a>
                                        @elseif ($account && ! $account->isProtectedSuperAdmin())
                                            <button type="button" data-permission-denied aria-haspopup="dialog" aria-controls="permission-denied-modal" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-700/20">Manage Permissions</button>
                                        @elseif ($account)
                                            <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Protected account</span>
                                        @elseif (auth()->user()->isSuperAdmin())
                                            <a href="{{ route('staff-profiles.edit', $profile) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-700/20">{{ $profile->registration_enabled ? 'Manage Registration' : 'Approve Registration' }}</a>
                                        @else
                                            <span class="text-xs font-medium text-slate-500">{{ $profile->registration_enabled && $profile->employment_status === 'In service' ? 'Awaiting staff registration' : 'Super admin approval required' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-500" aria-hidden="true">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                                        </div>
                                        <h3 class="mt-4 text-sm font-semibold text-slate-900">No staff profiles yet</h3>
                                        <p class="mt-1 text-sm text-slate-500">Profiles added in STAFF PROFILE IRDCRP will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($profiles->isNotEmpty())
                    <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-3 text-xs text-slate-500 sm:px-6">Showing all {{ $profiles->count() }} staff {{ \Illuminate\Support\Str::plural('member', $profiles->count()) }}</div>
                @endif
            </div>
        </div>
    </section>

    <dialog id="permission-denied-modal" class="permission-alert" role="alertdialog" aria-modal="true" aria-labelledby="permission-denied-title" aria-describedby="permission-denied-description permission-denied-help">
        <button type="button" data-close-permission-modal class="permission-alert-close" aria-label="Close permission alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <div class="permission-alert-content">
            <div class="permission-alert-icon" aria-hidden="true">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 6.5v7M12 17.5h.01"/></svg>
            </div>
            <span class="permission-alert-badge">Super Admin only</span>
            <h2 id="permission-denied-title" class="permission-alert-title">Permission required</h2>
            <p id="permission-denied-description" class="permission-alert-description">Only Super Admin users can manage permissions.</p>
            <p id="permission-denied-help" class="permission-alert-help">Contact your Super Admin if you need a permission change.</p>
        </div>
        <div class="permission-alert-actions">
            <button type="button" data-close-permission-modal class="permission-alert-confirm" autofocus>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                Got it
            </button>
        </div>
    </dialog>

    <script>
        (() => {
            const dialog = document.getElementById('permission-denied-modal');
            if (!dialog) return;
            let trigger = null;

            document.querySelectorAll('[data-permission-denied]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (dialog.open) return;
                    trigger = button;
                    dialog.showModal();
                    document.body.classList.add('permission-alert-open');
                });
            });

            dialog.querySelectorAll('[data-close-permission-modal]').forEach((button) => {
                button.addEventListener('click', () => dialog.close());
            });

            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const bounds = dialog.getBoundingClientRect();
                if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) {
                    dialog.close();
                }
            });

            dialog.addEventListener('close', () => {
                document.body.classList.remove('permission-alert-open');
                trigger?.focus({ preventScroll: true });
                trigger = null;
            });
        })();
    </script>
</x-app-layout>
