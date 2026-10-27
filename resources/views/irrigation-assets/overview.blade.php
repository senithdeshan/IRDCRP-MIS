<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">IRDCRP · Infrastructure Management</p>
            <h1 class="text-2xl font-semibold text-slate-950">Irrigation Infrastructure Assets Rehabilitated / Modernized</h1>
            <p class="mt-1 text-sm text-slate-500">Scheme details and Cascade Action Committee member records.</p>
        </div>
    </x-slot>

    <section class="irrigation-module px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            @if (session('status'))<div class="irrigation-notice">{{ session('status') }}</div>@endif
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Schemes</p><p class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($schemeCount) }}</p><p class="mt-1 text-xs text-slate-500">Section A records</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">CAC Members</p><p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($cacMemberCount) }}</p><p class="mt-1 text-xs text-slate-500">Section B records</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Farmer Organizations</p><p class="mt-3 text-3xl font-semibold text-teal-700">{{ number_format($organizationCount) }}</p><p class="mt-1 text-xs text-slate-500">Section C records</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">FO Members</p><p class="mt-3 text-3xl font-semibold text-cyan-700">{{ number_format($farmerMemberCount) }}</p><p class="mt-1 text-xs text-slate-500">Section D records</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Functional</p><p class="mt-3 text-3xl font-semibold text-emerald-700">{{ number_format($functionalSchemeCount) }}</p><p class="mt-1 text-xs text-slate-500">Schemes in functional condition</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Partially functional</p><p class="mt-3 text-3xl font-semibold text-amber-600">{{ number_format($partiallyFunctionalSchemeCount) }}</p><p class="mt-1 text-xs text-slate-500">Schemes needing further work</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Non-functional</p><p class="mt-3 text-3xl font-semibold text-rose-700">{{ number_format($nonFunctionalSchemeCount) }}</p><p class="mt-1 text-xs text-slate-500">Schemes in non-functional condition</p></div>
                <div class="metric-tile p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Abandoned</p><p class="mt-3 text-3xl font-semibold text-slate-600">{{ number_format($abandonedSchemeCount) }}</p><p class="mt-1 text-xs text-slate-500">Abandoned scheme records</p></div>
            </div>

            <div>
                <div class="mb-4"><h2 class="text-lg font-semibold text-slate-950">Module sections</h2><p class="mt-1 text-sm text-slate-500">Choose a section to view or enter its records.</p></div>
                <div class="grid gap-5 lg:grid-cols-2">
                    <a href="{{ route('irrigation-assets.index') }}" class="irrigation-module-card group">
                        <div class="irrigation-module-card-icon bg-emerald-100 text-emerald-800">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19V9l9-5 9 5v10M3 10l9 5 9-5M12 15v5M7 12v2m10-2v2M6 20h12" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">Sections A and B</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-950">Scheme Details & CAC</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Manage irrigation scheme information and Cascade Action Committee members.</p>
                            <div class="mt-4 flex flex-wrap gap-2"><span class="irrigation-module-card-tag">{{ number_format($schemeCount) }} schemes</span><span class="irrigation-module-card-tag">{{ number_format($cacMemberCount) }} CAC members</span></div>
                        </div>
                        <span class="irrigation-module-card-arrow" aria-hidden="true">→</span>
                    </a>

                    <a href="{{ route('irrigation-farmer-organizations.index') }}" class="irrigation-module-card group">
                        <div class="irrigation-module-card-icon bg-teal-100 text-teal-800">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m16 0v-1.5a4.5 4.5 0 0 0-3.5-4.38M10 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm9-5a3.5 3.5 0 0 1-1 6.83" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal-700">Sections C and D</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-950">Farmer Organization & Members</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Manage each scheme’s Farmer Organization profile and its member details.</p>
                            <div class="mt-4 flex flex-wrap gap-2"><span class="irrigation-module-card-tag">{{ number_format($organizationCount) }} organizations</span><span class="irrigation-module-card-tag">{{ number_format($farmerMemberCount) }} members</span></div>
                        </div>
                        <span class="irrigation-module-card-arrow" aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
