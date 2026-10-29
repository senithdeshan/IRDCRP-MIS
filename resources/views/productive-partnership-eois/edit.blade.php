<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p><h1 class="text-2xl font-semibold text-slate-950">Edit {{ $eoi->eoi_number }}</h1></div><a href="{{ request('from', route('selected-eois.show', $eoi)) }}" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Back</a></div>
    </x-slot>
    @include('productive-partnership-eois.partials.single-entry-form', ['formAction' => route('selected-eois.update', $eoi), 'formMethod' => 'PATCH', 'formTitle' => 'Edit EOI', 'submitLabel' => 'Save Changes', 'cancelUrl' => request('from', route('selected-eois.show', $eoi))])
</x-app-layout>
