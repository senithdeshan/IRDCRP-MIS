<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $eoi->exists ? 'Edit Farmer Group' : 'Add Farmer Group' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Organization, contact, business proposal and initial screening information.</p>
            </div>
            <a href="{{ route('farmer-organizations.records') }}" class="text-sm font-semibold text-emerald-700">Back to Farmer Groups</a>
        </div>
    </x-slot>
    <div class="pt-6">
        @include('productive-partnership-eois.partials.single-entry-form', [
            'farmerWorkflow' => true,
            'formAction' => $eoi->exists ? route('farmer-organizations.update', $eoi) : route('farmer-organizations.store'),
            'formMethod' => $eoi->exists ? 'PATCH' : 'POST',
            'formTitle' => 'Farmer Organization Information',
            'cancelUrl' => route('farmer-organizations.records'),
            'submitLabel' => $eoi->exists ? 'Update Farmer Group' : 'Create Farmer Group',
        ])
    </div>
</x-app-layout>
