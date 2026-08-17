<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">New access</p>
            <h1 class="text-2xl font-semibold text-slate-950">Add Staff</h1>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <form method="POST" action="{{ route('staff.store') }}">
                @csrf
                @include('staff._form', ['mode' => 'create'])
            </form>
        </div>
    </section>
</x-app-layout>
