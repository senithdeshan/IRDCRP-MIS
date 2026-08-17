@php
    $selectedPermissions = old('permissions', $staffMember->permissions ?? []);
@endphp

<div class="grid gap-5 lg:grid-cols-[1fr_0.8fr]">
    <div class="panel-surface p-6">
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="name" value="Full name" />
                <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $staffMember->name ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $staffMember->email ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="phone" value="Phone" />
                <x-text-input id="phone" name="phone" type="text" class="mt-2 block w-full" :value="old('phone', $staffMember->phone ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="designation" value="Designation" />
                <x-text-input id="designation" name="designation" type="text" class="mt-2 block w-full" :value="old('designation', $staffMember->designation ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('designation')" />
            </div>

            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $staffMember->role ?? 'staff') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('role')" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-2 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="active" @selected(old('status', $staffMember->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $staffMember->status ?? 'active') === 'inactive')>Inactive</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('status')" />
            </div>

            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" class="mt-2 block w-full" :required="$mode === 'create'" />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirm password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" :required="$mode === 'create'" />
            </div>
        </div>
    </div>

    <div class="panel-surface p-6">
        <h2 class="text-base font-semibold text-slate-950">Permissions</h2>
        <div class="mt-5 space-y-3">
            @foreach ($permissions as $key => $label)
                <label class="flex items-center justify-between gap-4 rounded-md border border-slate-200 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="{{ $key }}"
                        class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500"
                        @checked(in_array($key, $selectedPermissions, true))
                    >
                </label>
            @endforeach
        </div>
        <x-input-error class="mt-3" :messages="$errors->get('permissions')" />
    </div>
</div>

<div class="mt-5 flex items-center justify-end gap-3">
    <a href="{{ route('staff.index') }}" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
    <button type="submit" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
        {{ $mode === 'create' ? 'Create Staff' : 'Update Staff' }}
    </button>
</div>
