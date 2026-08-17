@php
    $locationFields = [
        'cascade_code' => 'Cascade Code',
        'province_code' => 'Province Code',
        'province' => 'Province',
        'district_code' => 'District Code',
        'district' => 'District',
        'dsd_code' => 'DSD Code',
        'dsd' => 'DSD',
        'gnd_code' => 'GND Code',
        'gnd_number' => 'GND Number',
        'gnd' => 'GND',
    ];

    $profileFields = [
        'cascade_code' => 'Cascade Code',
        'cascade_name' => 'Cascade Name',
        'river_basin_name' => 'River Basin Name',
        'number_of_tanks' => 'Number of Tanks',
        'number_of_anicuts' => 'Number of Anicuts',
        'area_of_cascade_ha' => 'Area of the Cascade (ha)',
        'command_area_under_scheme_ha' => 'Command Area Under the Scheme (ha)',
        'number_of_families_in_command_area' => 'Number of Families in Command Area',
        'asc' => 'ASC',
        'electorate' => 'Electorate',
    ];

    $tableFields = [...$locationFields, ...array_diff_key($profileFields, ['cascade_code' => true])];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Cascade Registration</h1>
                <p class="text-sm text-slate-500">Cascade location and profile registry.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">Registry Setup</span>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Location Fields</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">{{ count($locationFields) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cascade Fields</p>
                    <p class="mt-3 text-3xl font-semibold text-cyan-700">{{ count($profileFields) }}</p>
                </div>
                <div class="metric-tile p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</p>
                    <p class="mt-3 text-2xl font-semibold text-emerald-700">Ready</p>
                </div>
            </div>

            <div class="panel-surface p-5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Cascade Data Structure</h2>
                        <p class="mt-1 text-sm text-slate-500">Administrative location fields and cascade profile fields are organized below.</p>
                    </div>
                    <span class="w-fit rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">{{ count($tableFields) }} registry columns</span>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                            <h3 class="text-sm font-semibold text-slate-950">Location Registry</h3>
                        </div>
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Column Key</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($locationFields as $key => $label)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $label }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-500">{{ $key }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                            <h3 class="text-sm font-semibold text-slate-950">Cascade Profile</h3>
                        </div>
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Column Key</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($profileFields as $key => $label)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $label }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-500">{{ $key }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel-surface overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Cascade Registry Table</h2>
                        <p class="mt-1 text-sm text-slate-500">Cascade records can be reviewed across location and profile columns.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="search" placeholder="Search cascade records" class="filter-control sm:w-64">
                        <button type="button" class="filter-action">Add Cascade</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[96rem] divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach ($tableFields as $label)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                <td colspan="{{ count($tableFields) }}" class="px-5 py-12 text-center">
                                    <div class="mx-auto max-w-md">
                                        <p class="text-sm font-semibold text-slate-800">No cascade records available</p>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">Use the cascade registration fields above to maintain cascade location and profile records.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
