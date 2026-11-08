<?php

namespace Tests\Feature;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_registered_staff_cannot_change_their_official_email_from_account_settings(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $profile = $this->linkStaffProfile($user);

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => 'Changed Name',
            'email' => 'unapproved@example.com',
        ])->assertSessionHasErrors('email')
            ->assertRedirect('/profile');

        $this->assertSame('staff@example.com', $user->fresh()->email);
        $this->assertSame('staff@example.com', $profile->fresh()->official_email);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_registered_staff_can_update_their_name_while_keeping_their_official_email(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $profile = $this->linkStaffProfile($user);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Staff Name',
            'email' => 'staff@example.com',
        ])->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame('Updated Staff Name', $user->fresh()->name);
        $this->assertSame('staff@example.com', $user->fresh()->email);
        $this->assertSame($user->id, $profile->fresh()->user_id);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_malformed_email_in_account_settings_returns_a_validation_error(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);

        $this->actingAs($user)->patchJson('/profile', [
            'name' => 'Changed Name',
            'email' => ['changed@example.com'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertSame('staff@example.com', $user->fresh()->email);
    }

    public function test_account_settings_reject_case_only_duplicate_emails(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        User::factory()->create(['email' => 'Other.Staff@Example.COM']);

        $this->actingAs($user)->patchJson('/profile', [
            'name' => 'Changed Name',
            'email' => ' OTHER.staff@EXAMPLE.com ',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertSame('staff@example.com', $user->fresh()->email);
    }

    public function test_protected_super_admin_email_cannot_be_changed(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@irdcrp.lk',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Super Admin',
                'email' => 'other@example.com',
            ]);

        $response
            ->assertSessionHasErrors('email')
            ->assertRedirect('/profile');

        $this->assertSame('admin@irdcrp.lk', $user->refresh()->email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_protected_super_admin_cannot_delete_their_account(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@irdcrp.lk',
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
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
}
