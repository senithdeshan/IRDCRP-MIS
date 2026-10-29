@php
    $saved = $eoi->agreement_eoi_data ?? [];
    $failed = $errors->agreementData->any() && (string) old('agreement_eoi_id') === (string) $eoi->id;
    $value = fn ($key, $default = '') => $failed ? old($key, data_get($saved, $key, $default)) : data_get($saved, $key, $default);
    $progressAmounts = [];
    foreach (['investment', 'tr1', 'tr2', 'tr3', 'revised'] as $stageKey) {
        foreach (['own', 'loan', 'grant'] as $sourceKey) {
            $progressAmounts[$stageKey][$sourceKey] = $value($stageKey.'.'.$sourceKey);
        }
    }
@endphp
<x-modal name="eoi-data-{{ $eoi->id }}" :show="$failed" focusable>
    <form method="POST" action="{{ route('agreement-sign-fop.data', $eoi) }}" role="dialog" aria-modal="true" aria-labelledby="eoi-data-title-{{ $eoi->id }}" class="text-left" x-data="agreementProgress(@js($progressAmounts))" @submit="saving = true" data-preserve-scroll>
        @csrf
        @method('PATCH')
        <input type="hidden" name="agreement_eoi_id" value="{{ $eoi->id }}">
        <div class="flex items-start justify-between gap-4 bg-emerald-900 px-6 py-5 text-white">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-200">Component 1.2 · Agreement Sign FOP</p>
                <h2 id="eoi-data-title-{{ $eoi->id }}" class="mt-2 text-xl font-semibold">Update Financial Data</h2>
                <p class="mt-1 text-sm text-emerald-100">{{ $eoi->eoi_number }} · {{ $eoi->organization_name ?: 'Farmer organization' }}</p>
            </div>
            <button type="button" @click="$dispatch('close')" aria-label="Close EOI data popup" class="rounded-md px-3 py-1 text-2xl hover:bg-emerald-800">&times;</button>
        </div>
        <div class="max-h-[65vh] space-y-5 overflow-y-auto bg-slate-50 p-4 sm:p-6">
            @if ($failed)
                <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Please correct the financial fields below.</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->agreementData->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="rounded-lg border border-cyan-200 bg-cyan-50 p-4 text-sm text-cyan-900">Financial progress is calculated from recorded tranche dates and amounts. Agreement signing date is maintained in the proposal tracking stage.</div>
            @foreach (['investment' => 'Total Investment', 'tr1' => 'TR1', 'tr2' => 'TR2', 'tr3' => 'TR3', 'revised' => 'Revised Investment'] as $key => $title)
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800">{{ $key === 'investment' ? 'Agreement budget' : 'Expenditure' }}</span>
                    </div>
                    @if ($key !== 'investment')
                        <label class="mt-4 block text-sm font-medium text-slate-700">Date
                            <input type="date" name="{{ $key }}[date]" value="{{ $value($key.'.date') }}" max="{{ now()->toDateString() }}" class="mt-2 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <x-input-error class="mt-1" :messages="$errors->agreementData->get($key.'.date')" />
                        </label>
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach (['own' => 'Own', 'loan' => 'Loan', 'grant' => 'Grant'] as $source => $label)
                            <label class="block text-sm font-medium text-slate-700">{{ $label }} <span class="text-xs text-slate-400">(LKR)</span>
                                <input type="number" name="{{ $key }}[{{ $source }}]" value="{{ $value($key.'.'.$source) }}" x-model="amounts.{{ $key }}.{{ $source }}" min="0" max="999999999999.99" step="0.01" placeholder="0.00" @required($key === 'investment') class="mt-2 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <x-input-error class="mt-1" :messages="$errors->agreementData->get($key.'.'.$source)" />
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-slate-50 px-4 py-3">
                        <span class="text-sm text-slate-600">{{ $key === 'investment' ? 'Total Investment' : 'Total Expenditure' }}<span class="block text-xs text-slate-400">Own + Loan + Grant</span></span>
                        <output class="text-lg font-semibold text-emerald-800" aria-live="polite">LKR <span x-text="total('{{ $key }}')">0.00</span></output>
                    </div>
                    <x-agreement-progress :stage="$key" :data="$progressAmounts" :live="true" />
                </section>
            @endforeach
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4">
            <button type="button" @click="$dispatch('close')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
            <button type="submit" :disabled="saving" class="rounded-md bg-emerald-700 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-50" x-text="saving ? 'Saving...' : 'Save Financial Data'">Save Financial Data</button>
        </div>
    </form>
</x-modal>
