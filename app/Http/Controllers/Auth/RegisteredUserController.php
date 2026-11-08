<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        if ($request->boolean('change_email')) {
            $request->session()->forget('staff_registration');
        }

        $approvedStaff = $this->approvedStaff($request);

        if (! $approvedStaff) {
            $request->session()->forget('staff_registration');
        }

        return view('auth.register', compact('approvedStaff'));
    }

    public function checkEmail(Request $request): RedirectResponse
    {
        $request->session()->forget('staff_registration');
        $this->normalizeEmail($request);
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $profile = StaffProfile::query()
            ->whereRaw('LOWER(official_email) = ?', [$data['email']])
            ->where('registration_enabled', true)
            ->where('employment_status', 'In service')
            ->whereNull('user_id')
            ->first();

        if (! $profile || $this->emailHasAccount($data['email'])) {
            throw ValidationException::withMessages([
                'email' => 'Registration is not available for this email. Ask the Super Admin to create your staff profile and enable registration. If you already registered, sign in instead.',
            ]);
        }

        $request->session()->put('staff_registration', [
            'profile_id' => $profile->id,
            'email' => $data['email'],
        ]);

        return redirect()->route('register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request, $data) {
            // Lock the profile so its single approval cannot create multiple accounts.
            $profile = $this->approvedStaff($request, lock: true);

            if (! $profile || $data['email'] !== $request->session()->get('staff_registration.email')) {
                $request->session()->forget('staff_registration');

                throw ValidationException::withMessages([
                    'email' => 'Please check your approved staff email before creating an account.',
                ]);
            }

            $user = User::create([
                'name' => $profile->name_with_initials,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'staff',
                'status' => 'active',
                'designation' => $profile->designation,
                'phone' => $profile->contact_number ?: $profile->mobile_fixed,
                'permissions' => ['dashboard.view'],
            ]);

            $profile->update(['user_id' => $user->id, 'registration_enabled' => false]);

            return $user;
        });

        event(new Registered($user));

        $request->session()->forget('staff_registration');
        $request->session()->regenerate();

        return redirect()->route('login')->with('status', 'Your staff account has been created. Sign in with your official email and password.');
    }

    private function approvedStaff(Request $request, bool $lock = false): ?StaffProfile
    {
        $profileId = $request->session()->get('staff_registration.profile_id');
        $email = $request->session()->get('staff_registration.email');

        if (! $profileId || ! is_string($email)) {
            return null;
        }

        $query = StaffProfile::query()
            ->whereKey($profileId)
            ->whereRaw('LOWER(official_email) = ?', [$email])
            ->where('registration_enabled', true)
            ->where('employment_status', 'In service')
            ->whereNull('user_id');

        if ($lock) {
            $query->lockForUpdate();
        }

        $profile = $query->first();

        return $profile && ! $this->emailHasAccount($email) ? $profile : null;
    }

    private function emailHasAccount(string $email): bool
    {
        return User::query()->whereRaw('LOWER(email) = ?', [$email])->exists();
    }

    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }
    }
}
