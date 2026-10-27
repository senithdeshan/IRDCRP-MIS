<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">IRDCRP · Separate Program Module</p>
            <h1 class="text-2xl font-semibold text-slate-950">CERC</h1>
            <p class="mt-1 text-sm text-slate-500">Choose a tank category to manage its Sections A–D records.</p>
        </div>
    </x-slot>
    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-8">
            <div>
                <h2 class="mb-4 text-lg font-semibold text-slate-950">CERC Overview</h2>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['Total Schemes', $schemeCount, 'Tank and scheme records'],
                        ['CAC Members', $cacMemberCount, 'Cascade Action Committee members'],
                        ['Farmer Organizations', $organizationCount, 'Organizations linked to tanks'],
                        ['FO Members', $farmerMemberCount, 'Farmer Organization members'],
                    ] as [$title, $count, $description])
                        <article class="cerc-stat-card">
                            <p class="cerc-stat-label">{{ $title }}</p>
                            <p class="cerc-stat-value">{{ number_format($count) }}</p>
                            <p class="cerc-stat-description">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-lg font-semibold text-slate-950">Tank Categories</h2>
                <div class="grid gap-5 lg:grid-cols-2">
            @foreach ([['minor', 'Minor Tank'], ['major', 'Major Tanks']] as [$type, $label])
                <div class="cerc-category-card">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">CERC</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $label }}</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">{{ number_format($type === 'minor' ? $minorSchemeCount : $majorSchemeCount) }} schemes registered</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Enter scheme details and CAC members (A and B), then Farmer Organization and member details (C and D).</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('cerc-assets.index', ['tank_type' => $type]) }}" class="tank-rehab-action">Scheme Details (A and B)</a>
                            <a href="{{ route('cerc-farmer-organizations.index', ['tank_type' => $type]) }}" class="tank-rehab-secondary">Farmer Organization & Members (C and D)</a>
                        </div>
                    </div>
                </div>
            @endforeach
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
