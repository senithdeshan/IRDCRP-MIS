<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_role_is_not_available_on_staff_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/staff/create');

        $response
            ->assertOk()
            ->assertDontSee('value="super_admin"', false);
    }

    public function test_super_admin_role_cannot_be_assigned_to_new_staff(): void
    {
        $user = User::factory()->create();

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

        $response
            ->assertSessionHasErrors('role')
            ->assertRedirect('/staff/create');

        $this->assertDatabaseMissing('users', [
            'email' => 'second-admin@example.com',
        ]);
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
}
