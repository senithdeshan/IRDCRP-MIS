<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Command center</p>
                <h1 class="text-2xl font-semibold text-slate-950">Dashboard</h1>
            </div>
            <span class="inline-flex w-fit rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">System Online</span>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="grid gap-5 xl:grid-cols-[1.35fr_0.65fr]">
                <div class="hero-panel p-7">
                    <div class="max-w-2xl">
                        <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white">IRDCRP MIS</span>
                        <h2 class="mt-5 text-3xl font-semibold text-white">A cleaner workspace for field operations, records, and reporting.</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-200">Your staff, permissions, core modules, and reporting areas are now arranged in a focused management shell.</p>
                    </div>
                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ route('staff.index') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">Manage Staff</a>
                        <a href="{{ route('reports.index') }}" class="rounded-md border border-white/25 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">Reports</a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="metric-tile p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Staff</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $staffCount }}</p>
                    </div>
                    <div class="metric-tile p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Active Staff</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $activeStaffCount }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['title' => 'Component 1.2', 'route' => 'productive-partnership.index', 'value' => 'Main', 'tone' => 'border-amber-200 bg-amber-50 text-amber-800'],
                    ['title' => 'Farmer Organizations', 'route' => 'farmer-organizations.index', 'value' => 'First', 'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-800'],
                    ['title' => 'Component 1.3', 'route' => 'youth-women.index', 'value' => 'Main', 'tone' => 'border-violet-200 bg-violet-50 text-violet-800'],
                    ['title' => 'Business Information', 'route' => 'business-information.index', 'value' => 'First', 'tone' => 'border-sky-200 bg-sky-50 text-sky-800'],
                    ['title' => 'Component 2', 'route' => 'component-two.index', 'value' => 'Main', 'tone' => 'border-cyan-200 bg-cyan-50 text-cyan-800'],
                    ['title' => 'Cascade Registration', 'route' => 'cascade-registration.index', 'value' => 'Setup', 'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-800'],
                    ['title' => 'Tank Registration', 'route' => 'tank-registration.index', 'value' => 'Soon', 'tone' => 'border-blue-200 bg-blue-50 text-blue-800'],
                    ['title' => 'Reports', 'route' => 'reports.index', 'value' => 'Soon', 'tone' => 'border-teal-200 bg-teal-50 text-teal-800'],
                ] as $module)
                    <a href="{{ route($module['route']) }}" class="module-card block p-5">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="font-semibold text-slate-950">{{ $module['title'] }}</h3>
                            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $module['tone'] }}">{{ $module['value'] }}</span>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-500">Prepared as a dedicated module area for the next phase.</p>
                    </a>
                @endforeach
            </div>

            <div class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
                <div class="panel-surface p-6">
                    <h3 class="text-base font-semibold text-slate-950">Access Setup</h3>
                    <div class="mt-5 space-y-4">
                        @foreach (['Administrator', 'Manager', 'Officer', 'Viewer'] as $role)
                            <div class="flex items-center justify-between rounded-md border border-slate-200 px-4 py-3">
                                <span class="text-sm font-semibold text-slate-700">{{ $role }}</span>
                                <span class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Ready</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel-surface p-6">
                    <h3 class="text-base font-semibold text-slate-950">Build Queue</h3>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach (['Cascade registration', 'Tank registration', 'Farmer organization records', 'EOI selection tracking', 'Field visit workflow', 'Business information records'] as $task)
                            <div class="rounded-md border border-slate-200 p-4">
                                <p class="text-sm font-semibold text-slate-800">{{ $task }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-500">Waiting for module data fields.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
