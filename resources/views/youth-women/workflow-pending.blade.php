<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">{{ $eyebrow }}</p>
        <h1 class="mt-1 text-2xl font-semibold text-slate-950">{{ $title }}</h1>
    </x-slot>
    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="panel-surface mx-auto max-w-7xl p-6">
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Coming soon</span>
            <p class="mt-4 text-sm text-slate-600">{{ $description }}</p>
            <a href="{{ route('business-information.index') }}" class="mt-5 inline-flex rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Open Individual Business Information</a>
        </div>
    </section>
</x-app-layout>
