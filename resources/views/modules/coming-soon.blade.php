<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">{{ $eyebrow }}</p>
            <h1 class="text-2xl font-semibold text-slate-950">{{ $title }}</h1>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="panel-surface mx-auto max-w-7xl p-8">
            <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-800">Coming soon</span>
                    <h2 class="mt-5 text-3xl font-semibold text-slate-950">{{ $title }} module</h2>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">{{ $description }}</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600">Role access ready</span>
                        <span class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600">Reports ready</span>
                        <span class="rounded-md border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600">Audit friendly</span>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach (['Data entry', 'Review queue', 'Approvals', 'Exports'] as $item)
                        <div class="module-preview p-5">
                            <div class="h-2 w-16 rounded-full bg-emerald-500"></div>
                            <h3 class="mt-5 text-base font-semibold text-slate-900">{{ $item }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Prepared for the next development phase.</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
