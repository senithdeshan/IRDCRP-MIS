@php
    $selectedPermissions = old('permissions', $staffMember->permissions ?? []);
    $isProtectedSuperAdmin = $mode === 'edit' && isset($staffMember) && $staffMember->isProtectedSuperAdmin();
@endphp

<div class="admin-access-shell">
    <section class="admin-form-panel">
        <div class="admin-form-header">
            <span>Staff account</span>
            <h2>Profile Details</h2>
        </div>

        <div class="admin-form-grid">
            <div class="admin-field admin-field-wide">
                <x-input-label for="name" value="Full name" class="admin-label" />
                <input id="name" name="name" type="text" class="admin-input" value="{{ old('name', $staffMember->name ?? '') }}" required autofocus>
                <x-input-error class="admin-error" :messages="$errors->get('name')" />
            </div>

            <div class="admin-field">
                <x-input-label for="email" value="Email" class="admin-label" />
                <input id="email" name="email" type="email" @class(['admin-input', 'admin-input-readonly' => $isProtectedSuperAdmin]) value="{{ old('email', $staffMember->email ?? '') }}" required @readonly($isProtectedSuperAdmin)>
                <x-input-error class="admin-error" :messages="$errors->get('email')" />
            </div>

            <div class="admin-field">
                <x-input-label for="phone" value="Phone" class="admin-label" />
                <input id="phone" name="phone" type="text" class="admin-input" value="{{ old('phone', $staffMember->phone ?? '') }}">
                <x-input-error class="admin-error" :messages="$errors->get('phone')" />
            </div>

            <div class="admin-field">
                <x-input-label for="designation" value="Designation" class="admin-label" />
                <input id="designation" name="designation" type="text" class="admin-input" value="{{ old('designation', $staffMember->designation ?? '') }}">
                <x-input-error class="admin-error" :messages="$errors->get('designation')" />
            </div>

            <div class="admin-field">
                <x-input-label for="role" value="Role" class="admin-label" />
                @if ($isProtectedSuperAdmin)
                    <input type="hidden" name="role" value="super_admin">
                    <div class="admin-role-lock admin-role-lock-amber">
                        <img src="{{ asset('images/logos/super-admin-badge.svg') }}" alt="" class="admin-role-lock-icon">
                        <span>Super Admin</span>
                    </div>
                @else
                    <select id="role" name="role" class="admin-select">
                        @foreach ($roles as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', $staffMember->role ?? 'staff') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
                <x-input-error class="admin-error" :messages="$errors->get('role')" />
            </div>

            <div class="admin-field">
                <x-input-label for="status" value="Status" class="admin-label" />
                @if ($isProtectedSuperAdmin)
                    <input type="hidden" name="status" value="active">
                    <div class="admin-role-lock admin-role-lock-green">
                        <span>Active and protected</span>
                    </div>
                @else
                    <select id="status" name="status" class="admin-select">
                        <option value="active" @selected(old('status', $staffMember->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $staffMember->status ?? 'active') === 'inactive')>Inactive</option>
                    </select>
                @endif
                <x-input-error class="admin-error" :messages="$errors->get('status')" />
            </div>

            <div class="admin-field">
                <x-input-label for="password" value="Password" class="admin-label" />
                <input id="password" name="password" type="password" class="admin-input" @required($mode === 'create')>
                <x-input-error class="admin-error" :messages="$errors->get('password')" />
            </div>

            <div class="admin-field">
                <x-input-label for="password_confirmation" value="Confirm password" class="admin-label" />
                <input id="password_confirmation" name="password_confirmation" type="password" class="admin-input" @required($mode === 'create')>
            </div>
        </div>
    </section>

    <section class="admin-form-panel admin-permissions-panel">
        <div class="admin-form-header">
            <span>Access control</span>
            <h2>Permissions</h2>
        </div>

        <div class="admin-permission-list">
            @foreach ($permissions as $key => $label)
                <label class="admin-permission-card">
                    <span class="admin-permission-text">{{ $label }}</span>
                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="{{ $key }}"
                        class="admin-checkbox"
                        @checked($isProtectedSuperAdmin || in_array($key, $selectedPermissions, true))
                        @disabled($isProtectedSuperAdmin)
                    >
                </label>
            @endforeach
        </div>
        <x-input-error class="admin-error" :messages="$errors->get('permissions')" />

        @if ($isProtectedSuperAdmin)
            <p class="admin-alert-lock">
                Protected developer account. Super Admin role, active status, and full permissions are locked.
            </p>
        @endif
    </section>
</div>

<div class="admin-form-actions">
    <a href="{{ route('staff.index') }}" class="admin-btn-secondary">Cancel</a>
    <button type="submit" class="admin-btn-primary">
        {{ $mode === 'create' ? 'Create Staff' : 'Update Staff' }}
    </button>
</div>
