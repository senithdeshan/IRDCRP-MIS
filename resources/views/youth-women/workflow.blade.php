<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Component 1.3 · Individual Entrepreneurs</p>
        <h1 class="mt-1 text-2xl font-semibold text-slate-950">{{ $title }}</h1>
    </x-slot>
    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-5">
            @if (session('status'))
                <div role="status" class="rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">Please correct the highlighted review fields. <x-input-error :messages="$errors->get('action')" /></div>
            @endif
            <nav aria-label="Individual workflow stages" class="flex flex-wrap gap-2">
                <a href="{{ route('individual-eois.index') }}" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-semibold">Received All EOI</a>
                @foreach ($stages as $route => [$key, $label])
                    <a href="{{ route('individual-workflow.'.$route) }}" @if ($key === $stage) aria-current="page" @endif class="rounded-md border px-3 py-2 text-xs font-semibold {{ $key === $stage ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200 bg-white text-slate-600' }}">{{ $loop->iteration }}. {{ $label }}</a>
                @endforeach
            </nav>
            <div class="panel-surface flex flex-wrap items-center justify-between gap-4 p-4">
                <p class="text-sm text-slate-600"><strong class="text-lg text-slate-950">{{ number_format($total) }}</strong> individuals in this stage</p>
                <form method="GET" class="flex gap-2">
                    <input name="search" value="{{ request('search') }}" aria-label="Search applicants" placeholder="EOI, applicant or business" class="min-w-0 rounded-md border-slate-300 text-sm">
                    <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Search</button>
                </form>
            </div>
            @if ($stage === 'interview')
                <p class="text-sm text-slate-500">Individuals marked Selected in initial screening enter this queue. Interview marks above 50 move them to field verification.</p>
            @endif
            @forelse ($applicants as $applicant)
                @php
                    $completed = $applicant->workflow_stage === 'completed';
                    $hasOldInput = (string) old('_applicant') === (string) $applicant->id;
                    $saved = $applicant->workflow_data[$stage] ?? [];
                    $fields = match ($stage) {
                        'interview' => ['marks' => ['Interview Marks (0–100)', 'number'], 'interview_status' => ['Interview Status', 'select']],
                        'verification' => ['visit_date' => ['Field Visit Date', 'date'], 'result' => ['Verification Result', 'select']],
                        'proposal' => ['result' => ['Full Proposal Result', 'select']],
                        'agreement' => ['agreement_number' => ['Agreement Number', 'text'], 'signed_date' => ['Signed Date', 'date']],
                        default => [],
                    };
                    $options = $stage === 'interview' ? $interviewStatuses : ($stage === 'verification' ? ['pending' => 'Pending', 'approved' => 'Approved', 'not_approved' => 'Not Approved'] : ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']);
                @endphp
                <article class="panel-surface overflow-hidden">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 bg-slate-50 px-5 py-4">
                        <div>
                            <p class="text-xs font-semibold text-emerald-700">{{ $applicant->eoi_number }}</p>
                            <h2 class="mt-1 text-base font-semibold text-slate-950">{{ $applicant->applicant_name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $applicant->business_name }} · {{ $applicant->district ?: 'District not recorded' }} · {{ $applicant->telephone }}</p>
                        </div>
                        <a href="{{ route('business-information.edit', $applicant) }}" class="text-sm font-semibold text-emerald-700">Applicant details</a>
                    </div>
                    <div class="space-y-4 p-5">
                        @if ($stage === 'agreement')
                            <div class="flex flex-wrap gap-2">
                                <button type="button" x-data @click="$dispatch('open-modal', 'eoi-data-{{ $applicant->id }}')" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Add EOI Data</button>
                                <button type="button" x-data @click="$dispatch('open-modal', 'eoi-data-view-{{ $applicant->id }}')" class="rounded-md border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">EOI Data View Details</button>
                            </div>
                        @endif
                        @if ($completed)
                            <p class="rounded-md bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">Agreement signed: {{ $saved['agreement_number'] ?? '' }} · {{ $saved['signed_date'] ?? '' }}</p>
                        @else
                            <form method="POST" action="{{ route('individual-workflow.advance', $applicant) }}" novalidate class="space-y-4">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="{{ $stage }}">
                                <input type="hidden" name="_applicant" value="{{ $applicant->id }}">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach ($fields as $name => [$label, $type])
                                        @php($value = $hasOldInput ? old($name) : ($saved[$name] ?? ''))
                                        <div>
                                            <label for="{{ $name }}_{{ $applicant->id }}" class="block text-sm font-medium text-slate-700">{{ $label }} *</label>
                                            @if ($type === 'select')
                                                <select id="{{ $name }}_{{ $applicant->id }}" name="{{ $name }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                                    <option value="">Choose a result</option>
                                                    @foreach ($options as $key => $option)<option value="{{ $key }}" @selected($value === $key)>{{ $option }}</option>@endforeach
                                                </select>
                                            @else
                                                <input id="{{ $name }}_{{ $applicant->id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" required @if ($type === 'number') min="0" max="100" step="0.01" @elseif ($type === 'date') max="{{ now()->toDateString() }}" @else maxlength="255" @endif class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                            @endif
                                            @if ($hasOldInput)<x-input-error class="mt-1" :messages="$errors->get($name)" />@endif
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label for="notes_{{ $applicant->id }}" class="block text-sm font-medium text-slate-700">Review notes</label>
                                    <textarea id="notes_{{ $applicant->id }}" name="notes" rows="2" maxlength="5000" class="mt-1 block w-full rounded-md border-slate-300 text-sm">{{ $hasOldInput ? old('notes') : ($saved['notes'] ?? '') }}</textarea>
                                    @if ($hasOldInput)<x-input-error class="mt-1" :messages="$errors->get('notes')" />@endif
                                </div>
                                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">{{ match ($stage) { 'approved' => 'Move to Full Proposal Preparation', 'agreement' => 'Record Signed Agreement', default => 'Save Review' } }}</button>
                            </form>
                        @endif
                        @if ($applicant->workflow_history)
                            <details class="rounded-md border border-slate-200 p-3">
                                <summary class="cursor-pointer text-sm font-semibold text-slate-600">Review history ({{ count($applicant->workflow_history) }})</summary>
                                <ol class="mt-3 space-y-3">
                                    @foreach (array_reverse($applicant->workflow_history) as $entry)
                                        <li class="border-l-2 border-emerald-200 pl-3 text-xs text-slate-600">
                                            <p class="font-semibold">{{ ucfirst($entry['from']) }} → {{ ucfirst($entry['to']) }} · {{ \Carbon\Carbon::parse($entry['at'])->format('Y-m-d H:i') }}</p>
                                            @foreach (($entry['data'] ?? []) as $key => $value)
                                                @if (is_scalar($value) && filled($value))<p class="mt-1">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</p>@endif
                                            @endforeach
                                        </li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif
                    </div>
                </article>
                @if ($stage === 'agreement')
                    @include('youth-women.partials.agreement-eoi-data', ['eoi' => $applicant])
                    @include('youth-women.partials.agreement-eoi-data-view', ['eoi' => $applicant])
                @endif
            @empty
                <div class="panel-surface p-10 text-center text-sm text-slate-500">No individuals in this stage. Applicants appear after completing the previous step.</div>
            @endforelse
            {{ $applicants->links() }}
        </div>
    </section>
</x-app-layout>
