<?php

namespace Tests\Feature;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_creates_staff_profiles_before_accounts(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this
            ->actingAs($user)
            ->get('/staff/create');

        $response->assertRedirect(route('staff-profiles.create'));
    }

    public function test_direct_account_creation_cannot_bypass_staff_registration(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this
            ->actingAs($user)
            ->from('/staff/create')
            ->post('/staff', [
                'name' => 'Second Super Admin',
                'email' => 'second-admin@example.com',
                'phone' => null,
                'designation' => 'Administrator',
                'role' => 'super_admin',
                'status' => 'active',
                'permissions' => [],
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'second-admin@example.com',
        ]);
    }

    public function test_other_staff_cannot_access_account_creation(): void
    {
        foreach (['staff', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/staff/create')->assertForbidden();
            $this->post('/staff', [])->assertForbidden();
        }
    }

    public function test_super_admin_cannot_change_an_account_email_away_from_its_staff_profile(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $profile = $this->linkStaffProfile($user);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->from(route('staff.edit', $user))
            ->patch(route('staff.update', $user), $this->accountUpdateData([
                'email' => 'unapproved@example.com',
            ]))->assertSessionHasErrors('email')
            ->assertRedirect(route('staff.edit', $user));

        $this->assertSame('staff@example.com', $user->fresh()->email);
        $this->assertSame('staff@example.com', $profile->fresh()->official_email);
        $this->assertSame($user->id, $profile->fresh()->user_id);
    }

    public function test_super_admin_can_update_a_registered_account_without_changing_its_official_email(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $profile = $this->linkStaffProfile($user);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->patch(route('staff.update', $user), $this->accountUpdateData())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('staff.index'));

        $this->assertSame('Updated Staff Name', $user->fresh()->name);
        $this->assertSame('officer', $user->fresh()->role);
        $this->assertSame('staff@example.com', $user->fresh()->email);
        $this->assertSame($user->id, $profile->fresh()->user_id);
    }

    public function test_malformed_email_in_account_management_returns_a_validation_error(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->patchJson(route('staff.update', $user), $this->accountUpdateData([
                'email' => ['changed@example.com'],
            ]))->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertSame('staff@example.com', $user->fresh()->email);
    }

    public function test_account_management_rejects_case_only_duplicate_emails(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        User::factory()->create(['email' => 'Other.Staff@Example.COM']);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->patchJson(route('staff.update', $user), $this->accountUpdateData([
                'email' => ' OTHER.staff@EXAMPLE.com ',
            ]))->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertSame('staff@example.com', $user->fresh()->email);
    }

    public function test_protected_super_admin_cannot_be_edited_by_another_user(): void
    {
        $user = User::factory()->create();
        $protectedSuperAdmin = User::factory()->create([
            'email' => 'admin@irdcrp.lk',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this
            ->actingAs($user)
            ->get(route('staff.edit', $protectedSuperAdmin))
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->patch(route('staff.update', $protectedSuperAdmin), [
                'name' => 'Changed Admin',
                'email' => 'changed@example.com',
                'phone' => null,
                'designation' => 'Changed',
                'role' => 'administrator',
                'status' => 'inactive',
                'permissions' => [],
                'password' => null,
                'password_confirmation' => null,
            ])
            ->assertForbidden();

        $protectedSuperAdmin->refresh();

        $this->assertSame('admin@irdcrp.lk', $protectedSuperAdmin->email);
        $this->assertSame('super_admin', $protectedSuperAdmin->role);
        $this->assertSame('active', $protectedSuperAdmin->status);
    }

    private function linkStaffProfile(User $user): StaffProfile
    {
        return StaffProfile::create([
            'staff_type' => 'MIS Officer',
            'employment_status' => 'In service',
            'name_with_initials' => $user->name,
            'nic_number' => '199512345678',
            'designation' => 'MIS Officer',
            'official_email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    private function accountUpdateData(array $attributes = []): array
    {
        return array_merge([
            'name' => 'Updated Staff Name',
            'email' => 'staff@example.com',
            'role' => 'officer',
            'status' => 'active',
            'permissions' => ['dashboard.view'],
        ], $attributes);
    }
}
