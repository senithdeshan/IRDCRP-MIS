@php
    $saved = $eoi->agreement_eoi_data ?? [];
    $failed = $errors->agreementData->any() && (string) old('agreement_eoi_id') === (string) $eoi->id;
    $value = fn ($key, $default = '') => $failed ? old($key, data_get($saved, $key, $default)) : data_get($saved, $key, $default);
@endphp
<x-modal name="eoi-data-{{ $eoi->id }}" :show="$failed" focusable>
    <form method="POST" action="{{ route('agreement-sign-fop.data', $eoi) }}" enctype="multipart/form-data" role="dialog" aria-modal="true" aria-labelledby="eoi-data-title-{{ $eoi->id }}" class="text-left" x-data="{ saving: false }" @submit="saving = true">
        @csrf
        @method('PATCH')
        <input type="hidden" name="agreement_eoi_id" value="{{ $eoi->id }}">
        <div class="flex items-start justify-between gap-4 bg-emerald-900 px-6 py-5 text-white">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-200">Component 1.2 · Agreement Sign FOP</p>
                <h2 id="eoi-data-title-{{ $eoi->id }}" class="mt-2 text-xl font-semibold">Add EOI Data</h2>
                <p class="mt-1 text-sm text-emerald-100">{{ $eoi->eoi_number }} · {{ $eoi->organization_name ?: 'Farmer organization' }}</p>
            </div>
            <button type="button" @click="$dispatch('close')" aria-label="Close EOI data popup" class="rounded-md px-3 py-1 text-2xl hover:bg-emerald-800">&times;</button>
        </div>
        <div class="max-h-[65vh] space-y-5 overflow-y-auto bg-slate-50 p-4 sm:p-6">
            @if ($failed)
                <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Please correct the following fields. Select uploads again before saving.</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->agreementData->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-slate-900">Agreement details</h3>
                <label class="mt-4 block text-sm font-medium text-slate-700">Agreement Sign Date <span class="text-red-600">*</span>
                    <input type="date" name="agreement_sign_date" value="{{ $value('agreement_sign_date') }}" required class="mt-2 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                </label>
            </section>
            @foreach (['investment' => 'Total Investment', 'tr1' => 'TR1', 'tr2' => 'TR2', 'tr3' => 'TR3', 'revised' => 'Revised Investment'] as $key => $title)
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" x-data="{ own: @js($value($key.'.own')), loan: @js($value($key.'.loan')), grant: @js($value($key.'.grant')), total() { return ((Math.round(Number(this.own || 0) * 100) + Math.round(Number(this.loan || 0) * 100) + Math.round(Number(this.grant || 0) * 100)) / 100).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); } }">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800">{{ $key === 'investment' ? 'Agreement budget' : 'Expenditure' }}</span>
                    </div>
                    @if ($key !== 'investment')
                        <label class="mt-4 block text-sm font-medium text-slate-700">Date
                            <input type="date" name="{{ $key }}[date]" value="{{ $value($key.'.date') }}" class="mt-2 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </label>
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach (['own' => 'Own', 'loan' => 'Loan', 'grant' => 'Grant'] as $source => $label)
                            <label class="block text-sm font-medium text-slate-700">{{ $label }} <span class="text-xs text-slate-400">(LKR)</span>
                                <input type="number" name="{{ $key }}[{{ $source }}]" x-model="{{ $source }}" min="0" max="999999999999.99" step="0.01" placeholder="0.00" @required($key === 'investment') class="mt-2 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-slate-50 px-4 py-3">
                        <span class="text-sm text-slate-600">{{ $key === 'investment' ? 'Total Investment' : 'Total Expenditure' }}<span class="block text-xs text-slate-400">Own + Loan + Grant</span></span>
                        <output class="text-lg font-semibold text-emerald-800" aria-live="polite">LKR <span x-text="total()">0.00</span></output>
                    </div>
                </section>
                @if ($key === 'investment')
                    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-900">Proposal &amp; attachments</h3>
                        <p class="mt-1 text-xs text-slate-500">Maximum 10 MB per file. Add up to 10 supporting files at a time.</p>
                        <label class="mt-4 block rounded-lg border border-dashed border-emerald-300 bg-emerald-50 p-4 text-sm font-medium text-slate-700">Full Proposal PDF
                            <input type="file" name="full_proposal" accept=".pdf" class="mt-3 block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:font-semibold file:text-white">
                        </label>
                        @if (isset($saved['full_proposal']))
                            <a href="{{ route('agreement-sign-fop.document', [$eoi, 'proposal']) }}" class="mt-2 block break-all text-xs text-emerald-700 underline">{{ $saved['full_proposal']['name'] }}</a>
                            <p class="mt-1 text-xs text-slate-500">Choose a new PDF to replace the current proposal.</p>
                        @endif
                        <label class="mt-4 block rounded-lg border border-dashed border-slate-300 p-4 text-sm font-medium text-slate-700">+ Other attachments
                            <span class="mt-1 block text-xs font-normal text-slate-500">PDF, JPG, PNG, Word or Excel documents</span>
                            <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-3 block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold file:text-slate-700">
                        </label>
                        @foreach ($saved['attachments'] ?? [] as $index => $attachment)
                            <a href="{{ route('agreement-sign-fop.document', [$eoi, $index]) }}" class="mt-2 block break-all text-xs text-emerald-700 underline">{{ $attachment['name'] }}</a>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4">
            <button type="button" @click="$dispatch('close')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
            <button type="submit" :disabled="saving" class="rounded-md bg-emerald-700 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:opacity-50" x-text="saving ? 'Saving…' : 'Save EOI Data'">Save EOI Data</button>
        </div>
    </form>
</x-modal>
