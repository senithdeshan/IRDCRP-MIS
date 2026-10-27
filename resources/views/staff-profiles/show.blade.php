<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">IRDCRP staff directory</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Staff Profile</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff-profiles.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Back to directory</a>
                <form method="POST" action="{{ route('staff-profiles.destroy', $profile) }}" data-confirm-title="Delete this staff profile?" data-confirm-message="{{ $profile->name_with_initials }}'s profile and uploaded documents will be permanently deleted." data-confirm-action="Yes, delete" data-confirm-cancel="No, keep profile" data-confirm-tone="rose">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Delete Profile</button>
                </form>
                <a href="{{ route('staff-profiles.edit', $profile) }}" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">Edit profile</a>
            </div>
        </div>
    </x-slot>

    <section class="staff-record-page">
        <div class="staff-record-container">
            @if(session('status'))
                <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif

            <header class="staff-record-hero">
                <div class="staff-record-identity">
                    @if($profile->photo_path)
                        <img src="{{ route('staff-profiles.document', [$profile, 'photo']) }}" alt="Photo of {{ $profile->name_with_initials }}" class="staff-record-photo">
                    @else
                        <div class="staff-record-avatar" aria-hidden="true">{{ Str::upper(Str::substr($profile->name_with_initials, 0, 1)) }}</div>
                    @endif
                    <div class="staff-record-heading">
                        <p class="staff-record-eyebrow">Employee record #{{ str_pad((string) $profile->id, 5, '0', STR_PAD_LEFT) }}</p>
                        <h2>{{ $profile->name_with_initials }}</h2>
                        <p class="staff-record-designation">{{ $profile->designation }}</p>
                        <div class="staff-record-tags">
                            <span>{{ $profile->staff_type }}</span>
                            @if($profile->grade)<span>Grade {{ $profile->grade }}</span>@endif
                        </div>
                    </div>
                </div>
                <div @class(['staff-record-status', 'staff-record-status-active' => $profile->employment_status === 'In service', 'staff-record-status-resigned' => $profile->employment_status === 'Resigned'])>
                    <span aria-hidden="true" class="staff-record-status-dot"></span>{{ $profile->employment_status }}
                </div>
            </header>

            <div class="staff-record-grid">
                @foreach([
                    'Personal information' => ['NIC Number' => $profile->nic_number, 'Date of Birth' => $profile->date_of_birth?->format('d M Y'), 'Gender' => $profile->gender, 'Permanent Address' => $profile->permanent_address],
                    'Employment details' => ['Designation' => $profile->designation, 'Staff Type' => $profile->staff_type, 'Grade' => $profile->grade, 'First Appointment Date' => $profile->first_appointment_date?->format('d M Y'), 'W and OP Number' => $profile->w_op_number, 'Personal File Number' => $profile->personal_file_number],
                    'Contact information' => ['Official Email Address' => $profile->official_email, 'Contact Number' => $profile->contact_number, 'Mobile / Fixed Number' => $profile->mobile_fixed],
                    'Education' => ['Highest Education Qualifications' => $profile->highest_education_qualifications],
                ] as $section => $items)
                    <section class="staff-record-card">
                        <div class="staff-record-card-heading"><span class="staff-record-card-mark" aria-hidden="true"></span><h3>{{ $section }}</h3></div>
                        <dl class="staff-record-fields">
                            @foreach($items as $label => $value)
                                <div @class(['staff-record-field', 'staff-record-field-wide' => $label === 'Permanent Address' || $label === 'Highest Education Qualifications'])>
                                    <dt>{{ $label }}</dt>
                                    <dd>{{ filled($value) ? $value : 'Not provided' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endforeach

                <section class="staff-record-card staff-record-documents">
                    <div class="staff-record-card-heading"><span class="staff-record-card-mark" aria-hidden="true"></span><h3>Documents</h3></div>
                    <div class="staff-record-document-list">
                        @foreach([['photo_path', 'photo_name', 'photo', 'Staff photo'], ['appointment_letter_path', 'appointment_letter_name', 'appointment-letter', 'Appointment letter'], ['cv_path', 'cv_name', 'cv', 'Curriculum vitae']] as [$column, $nameColumn, $type, $label])
                            @if($profile->{$column})
                                <a href="{{ route('staff-profiles.document', [$profile, $type]) }}{{ $type === 'photo' ? '?download=1' : '' }}" class="staff-record-document">
                                    <span class="staff-record-document-icon" aria-hidden="true">{{ $type === 'photo' ? 'IMG' : 'PDF' }}</span>
                                    <span class="staff-record-document-copy"><strong>{{ $label }}</strong><small>{{ $profile->{$nameColumn} ?: $label }}</small></span>
                                    <span class="staff-record-document-download" aria-hidden="true">Download</span>
                                </a>
                            @endif
                        @endforeach
                        @if(!$profile->photo_path && !$profile->appointment_letter_path && !$profile->cv_path)
                            <p class="staff-record-empty-documents">No documents have been uploaded for this profile.</p>
                        @endif
                    </div>
                </section>
            </div>

            <footer class="staff-record-footer">Profile created {{ $profile->created_at?->format('d M Y') }}@if($profile->updated_at && $profile->updated_at->ne($profile->created_at)) <span>·</span> Last updated {{ $profile->updated_at->format('d M Y') }}@endif</footer>
        </div>
    </section>
</x-app-layout>
