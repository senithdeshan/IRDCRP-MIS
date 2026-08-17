<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public const ROLES = [
        'super_admin' => 'Super Admin',
        'administrator' => 'Administrator',
        'manager' => 'Manager',
        'officer' => 'Officer',
        'viewer' => 'Viewer',
        'staff' => 'Staff',
    ];

    public const PERMISSIONS = [
        'dashboard.view' => 'Dashboard',
        'staff.manage' => 'Staff Management',
        'productive_partnership.view' => 'Component 1.2 Productive Partnership',
        'farmer_organizations.view' => 'Farmer Organization Information',
        'selected_eois.view' => 'Selected All EOI',
        'field_visits.view' => 'Selected For Verification Field Visit Pass',
        'full_proposals.view' => 'Selected for Full Proposal Preparation',
        'agreement_sign_fop.view' => 'Agreement Sign FOP',
        'youth_women.view' => 'Component 1.3 Youth and Women Entrepreneurs',
        'business_information.view' => 'Business Information',
        'component_two.view' => 'Component 2',
        'cascade_registration.view' => 'Cascade Registration',
        'tank_registration.view' => 'Tank Registration',
        'reports.view' => 'Reports',
        'settings.manage' => 'Settings',
    ];

    public function index(): View
    {
        return view('staff.index', [
            'staff' => User::query()->latest()->paginate(10),
            'roles' => self::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('staff.create', [
            'roles' => self::ROLES,
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['permissions'] = $request->input('permissions', []);

        User::create($data);

        return redirect()->route('staff.index')->with('status', 'Staff member created successfully.');
    }

    public function edit(User $staff): View
    {
        return view('staff.edit', [
            'staffMember' => $staff,
            'roles' => self::ROLES,
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $data = $this->validated($request, $staff);
        $data['permissions'] = $request->input('permissions', []);

        if ($request->user()->is($staff) && $data['status'] !== 'active') {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.'])->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $staff->update($data);

        return redirect()->route('staff.index')->with('status', 'Staff member updated successfully.');
    }

    private function validated(Request $request, ?User $staff = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff)],
            'designation' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(array_keys(self::PERMISSIONS))],
            'password' => [$staff ? 'nullable' : 'required', 'confirmed', 'min:8'],
        ]);
    }
}
