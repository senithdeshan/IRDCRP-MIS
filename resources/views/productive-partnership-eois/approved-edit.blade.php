<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Approved Farmer Producer Group</p>
                <h1 class="text-2xl font-semibold text-slate-950">{{ $eoi->eoi_number }}</h1>
                <p class="text-sm text-slate-500">{{ $eoi->organization_name }}</p>
            </div>
            <a href="{{ route('approved-farmer-producer-groups.index') }}" class="w-fit rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Back</a>
        </div>
    </x-slot>

    <section class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <form method="POST" action="{{ route('approved-farmer-producer-groups.update', $eoi) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ([
                        ['key' => 'pre_construction', 'title' => 'Pre Construction'],
                        ['key' => 'during_construction', 'title' => 'During Construction'],
                        ['key' => 'post_construction', 'title' => 'Post Construction'],
                    ] as $stage)
                        @php
                            $dateField = $stage['key'].'_date';
                            $notesField = $stage['key'].'_notes';
                            $imagesField = $stage['key'].'_images';
                        @endphp
                        <div class="panel-surface p-6">
                            <h2 class="text-base font-semibold text-slate-950">{{ $stage['title'] }}</h2>
                            <div class="mt-5 space-y-4">
                                <div>
                                    <x-input-label :for="$dateField" value="Date" />
                                    <x-text-input :id="$dateField" :name="$dateField" type="date" class="mt-2 block w-full" :value="old($dateField, optional($eoi->{$dateField})->format('Y-m-d'))" />
                                    <x-input-error class="mt-2" :messages="$errors->get($dateField)" />
                                </div>
                                <div>
                                    <x-input-label :for="$notesField" value="Data / Notes" />
                                    <textarea id="{{ $notesField }}" name="{{ $notesField }}" rows="4" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($notesField, $eoi->{$notesField}) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get($notesField)" />
                                </div>
                                <div>
                                    <x-input-label :for="$imagesField" value="Images" />
                                    <input id="{{ $imagesField }}" name="{{ $imagesField }}[]" type="file" accept="image/*" multiple class="mt-2 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                                    <x-input-error class="mt-2" :messages="$errors->get($imagesField.'.*')" />
                                </div>
                                @if (count($eoi->{$imagesField} ?? []))
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach ($eoi->{$imagesField} as $image)
                                            <a href="{{ url('storage/'.$image) }}" target="_blank" class="block overflow-hidden rounded-md border border-slate-200">
                                                <img src="{{ url('storage/'.$image) }}" alt="{{ $stage['title'] }}" class="h-20 w-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="panel-surface p-6">
                    <h2 class="text-base font-semibold text-slate-950">Final Documents</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="business_registration_image" value="Business Registration Image" />
                            <input id="business_registration_image" name="business_registration_image" type="file" accept="image/*" class="mt-2 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            <x-input-error class="mt-2" :messages="$errors->get('business_registration_image')" />
                            @if ($eoi->business_registration_image)
                                <a href="{{ url('storage/'.$eoi->business_registration_image) }}" target="_blank" class="mt-3 inline-flex rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">View BR Image</a>
                            @endif
                        </div>
                        <div>
                            <x-input-label for="business_proposal_pdf" value="Business Proposal PDF" />
                            <input id="business_proposal_pdf" name="business_proposal_pdf" type="file" accept="application/pdf,.pdf" class="mt-2 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            <x-input-error class="mt-2" :messages="$errors->get('business_proposal_pdf')" />
                            @if ($eoi->business_proposal_pdf)
                                <a href="{{ url('storage/'.$eoi->business_proposal_pdf) }}" target="_blank" class="mt-3 inline-flex rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">View Proposal PDF</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="rounded-md bg-slate-950 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Save Updates</button>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
