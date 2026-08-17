<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Access control</p>
                <h1 class="text-2xl font-semibold text-slate-950">Staff Management</h1>
            </div>
            <a href="{{ route('staff.create') }}" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                Add Staff
            </a>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $staff->total() }}</p>
                </div>
                @foreach ($roles as $key => $label)
                    @if ($loop->iteration <= 3)
                        <div class="metric-tile p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ $label }}</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">{{ \App\Models\User::where('role', $key)->count() }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="panel-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Staff</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Role</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Permissions</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($staff as $member)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $member->name }}</div>
                                        <div class="text-sm text-slate-500">{{ $member->email }}</div>
                                        @if ($member->designation)
                                            <div class="mt-1 text-xs font-medium text-slate-400">{{ $member->designation }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $roles[$member->role] ?? ucfirst($member->role ?? 'Staff') }}</td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'rounded-full px-3 py-1 text-xs font-semibold',
                                            'bg-emerald-100 text-emerald-800' => $member->status === 'active',
                                            'bg-rose-100 text-rose-800' => $member->status !== 'active',
                                        ])>
                                            {{ ucfirst($member->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ count($member->permissions ?? []) }} enabled</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('staff.edit', $member) }}" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No staff records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $staff->links() }}
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
