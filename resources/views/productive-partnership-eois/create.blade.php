<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('selected-eois.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Back to Received All EOI</a>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.2</p>
                <h1 class="text-2xl font-semibold text-slate-950">Add EOI</h1>
            </div>
        </div>
    </x-slot>

    <section class="px-4 py-5 sm:px-6 lg:px-8" aria-label="Template and bulk upload">
        <div class="mx-auto grid max-w-7xl gap-4 md:grid-cols-2">
            <div class="panel-surface border-t-2 border-t-emerald-600 p-4">
                <h2 class="text-base font-semibold text-slate-950">Download Template</h2>
                <p class="mt-1 text-xs text-slate-500">Use the Excel template to prepare multiple EOI records.</p>
                <a href="{{ route('selected-eois.template') }}" class="mt-3 inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100">Download Template</a>
            </div>

            <div class="panel-surface border-t-2 border-t-emerald-600 p-4">
                <h2 class="text-base font-semibold text-slate-950">Bulk Upload</h2>
                <p id="eoi-file-help" class="mt-1 text-xs text-slate-500">Excel (.xlsx), up to 15 MB. Matching EOI numbers will be updated.</p>
                <form method="POST" action="{{ route('selected-eois.import') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2">
                    @csrf
                    <label for="eoi_file" class="sr-only">Choose File</label>
                    <input id="eoi_file" type="file" name="eoi_file" required accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" aria-describedby="eoi-file-help eoi-file-error" @if ($errors->has('eoi_file')) aria-invalid="true" @endif class="block min-w-0 flex-1 rounded-md border border-slate-200 bg-white p-1 text-xs text-slate-600 file:mr-2 file:rounded file:border-0 file:bg-slate-100 file:px-2 file:py-2 file:text-xs file:font-semibold file:text-slate-700">
                    <x-input-error id="eoi-file-error" :messages="$errors->get('eoi_file')" />
                    <button type="submit" class="rounded-md bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">Upload</button>
                </form>
            </div>
        </div>
    </section>
    @include('productive-partnership-eois.partials.single-entry-form')
</x-app-layout>
