<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Access profile</p>
            <h1 class="text-2xl font-semibold text-slate-950">Edit Staff</h1>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <form method="POST" action="{{ route('staff.update', $staffMember) }}">
                @csrf
                @method('PATCH')
                @include('staff._form', ['mode' => 'edit'])
            </form>
        </div>
    </section>
</x-app-layout>
