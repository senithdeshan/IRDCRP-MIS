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
        'selected_eois.view' => 'Received All EOI',
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

    public const PERMISSION_ACTIONS = [
        'view' => 'View',
        'add' => 'Add',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'upload_excel' => 'Upload Excel',
    ];

    public const PERMISSION_MODULES = [
        'dashboard' => ['label' => 'Dashboard', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'dashboard.view']],
        'staff_profile' => ['label' => 'Staff Profile', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'staff.manage']],
        'user_management' => ['label' => 'User Management', 'actions' => self::PERMISSION_ACTIONS],
        'productive_partnership' => ['label' => 'Component 1.2 - Productive Partnership', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'productive_partnership.view']],
        'farmer_organizations' => ['label' => 'Farmer Organization Information', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'farmer_organizations.view']],
        'received_all_eoi' => ['label' => 'Received All EOI', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'selected_eois.view']],
        'review_interviews' => ['label' => 'Selected for Review Interview', 'actions' => self::PERMISSION_ACTIONS],
        'field_visits' => ['label' => 'Selected for Verification Field Visit', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'field_visits.view']],
        'proposal_preparation' => ['label' => 'Proposal Preparation', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'full_proposals.view']],
        'agreement_signed_fop' => ['label' => 'Agreement Signed FPO', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'agreement_sign_fop.view']],
        'youth_women' => ['label' => 'Component 1.3 - Youth and Women Entrepreneurs', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'youth_women.view']],
        'individual_entrepreneur_information' => ['label' => 'Individual Entrepreneur Information', 'actions' => self::PERMISSION_ACTIONS],
        'individual_eois' => ['label' => 'Individual Received All EOI', 'actions' => self::PERMISSION_ACTIONS],
        'individual_review_interviews' => ['label' => 'Individual Review Interviews', 'actions' => self::PERMISSION_ACTIONS],
        'individual_field_visits' => ['label' => 'Individual Field Visits', 'actions' => self::PERMISSION_ACTIONS],
        'individual_proposals' => ['label' => 'Approved Individual Entrepreneurs', 'actions' => self::PERMISSION_ACTIONS],
        'individual_full_proposals' => ['label' => 'Selected for Full Proposal Preparation', 'actions' => self::PERMISSION_ACTIONS],
        'individual_agreements' => ['label' => 'Agreement Sign Individual Entrepreneur Information', 'actions' => self::PERMISSION_ACTIONS],
        'business_information' => ['label' => 'Individual Business Information', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'business_information.view']],
        'individual_add_applicant' => ['label' => 'Add Individual Applicant', 'actions' => self::PERMISSION_ACTIONS],
        'individual_selected' => ['label' => 'Selected Individuals', 'actions' => self::PERMISSION_ACTIONS],
        'individual_rejected' => ['label' => 'Rejected Individuals', 'actions' => self::PERMISSION_ACTIONS],
        'component_two' => ['label' => 'Component 2', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'component_two.view']],
        'cascade_registration' => ['label' => 'Cascade Registration', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'cascade_registration.view']],
        'tank_registration' => ['label' => 'Tank Registration', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'tank_registration.view']],
        'irrigation_infrastructure' => ['label' => 'Irrigation Infrastructure Assets Rehabilitated / Modernized', 'actions' => self::PERMISSION_ACTIONS],
        'irrigation_assets' => ['label' => 'Scheme Details (A and B)', 'actions' => self::PERMISSION_ACTIONS],
        'irrigation_farmer_organizations' => ['label' => 'Farmer Organization & Members (C and D)', 'actions' => self::PERMISSION_ACTIONS],
        'cerc' => ['label' => 'CERC', 'actions' => self::PERMISSION_ACTIONS],
        'cerc_minor_scheme_details' => ['label' => 'Minor Tank - Scheme Details (A and B)', 'actions' => self::PERMISSION_ACTIONS],
        'cerc_minor_farmer_org_members' => ['label' => 'Minor Tank - Farmer Organization & Members (C and D)', 'actions' => self::PERMISSION_ACTIONS],
        'cerc_major_scheme_details' => ['label' => 'Major Tanks - Scheme Details (A and B)', 'actions' => self::PERMISSION_ACTIONS],
        'cerc_major_farmer_org_members' => ['label' => 'Major Tanks - Farmer Organization & Members (C and D)', 'actions' => self::PERMISSION_ACTIONS],
        'reports' => ['label' => 'Reports', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'reports.view']],
        'settings' => ['label' => 'Settings', 'actions' => self::PERMISSION_ACTIONS, 'legacy' => ['view' => 'settings.manage']],
    ];

    public function index(Request $request): View
    {
        $this->authorizeManagement($request);

        return view('staff.index', [
            'staff' => User::query()->latest()->paginate(10),
            'roles' => self::ROLES,
        ]);
    }

    public function editPermissions(User $staff): View
    {
        abort_if($staff->isProtectedSuperAdmin(), 403);

        return view('staff.permissions', [
            'staffMember' => $staff,
            'permissions' => self::PERMISSIONS,
            'permissionModules' => self::PERMISSION_MODULES,
            'permissionActions' => self::PERMISSION_ACTIONS,
        ]);
    }

    public function updatePermissions(Request $request, User $staff): RedirectResponse
    {
        abort_if($staff->isProtectedSuperAdmin(), 403);

        $matrixKeys = $this->permissionMatrixKeys();
        $data = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::in(array_values(array_unique([...array_keys(self::PERMISSIONS), ...$matrixKeys])))],
        ]);

        $legacyPermissions = array_diff($staff->permissions ?? [], $matrixKeys);
        $staff->update(['permissions' => array_values(array_unique([...$legacyPermissions, ...($data['permissions'] ?? [])]))]);

        return redirect()->route('user-management.index')->with('status', 'Permissions updated successfully.');
    }

    public function create(): View
    {
        $this->authorizeManagement(request());

        return view('staff.create', [
            'roles' => $this->assignableRoles(),
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);

        $data = $this->validated($request);
        $data['permissions'] = $request->input('permissions', []);

        User::create($data);

        return redirect()->route('staff.index')->with('status', 'Staff member created successfully.');
    }

    public function edit(User $staff): View
    {
        $this->authorizeManagement(request());

        abort_if($staff->isProtectedSuperAdmin() && ! request()->user()->is($staff), 403);

        return view('staff.edit', [
            'staffMember' => $staff,
            'roles' => $staff->isProtectedSuperAdmin() ? ['super_admin' => self::ROLES['super_admin']] : $this->assignableRoles(),
            'permissions' => self::PERMISSIONS,
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->authorizeManagement($request);

        abort_if($staff->isProtectedSuperAdmin() && ! $request->user()->is($staff), 403);

        $data = $this->validated($request, $staff);
        $data['permissions'] = $request->input('permissions', []);

        if ($staff->isProtectedSuperAdmin()) {
            if ($data['email'] !== $staff->email || $data['role'] !== 'super_admin' || $data['status'] !== 'active') {
                return back()->withErrors([
                    'role' => 'Protected Super Admin cannot be downgraded, deactivated, or transferred.',
                ])->withInput();
            }

            $data['permissions'] = array_keys(self::PERMISSIONS);
        }

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
            'role' => ['required', Rule::in(array_keys($staff?->isProtectedSuperAdmin() ? self::ROLES : $this->assignableRoles()))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(array_keys(self::PERMISSIONS))],
            'password' => [$staff ? 'nullable' : 'required', 'confirmed', 'min:8'],
        ]);
    }

    private function assignableRoles(): array
    {
        return array_diff_key(self::ROLES, ['super_admin' => true]);
    }

    private function permissionMatrixKeys(): array
    {
        $keys = [];
        foreach (self::PERMISSION_MODULES as $module => $details) {
            foreach (array_keys($details['actions']) as $action) {
                $keys[] = $details['legacy'][$action] ?? $module.'.'.$action;
            }
        }

        return $keys;
    }

    private function authorizeManagement(Request $request): void
    {
        abort_unless($request->user()->isSuperAdmin() || in_array($request->user()->role, ['admin', 'administrator'], true), 403);
    }
}
