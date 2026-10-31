<x-app-layout>
    <section class="permission-editor">
        <div class="permission-editor-inner">
            <div class="permission-editor-navigation">
                <a href="{{ route('user-management.index') }}" class="permission-back-link">
                    <span aria-hidden="true">←</span> Back
                </a>
            </div>

            <header class="permission-editor-hero">
                <div class="permission-editor-title-row">
                    <span class="permission-editor-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                    </span>
                    <div>
                        <h1>Assign permissions</h1>
                        <p>Choose the actions this staff member can perform in each module.</p>
                    </div>
                </div>
                <div class="permission-editor-user">
                    <span class="permission-editor-user-name">{{ $staffMember->name }}</span>
                    <span>{{ $staffMember->email }}</span>
                </div>
            </header>

            @if ($errors->any())
                <div role="alert" class="permission-editor-error">Some permissions could not be saved. Review your selections and try again.</div>
            @endif

            <form method="POST" action="{{ route('staff.permissions.update', $staffMember) }}" class="permission-editor-form" data-permission-matrix>
                @csrf
                @method('PATCH')

                <div class="permission-editor-toolbar">
                    <div class="permission-editor-shortcuts">
                        <button type="button" data-select-all class="permission-select-all">Select all</button>
                        <button type="button" data-clear-all class="permission-clear-all">Clear all</button>
                    </div>
                    <p>Row selects actions for a module. Column selects one action for every module.</p>
                </div>

                <div class="permission-table-scroll">
                    <table class="permission-matrix-table">
                        <thead>
                            <tr>
                                <th class="permission-select-cell">
                                    <input type="checkbox" aria-label="Select every permission" data-master-toggle>
                                </th>
                                <th class="permission-module-heading">Module</th>
                                @foreach ($permissionActions as $action => $actionLabel)
                                    <th class="permission-action-heading">
                                        <input type="checkbox" aria-label="Select {{ $actionLabel }} for every module" data-column-toggle="{{ $action }}">
                                        <span>{{ $actionLabel }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php($selectedPermissions = old('permissions', $staffMember->permissions ?? []))
                            @foreach ($permissionModules as $module => $details)
                                <tr>
                                    <td class="permission-select-cell">
                                        <input type="checkbox" aria-label="Select all permissions for {{ $details['label'] }}" data-row-toggle="{{ $module }}">
                                    </td>
                                    <th scope="row" class="permission-module-name">{{ $details['label'] }}</th>
                                    @foreach ($permissionActions as $action => $actionLabel)
                                        @php($permissionKey = $details['legacy'][$action] ?? $module.'.'.$action)
                                        <td class="permission-action-cell">
                                            <input type="checkbox" name="permissions[]" value="{{ $permissionKey }}" data-permission data-module="{{ $module }}" data-action="{{ $action }}" aria-label="{{ $actionLabel }}: {{ $details['label'] }}" @checked(in_array($permissionKey, $selectedPermissions, true))>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="permission-editor-footer">
                    <a href="{{ route('user-management.index') }}" class="permission-cancel">Cancel</a>
                    <button type="submit" class="permission-save">Save Permissions</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        (() => {
            const form = document.querySelector('[data-permission-matrix]');
            if (!form) return;

            const permissions = [...form.querySelectorAll('[data-permission]')];
            const rowToggles = [...form.querySelectorAll('[data-row-toggle]')];
            const columnToggles = [...form.querySelectorAll('[data-column-toggle]')];
            const masterToggle = form.querySelector('[data-master-toggle]');

            const syncShortcuts = () => {
                rowToggles.forEach((toggle) => {
                    const group = permissions.filter((input) => input.dataset.module === toggle.dataset.rowToggle);
                    const checked = group.filter((input) => input.checked).length;
                    toggle.checked = checked === group.length;
                    toggle.indeterminate = checked > 0 && checked < group.length;
                });
                columnToggles.forEach((toggle) => {
                    const group = permissions.filter((input) => input.dataset.action === toggle.dataset.columnToggle);
                    const checked = group.filter((input) => input.checked).length;
                    toggle.checked = checked === group.length;
                    toggle.indeterminate = checked > 0 && checked < group.length;
                });
                const checked = permissions.filter((input) => input.checked).length;
                masterToggle.checked = checked === permissions.length;
                masterToggle.indeterminate = checked > 0 && checked < permissions.length;
            };

            rowToggles.forEach((toggle) => toggle.addEventListener('change', () => {
                permissions.filter((input) => input.dataset.module === toggle.dataset.rowToggle).forEach((input) => input.checked = toggle.checked);
                syncShortcuts();
            }));
            columnToggles.forEach((toggle) => toggle.addEventListener('change', () => {
                permissions.filter((input) => input.dataset.action === toggle.dataset.columnToggle).forEach((input) => input.checked = toggle.checked);
                syncShortcuts();
            }));
            permissions.forEach((input) => input.addEventListener('change', syncShortcuts));
            masterToggle.addEventListener('change', () => {
                permissions.forEach((input) => input.checked = masterToggle.checked);
                syncShortcuts();
            });
            form.querySelector('[data-select-all]').addEventListener('click', () => {
                permissions.forEach((input) => input.checked = true);
                syncShortcuts();
            });
            form.querySelector('[data-clear-all]').addEventListener('click', () => {
                permissions.forEach((input) => input.checked = false);
                syncShortcuts();
            });
            syncShortcuts();
        })();
    </script>
</x-app-layout>
