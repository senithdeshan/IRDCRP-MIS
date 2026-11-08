<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\User;
use App\Rules\SriLankanNic;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    public function index(Request $request): View
    {
        $query = StaffProfile::query()->with('user')->latest();
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name_with_initials', 'like', "%{$search}%")
                    ->orWhere('nic_number', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('official_email', 'like', "%{$search}%")
                    ->orWhere('staff_type', 'like', "%{$search}%");
            });
        }

        $profiles = $query->paginate(12)->withQueryString();
        $emails = $profiles->getCollection()->pluck('official_email')->filter()
            ->map(fn (string $email) => mb_strtolower($email))->unique()->values();
        $usersByEmail = User::query()->whereIn(DB::raw('LOWER(email)'), $emails)
            ->get(['id', 'email'])->keyBy(fn (User $user) => mb_strtolower($user->email));

        return view('staff-profiles.index', [
            'profiles' => $profiles,
            'usersByEmail' => $usersByEmail,
            'search' => $search,
            'canManage' => $this->canManage($request),
            'summary' => [
                'total' => StaffProfile::count(),
                'male' => StaffProfile::whereRaw('LOWER(gender) = ?', ['male'])->count(),
                'female' => StaffProfile::whereRaw('LOWER(gender) = ?', ['female'])->count(),
                'byType' => StaffProfile::query()
                    ->selectRaw('staff_type, COUNT(*) as member_count')
                    ->groupBy('staff_type')
                    ->orderBy('staff_type')
                    ->get(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeManagement($request);

        return view('staff-profiles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['photo_path'] = $request->file('photo')?->store('staff-profiles/photos', 'local');
        $data['photo_name'] = $request->file('photo')?->getClientOriginalName();
        $data['appointment_letter_path'] = $request->file('appointment_letter')?->store('staff-profiles/letters', 'local');
        $data['appointment_letter_name'] = $request->file('appointment_letter')?->getClientOriginalName();
        $data['cv_path'] = $request->file('cv')?->store('staff-profiles/cvs', 'local');
        $data['cv_name'] = $request->file('cv')?->getClientOriginalName();
        unset($data['photo'], $data['appointment_letter'], $data['cv']);

        $profile = StaffProfile::create($data);

        return redirect()->route('staff-profiles.show', $profile)->with('status', 'Staff profile registered successfully.');
    }

    public function show(Request $request, StaffProfile $staffProfile): View
    {
        return view('staff-profiles.show', [
            'profile' => $staffProfile->load('user'),
            'registeredAccount' => $this->registeredAccount($staffProfile),
            'canManage' => $this->canManage($request),
        ]);
    }

    public function edit(Request $request, StaffProfile $staffProfile): View
    {
        $this->authorizeManagement($request);

        return view('staff-profiles.edit', [
            'profile' => $staffProfile,
            'registeredAccount' => $this->registeredAccount($staffProfile),
        ]);
    }

    public function update(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        $this->authorizeManagement($request);

        DB::transaction(function () use ($request, $staffProfile) {
            $staffProfile = StaffProfile::query()->lockForUpdate()->findOrFail($staffProfile->id);
            $data = $this->validated($request, $staffProfile);

            foreach ([
                'photo' => 'photo_path',
                'appointment_letter' => 'appointment_letter_path',
                'cv' => 'cv_path',
            ] as $input => $column) {
                if ($request->hasFile($input)) {
                    if ($staffProfile->{$column}) {
                        Storage::disk('local')->delete($staffProfile->{$column});
                    }
                    $folder = match ($input) {
                        'photo' => 'photos',
                        'appointment_letter' => 'letters',
                        default => 'cvs',
                    };
                    $data[$column] = $request->file($input)->store("staff-profiles/{$folder}", 'local');
                    $nameColumn = match ($input) {
                        'photo' => 'photo_name',
                        'appointment_letter' => 'appointment_letter_name',
                        default => 'cv_name',
                    };
                    $data[$nameColumn] = $request->file($input)->getClientOriginalName();
                }
            }

            unset($data['photo'], $data['appointment_letter'], $data['cv']);
            $staffProfile->update($data);
        });

        return redirect()->route('staff-profiles.show', $staffProfile)->with('status', 'Staff profile updated successfully.');
    }

    public function destroy(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        $this->authorizeManagement($request);

        foreach (['photo_path', 'appointment_letter_path', 'cv_path'] as $column) {
            if ($staffProfile->{$column}) {
                Storage::disk('local')->delete($staffProfile->{$column});
            }
        }

        $staffProfile->delete();

        return redirect()->route('staff-profiles.index')->with('status', 'Staff profile deleted successfully.');
    }

    public function document(Request $request, StaffProfile $staffProfile, string $type)
    {
        $column = match ($type) {
            'photo' => 'photo_path',
            'appointment-letter' => 'appointment_letter_path',
            'cv' => 'cv_path',
            default => abort(404),
        };
        $path = $staffProfile->{$column};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $nameColumn = match ($type) {
            'photo' => 'photo_name',
            'appointment-letter' => 'appointment_letter_name',
            'cv' => 'cv_name',
        };

        if ($type === 'photo' && ! $request->boolean('download')) {
            return response()->file(Storage::disk('local')->path($path));
        }

        return Storage::disk('local')->download($path, $staffProfile->{$nameColumn} ?: basename($path));
    }

    private function validated(Request $request, ?StaffProfile $profile = null): array
    {
        if (is_string($request->input('official_email'))) {
            $request->merge(['official_email' => mb_strtolower(trim($request->input('official_email')))]);
        }

        $data = $request->validate([
            'staff_type' => ['required', Rule::in(['Project Director', 'Deputy Project Director', 'Subject Specialist', 'Project Coordinator', 'Technical Officer', 'MIS Officer'])],
            'employment_status' => ['required', Rule::in(['In service', 'Resigned'])],
            'name_with_initials' => ['required', 'string', 'max:255'],
            'nic_number' => ['required', 'string', 'max:30', new SriLankanNic, Rule::unique('staff_profiles', 'nic_number')->ignore($profile)],
            'permanent_address' => ['nullable', 'string', 'max:2000'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'mobile_fixed' => ['nullable', 'string', 'max:30'],
            'official_email' => [
                'bail',
                Rule::requiredIf($request->boolean('registration_enabled')),
                'nullable', 'string', 'email', 'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($profile): void {
                    $duplicate = StaffProfile::query()
                        ->whereRaw('LOWER(official_email) = ?', [mb_strtolower($value)])
                        ->when($profile, fn ($query) => $query->whereKeyNot($profile->id))
                        ->exists();

                    if ($duplicate) {
                        $fail('This official email address is already assigned to another staff profile.');
                    }
                },
            ],
            'registration_enabled' => ['sometimes', 'boolean'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['Female', 'Male', 'Other', 'Prefer not to say'])],
            'w_op_number' => ['nullable', 'string', 'max:100'],
            'highest_education_qualifications' => ['nullable', 'string', 'max:2000'],
            'personal_file_number' => ['nullable', 'string', 'max:100'],
            'first_appointment_date' => ['nullable', 'date', 'before_or_equal:today'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'appointment_letter' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $data['registration_enabled'] = $request->boolean('registration_enabled');

        if ($profile?->user && strcasecmp((string) ($data['official_email'] ?? ''), $profile->user->email) !== 0) {
            throw ValidationException::withMessages([
                'official_email' => 'The official email must match this staff member\'s registered account.',
            ]);
        }

        if ($data['registration_enabled']) {
            if ($data['employment_status'] !== 'In service') {
                throw ValidationException::withMessages([
                    'registration_enabled' => 'Only staff members who are in service can be approved for registration.',
                ]);
            }

            if ($profile?->user_id || User::query()->whereRaw('LOWER(email) = ?', [$data['official_email']])->exists()) {
                throw ValidationException::withMessages([
                    'registration_enabled' => 'An account already exists for this staff member. They can sign in instead.',
                ]);
            }
        }

        // The database still requires designation; keep it aligned with the selected staff type.
        $data['designation'] = $data['staff_type'];

        return $data;
    }

    private function registeredAccount(StaffProfile $profile): ?User
    {
        $profile->loadMissing('user');

        return $profile->user ?? ($profile->official_email
            ? User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($profile->official_email)])->first()
            : null);
    }

    private function canManage(Request $request): bool
    {
        return $request->user()?->isSuperAdmin() ?? false;
    }

    private function authorizeManagement(Request $request): void
    {
        abort_unless($this->canManage($request), 403);
    }
}
