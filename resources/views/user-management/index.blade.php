<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Workspace / Access control</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">User Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage application access for staff registered in STAFF PROFILE IRDCRP.</p>
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
                                <th scope="col" class="min-w-52 px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($profiles as $profile)
                                @php
                                    $account = $profile->official_email ? $usersByEmail->get(mb_strtolower($profile->official_email)) : null;
                                @endphp
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm tabular-nums text-slate-500">{{ $profile->id }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-900">{{ $profile->name_with_initials }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $profile->official_email ?: 'No official email recorded' }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $profile->designation ?: 'Not provided' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                        @if ($account && ! $account->isProtectedSuperAdmin())
                                            <a href="{{ route('staff.permissions.edit', $account) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-700/20">Manage Permissions</a>
                                        @elseif ($account)
                                            <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">Protected account</span>
                                        @elseif ($profile->official_email && $canManageUsers)
                                            <a href="{{ route('staff.create', ['name' => $profile->name_with_initials, 'email' => $profile->official_email, 'phone' => $profile->contact_number, 'designation' => $profile->designation]) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-700/20">Create User Account</a>
                                        @elseif ($profile->official_email)
                                            <span class="text-xs font-medium text-slate-500">Admin access required to create account</span>
                                        @else
                                            <span class="text-xs font-medium text-slate-500">Add official email first</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
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
</x-app-layout>
