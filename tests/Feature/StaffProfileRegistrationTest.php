<?php

namespace Tests\Feature;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffProfileRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_profile_and_enable_its_registration(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->post(route('staff-profiles.store'), $this->profileData([
            'official_email' => ' STAFF@EXAMPLE.COM ',
            'registration_enabled' => '1',
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $profile = StaffProfile::where('official_email', 'staff@example.com')->firstOrFail();
        $this->assertSame($admin->id, $profile->created_by);
        $this->assertTrue($profile->registration_enabled);
        $this->assertNull($profile->user_id);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_new_profiles_do_not_enable_registration_by_default(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('staff-profiles.store'), $this->profileData())
            ->assertSessionHasNoErrors();

        $this->assertFalse(StaffProfile::firstOrFail()->registration_enabled);
    }

    public function test_staff_and_administrators_cannot_create_edit_delete_or_approve_profiles(): void
    {
        $profile = StaffProfile::create($this->profileData());

        foreach (['staff', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('staff-profiles.create'))->assertForbidden();
            $this->post(route('staff-profiles.store'), $this->profileData())->assertForbidden();
            $this->get(route('staff-profiles.edit', $profile))->assertForbidden();
            $this->put(route('staff-profiles.update', $profile), $this->profileData([
                'registration_enabled' => true,
            ]))->assertForbidden();
            $this->delete(route('staff-profiles.destroy', $profile))->assertForbidden();
        }

        $this->assertModelExists($profile);
        $this->assertFalse($profile->refresh()->registration_enabled);
    }

    public function test_signed_in_staff_can_view_profiles_without_management_actions(): void
    {
        $profile = StaffProfile::create($this->profileData());
        $this->actingAs(User::factory()->create(['role' => 'staff']));

        $this->get(route('staff-profiles.index'))->assertOk()
            ->assertDontSee(route('staff-profiles.create'), false);
        $this->get(route('staff-profiles.show', $profile))->assertOk()
            ->assertDontSee(route('staff-profiles.edit', $profile), false);
    }

    public function test_registration_approval_requires_an_official_email(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('staff-profiles.store'), $this->profileData([
                'official_email' => '',
                'registration_enabled' => true,
            ]))->assertSessionHasErrors('official_email');

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_official_emails_must_be_unique_without_regard_to_case(): void
    {
        StaffProfile::create($this->profileData(['official_email' => 'STAFF@EXAMPLE.COM']));

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('staff-profiles.store'), $this->profileData([
                'nic_number' => '199512345679',
                'official_email' => 'staff@example.com',
            ]))->assertSessionHasErrors('official_email');

        $this->assertDatabaseCount('staff_profiles', 1);
    }

    public function test_super_admin_can_enable_and_revoke_registration_on_an_existing_profile(): void
    {
        $profile = StaffProfile::create($this->profileData());
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->put(route('staff-profiles.update', $profile), $this->profileData([
            'registration_enabled' => '1',
        ]))->assertSessionHasNoErrors();
        $this->assertTrue($profile->refresh()->registration_enabled);

        $this->put(route('staff-profiles.update', $profile), $this->profileData())
            ->assertSessionHasNoErrors();
        $this->assertFalse($profile->refresh()->registration_enabled);
    }

    public function test_resigned_staff_cannot_be_approved_to_register(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('staff-profiles.store'), $this->profileData([
                'employment_status' => 'Resigned',
                'registration_enabled' => true,
            ]))->assertSessionHasErrors('registration_enabled');

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_registered_profile_cannot_be_approved_a_second_time(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $profile = StaffProfile::create(array_merge($this->profileData(), ['user_id' => $user->id]));

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->put(route('staff-profiles.update', $profile), $this->profileData([
                'registration_enabled' => true,
            ]))->assertSessionHasErrors('registration_enabled');

        $this->assertFalse($profile->refresh()->registration_enabled);
        $this->assertSame($user->id, $profile->user_id);
    }

    public function test_existing_account_email_cannot_receive_a_new_registration_approval(): void
    {
        User::factory()->create(['email' => 'STAFF@EXAMPLE.COM']);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('staff-profiles.store'), $this->profileData([
                'registration_enabled' => true,
            ]))->assertSessionHasErrors();

        $this->assertDatabaseCount('staff_profiles', 0);
    }

    public function test_registered_profile_email_cannot_be_changed_away_from_the_linked_account(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com']);
        $profile = StaffProfile::create(array_merge($this->profileData(), ['user_id' => $user->id]));

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->put(route('staff-profiles.update', $profile), $this->profileData([
                'official_email' => 'changed@example.com',
            ]))->assertSessionHasErrors('official_email');

        $this->assertSame('staff@example.com', $profile->refresh()->official_email);
    }

    private function profileData(array $attributes = []): array
    {
        return array_merge([
            'staff_type' => 'MIS Officer',
            'employment_status' => 'In service',
            'name_with_initials' => 'A. Staff Member',
            'nic_number' => '199512345678',
            'designation' => 'MIS Officer',
            'official_email' => 'staff@example.com',
        ], $attributes);
    }
}
