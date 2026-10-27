<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">People directory</p><h1 class="mt-1 text-2xl font-semibold text-slate-950">STAFF PROFILE IRDCRP</h1><p class="mt-1 text-sm text-slate-500">Staff registration records and profile details</p></div>
            @if($canManage)<a href="{{ route('staff-profiles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800"><span aria-hidden="true">＋</span> Register Staff</a>@endif
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8"><div class="mx-auto max-w-7xl space-y-5">
        @if(session('status'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>@endif
        <div class="grid gap-4 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Total staff members</p><p class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ number_format($summary['total']) }}</p><p class="mt-1 text-sm text-slate-500">Registered staff profiles</p></div><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-lg font-bold text-emerald-800" aria-hidden="true">S</span></div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Staff by gender</p>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Male</p><p class="mt-2 text-3xl font-bold text-blue-950">{{ number_format($summary['male']) }}</p><p class="mt-1 text-xs text-blue-700">members</p></div>
                    <div class="rounded-xl bg-fuchsia-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-fuchsia-700">Female</p><p class="mt-2 text-3xl font-bold text-fuchsia-950">{{ number_format($summary['female']) }}</p><p class="mt-1 text-xs text-fuchsia-700">members</p></div>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3"><p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Staff by type</p><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $summary['byType']->count() }} types</span></div>
                @if($summary['byType']->isNotEmpty())
                    <ul class="mt-3 divide-y divide-slate-100">
                        @foreach($summary['byType'] as $type)
                            <li class="flex items-center justify-between gap-3 py-2"><span class="truncate text-sm font-medium text-slate-700">{{ $type->staff_type }}</span><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800">{{ number_format($type->member_count) }}</span></li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 rounded-lg bg-slate-50 px-3 py-4 text-sm text-slate-500">No staff types recorded yet.</p>
                @endif
            </article>
        </div>
        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div><p class="text-sm font-semibold text-slate-900">{{ $profiles->total() }} staff {{ Str::plural('profile', $profiles->total()) }}</p><p class="mt-1 text-xs text-slate-500">Profiles are available to signed-in staff.</p></div>
            <form method="GET" action="{{ route('staff-profiles.index') }}" class="flex w-full gap-2 sm:max-w-md"><label for="staff-search" class="sr-only">Search staff profiles</label><input id="staff-search" name="search" value="{{ $search }}" placeholder="Search name, NIC, designation…" class="min-w-0 flex-1 rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10"><button class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Search</button></form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50"><tr>@foreach(['Staff member', 'Staff type', 'NIC number', 'Designation', 'Contact', 'Employment status', ''] as $heading)<th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($profiles as $profile)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4"><div class="flex items-center gap-3">@if($profile->photo_path)<img src="{{ route('staff-profiles.document', [$profile, 'photo']) }}" alt="" class="h-10 w-10 rounded-full object-cover ring-1 ring-slate-200">@else<div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-800">{{ Str::upper(Str::substr($profile->name_with_initials, 0, 1)) }}</div>@endif<div><p class="text-sm font-semibold text-slate-900">{{ $profile->name_with_initials }}</p><p class="text-xs text-slate-500">{{ $profile->official_email ?: 'No official email' }}</p></div></div></td>
                            <td class="whitespace-nowrap px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $profile->staff_type }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $profile->nic_number }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $profile->designation }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $profile->contact_number ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-800' => $profile->employment_status === 'In service', 'bg-rose-50 text-rose-700' => $profile->employment_status === 'Resigned'])>{{ $profile->employment_status }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('staff-profiles.show', $profile) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">View profile <span aria-hidden="true">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center"><div class="mx-auto max-w-sm"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-emerald-800">♙</div><h2 class="mt-4 text-sm font-semibold text-slate-900">No staff profiles found</h2><p class="mt-1 text-sm text-slate-500">
                            @if($search) Try another search term.
                            @elseif($canManage) Register the first staff profile to get started.
                            @else Staff profiles will appear here when they are registered.
                            @endif
                        </p>@if($canManage && !$search)<a href="{{ route('staff-profiles.create') }}" class="mt-4 inline-flex rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Register Staff</a>@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
            @if($profiles->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $profiles->links() }}</div>@endif
        </div>
    </div></section>
</x-app-layout>
