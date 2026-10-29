<x-app-layout>
    <x-slot name="header">
        <div class="eoi-create-heading">
            <div>
                <p class="eoi-create-eyebrow">Component 1.2</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Add EOI</h1>
                <p class="mt-1 text-sm text-slate-500">Enter one EOI manually, or use the template to upload multiple records.</p>
            </div>
            <a href="{{ route('selected-eois.index') }}" class="eoi-create-back">Back to Received All EOI</a>
        </div>
    </x-slot>

    <div class="eoi-create-page">
        <section class="eoi-create-tools" aria-label="Template and bulk upload">
            <div class="eoi-create-tools-grid">
            <div class="eoi-create-tool-card panel-surface">
                <span class="eoi-create-tool-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" /></svg></span>
                <div>
                <h2 class="text-base font-semibold text-slate-950">Download Template</h2>
                <p class="mt-1 text-xs text-slate-500">Use the Excel template to prepare multiple EOI records.</p>
                <a href="{{ route('selected-eois.template') }}" class="mt-3 inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100">Download Template</a>
                </div>
            </div>

            <div class="eoi-create-tool-card panel-surface">
                <span class="eoi-create-tool-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M5 16v4h14v-4" /></svg></span>
                <div>
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
        </div>
        </section>
        @include('productive-partnership-eois.partials.single-entry-form')
    </div>

    @php
        $eoiResult = session('eoi_result');
        $eoiSaveFailed = ! $eoiResult && $errors->any();
        $eoiSaveSucceeded = (bool) data_get($eoiResult, 'success', false);
    @endphp
    <x-modal name="eoi-save-result" :show="(bool) $eoiResult || $eoiSaveFailed" maxWidth="md">
        <div role="dialog" aria-modal="true" aria-labelledby="eoi-save-result-title" class="p-6 text-center sm:p-8">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full {{ $eoiSaveSucceeded ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                @if ($eoiSaveSucceeded)
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                @else
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.9 1.8 18.2A2 2 0 0 0 3.5 21h17a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z" /></svg>
                @endif
            </div>
            <h2 id="eoi-save-result-title" class="mt-4 text-xl font-semibold text-slate-950">
                {{ $eoiSaveSucceeded ? 'EOI saved' : 'EOI not added' }}
            </h2>
            @if ($eoiResult)
                <p role="{{ $eoiSaveSucceeded ? 'status' : 'alert' }}" class="mt-2 text-sm leading-6 text-slate-600">{{ data_get($eoiResult, 'message') }}</p>
            @else
                <p class="mt-2 text-sm leading-6 text-slate-600">Please correct the following details and try again:</p>
                <ul role="alert" class="mt-3 space-y-1 text-left text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" x-on:click="$dispatch('close-modal', 'eoi-save-result')" class="mt-6 inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                {{ $eoiSaveSucceeded ? 'Continue' : 'Review entry' }}
            </button>
        </div>
    </x-modal>
</x-app-layout>
