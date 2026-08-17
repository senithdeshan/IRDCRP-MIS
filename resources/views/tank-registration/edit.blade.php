<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 2</p>
            <h1 class="text-2xl font-semibold text-slate-950">Edit Tank Registration</h1>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <form method="POST" action="{{ route('tank-registration.update', $tank) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @include('tank-registration._form', ['mode' => 'edit'])
            </form>
        </div>
    </section>
</x-app-layout>
