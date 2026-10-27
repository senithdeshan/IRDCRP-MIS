@php($editing = isset($profile))
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('staff-profiles.update', $profile) : route('staff-profiles.store') }}" class="staff-profile-form">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div role="alert" class="staff-form-alert">
            <p class="font-semibold">Please review the highlighted information.</p>
            <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="staff-form-section">
        <div class="staff-form-section-heading">
            <span class="staff-form-section-number">01</span>
            <div><h2>Personal and appointment details</h2>
            <p>Enter the official staff information. Fields marked <span>*</span> are required.</p></div>
        </div>
        <div class="staff-form-grid">
            <label class="staff-form-field">
                <span class="staff-form-label">Employment Status <span class="staff-form-required">*</span></span>
                <select name="employment_status" required @class(['staff-form-control', 'staff-form-control-error' => $errors->has('employment_status')])>
                    @foreach (['In service', 'Resigned'] as $option)
                        <option value="{{ $option }}" @selected(old('employment_status', $editing ? $profile->employment_status : 'In service') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('employment_status')<span class="staff-form-error">{{ $message }}</span>@enderror
            </label>
            @php($fields = [
                ['staff_type', 'Staff Type', 'text', true, 'e.g. Permanent, Contract'],
                ['name_with_initials', 'Name with Initials', 'text', true, 'Enter name with initials'],
                ['nic_number', 'NIC Number', 'text', true, 'Enter NIC number'],
                ['grade', 'Grade', 'text', false, 'Enter grade'],
                ['designation', 'Designation', 'text', true, 'Enter designation'],
                ['contact_number', 'Contact Number', 'tel', false, 'Enter contact number'],
                ['mobile_fixed', 'Mobile / Fixed Number', 'tel', false, 'Enter mobile or fixed number'],
                ['official_email', 'Official Email Address', 'email', false, 'Enter official email'],
                ['date_of_birth', 'Date of Birth', 'date', false, ''],
                ['w_op_number', 'W and OP Number', 'text', false, 'Enter W and OP number'],
                ['personal_file_number', 'Personal File Number', 'text', false, 'Enter personal file number'],
                ['first_appointment_date', 'First Appointment Date', 'date', false, ''],
            ])
            @foreach ($fields as [$name, $label, $type, $required, $placeholder])
                <label class="staff-form-field">
                    <span class="staff-form-label">{{ $label }} @if($required)<span class="staff-form-required">*</span>@endif</span>
                    <input name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $editing ? ($type === 'date' ? $profile->{$name}?->format('Y-m-d') : $profile->{$name}) : '') }}" placeholder="{{ $placeholder }}" @required($required)
                        @class(['staff-form-control', 'staff-form-control-error' => $errors->has($name)])>
                    @error($name)<span class="staff-form-error">{{ $message }}</span>@enderror
                </label>
            @endforeach
            <label class="staff-form-field">
                <span class="staff-form-label">Gender</span>
                <select name="gender" class="staff-form-control">
                    <option value="">Select gender</option>
                    @foreach (['Female', 'Male', 'Other', 'Prefer not to say'] as $option)<option value="{{ $option }}" @selected(old('gender', $editing ? $profile->gender : '') === $option)>{{ $option }}</option>@endforeach
                </select>
            </label>
            <label class="staff-form-field">
                <span class="staff-form-label">Highest Education Qualifications</span>
                <textarea name="highest_education_qualifications" rows="2" placeholder="Enter education qualifications" class="staff-form-control">{{ old('highest_education_qualifications', $editing ? $profile->highest_education_qualifications : '') }}</textarea>
            </label>
            <label class="staff-form-field staff-form-field-wide">
                <span class="staff-form-label">Permanent Address</span>
                <textarea name="permanent_address" rows="3" placeholder="Enter permanent address" class="staff-form-control">{{ old('permanent_address', $editing ? $profile->permanent_address : '') }}</textarea>
            </label>
        </div>
    </section>

    <section class="staff-form-section">
        <div class="staff-form-section-heading">
            <span class="staff-form-section-number">02</span>
            <div><h2>Staff documents</h2>
            <p>Upload a clear photo, appointment letter, and CV where available.</p></div>
        </div>
        <div class="staff-form-upload-grid">
            @foreach ([['photo', 'Staff Photo', 'image/*', 'photo_path'], ['appointment_letter', 'Appointment Letter (PDF)', 'application/pdf,.pdf', 'appointment_letter_path'], ['cv', 'Upload CV', '.pdf,.doc,.docx', 'cv_path']] as [$input, $label, $accept, $column])
                <label class="staff-form-upload">
                    <span class="staff-form-label">{{ $label }}</span>
                    @if($editing && $profile->{$column})<a class="staff-form-current-file" href="{{ route('staff-profiles.document', [$profile, str_replace('_', '-', $input)]) }}">View current file</a>@endif
                    <input name="{{ $input }}" type="file" accept="{{ $accept }}" class="staff-form-file">
                    <span class="staff-form-hint">@if($input === 'photo') JPG or PNG, up to 5 MB @elseif($input === 'appointment_letter') PDF, up to 10 MB @else PDF or Word, up to 10 MB @endif</span>
                    @error($input)<span class="staff-form-error">{{ $message }}</span>@enderror
                </label>
            @endforeach
        </div>
    </section>

    <div class="staff-form-footer">
        <p><span>*</span> Required information</p>
        <div><a href="{{ route('staff-profiles.index') }}" class="staff-form-cancel">Cancel</a>
        <button type="submit" class="staff-form-submit">{{ $editing ? 'Save Changes' : 'Register Staff Profile' }} <span aria-hidden="true">→</span></button></div>
    </div>
</form>
