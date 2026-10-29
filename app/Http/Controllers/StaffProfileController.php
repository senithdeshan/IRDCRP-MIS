<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Rules\SriLankanNic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    public function index(Request $request): View
    {
        $query = StaffProfile::query()->latest();
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name_with_initials', 'like', "%{$search}%")
                    ->orWhere('nic_number', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('staff_type', 'like', "%{$search}%");
            });
        }

        return view('staff-profiles.index', [
            'profiles' => $query->paginate(12)->withQueryString(),
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

    public function show(StaffProfile $staffProfile): View
    {
        return view('staff-profiles.show', ['profile' => $staffProfile]);
    }

    public function edit(Request $request, StaffProfile $staffProfile): View
    {
        $this->authorizeManagement($request);

        return view('staff-profiles.edit', ['profile' => $staffProfile]);
    }

    public function update(Request $request, StaffProfile $staffProfile): RedirectResponse
    {
        $this->authorizeManagement($request);
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

        return redirect()->route('staff-profiles.show', $staffProfile)->with('status', 'Staff profile updated successfully.');
    }

    public function destroy(StaffProfile $staffProfile): RedirectResponse
    {
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
        return $request->validate([
            'staff_type' => ['required', 'string', 'max:100'],
            'employment_status' => ['required', Rule::in(['In service', 'Resigned'])],
            'name_with_initials' => ['required', 'string', 'max:255'],
            'nic_number' => ['required', 'string', 'max:30', new SriLankanNic, Rule::unique('staff_profiles', 'nic_number')->ignore($profile)],
            'grade' => ['nullable', 'string', 'max:100'],
            'designation' => ['required', 'string', 'max:255'],
            'permanent_address' => ['nullable', 'string', 'max:2000'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'mobile_fixed' => ['nullable', 'string', 'max:30'],
            'official_email' => ['nullable', 'email', 'max:255'],
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
    }

    private function canManage(Request $request): bool
    {
        return $request->user() !== null;
    }

    private function authorizeManagement(Request $request): void
    {
        abort_unless($request->user() !== null, 403);
    }
}
