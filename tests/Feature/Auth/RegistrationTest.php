<?php

namespace Tests\Feature\Auth;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_starts_by_checking_the_official_email(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee(route('register.check-email'), false)
            ->assertDontSee('name="password"', false);
    }

    public function test_login_page_links_to_staff_registration(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('register'), false);
    }

    public function test_unapproved_users_cannot_register_directly(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Unapproved User',
            'email' => 'unapproved@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'unapproved@example.com']);
    }

    public function test_approved_staff_can_register_then_sign_in_with_their_password(): void
    {
        $profile = $this->profile();

        $this->post(route('register.check-email'), ['email' => ' STAFF@EXAMPLE.COM '])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('register'))
            ->assertSessionHas('staff_registration.profile_id', $profile->id)
            ->assertSessionHas('staff_registration.email', 'staff@example.com');

        $this->get('/register')
            ->assertOk()
            ->assertSee('name="password"', false);

        $this->post('/register', $this->credentials([
            'name' => 'Forged Name',
            'role' => 'super_admin',
            'permissions' => ['settings.manage', 'staff.manage'],
            'status' => 'inactive',
        ]))->assertSessionHasNoErrors()
            ->assertRedirect(route('login'))
            ->assertSessionMissing('staff_registration');

        $this->assertGuest();
        $user = User::where('email', 'staff@example.com')->firstOrFail();
        $this->assertSame('A. Staff Member', $user->name);
        $this->assertSame('staff', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertSame('MIS Officer', $user->designation);
        $this->assertSame('0771234567', $user->phone);
        $this->assertSame(['dashboard.view'], $user->permissions);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNull($user->email_verified_at);
        $this->assertSame($user->id, $profile->refresh()->user_id);
        $this->assertFalse($profile->registration_enabled);

        $this->post('/login', ['email' => 'staff@example.com', 'password' => 'password'])
            ->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_unknown_disabled_resigned_and_already_registered_emails_are_rejected(): void
    {
        $this->post(route('register.check-email'), ['email' => 'unknown@example.com'])
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('staff_registration');

        $profile = $this->profile(['registration_enabled' => false]);
        $this->post(route('register.check-email'), ['email' => $profile->official_email])
            ->assertSessionHasErrors('email');

        $profile->update(['registration_enabled' => true, 'employment_status' => 'Resigned']);
        $this->post(route('register.check-email'), ['email' => $profile->official_email])
            ->assertSessionHasErrors('email');

        $user = User::factory()->create(['email' => $profile->official_email]);
        $profile->update(['employment_status' => 'In service', 'user_id' => $user->id]);
        $this->post(route('register.check-email'), ['email' => $profile->official_email])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_existing_account_email_is_not_eligible_even_without_a_profile_link(): void
    {
        $profile = $this->profile();
        User::factory()->create(['email' => strtoupper($profile->official_email)]);

        $this->post(route('register.check-email'), ['email' => $profile->official_email])
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('staff_registration');
    }

    public function test_an_approved_profile_still_requires_the_email_check_step(): void
    {
        $this->profile();

        $this->post('/register', $this->credentials())->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_malformed_email_input_is_a_validation_error(): void
    {
        $this->postJson(route('register.check-email'), ['email' => ['staff@example.com']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/register', $this->credentials(['email' => ['staff@example.com']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_cannot_be_switched_after_the_check_step(): void
    {
        $this->profile();
        $this->profile(['official_email' => 'other@example.com', 'nic_number' => '199512345679']);
        $this->post(route('register.check-email'), ['email' => 'staff@example.com']);

        $this->post('/register', $this->credentials(['email' => 'other@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_revoked_approval_is_checked_again_when_creating_the_account(): void
    {
        $profile = $this->profile();
        $this->post(route('register.check-email'), ['email' => $profile->official_email]);
        $profile->update(['registration_enabled' => false]);

        $this->post('/register', $this->credentials())->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
        $this->assertNull($profile->refresh()->user_id);
    }

    public function test_staff_who_resign_after_the_email_check_cannot_create_an_account(): void
    {
        $profile = $this->profile();
        $this->post(route('register.check-email'), ['email' => $profile->official_email]);
        $profile->update(['employment_status' => 'Resigned']);

        $this->post('/register', $this->credentials())->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_failure_does_not_consume_approval(): void
    {
        $profile = $this->profile();
        $this->post(route('register.check-email'), ['email' => $profile->official_email]);

        $this->post('/register', $this->credentials(['password_confirmation' => 'different-password']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
        $this->assertTrue($profile->refresh()->registration_enabled);
        $this->assertNull($profile->user_id);
    }

    public function test_a_registration_approval_cannot_create_a_second_account(): void
    {
        $profile = $this->profile();
        $this->post(route('register.check-email'), ['email' => $profile->official_email]);
        $approval = session('staff_registration');
        $this->post('/register', $this->credentials())->assertSessionHasNoErrors();

        $this->withSession(['staff_registration' => $approval])
            ->post('/register', $this->credentials())->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertFalse($profile->refresh()->registration_enabled);
    }

    private function profile(array $attributes = []): StaffProfile
    {
        return StaffProfile::create(array_merge([
            'staff_type' => 'MIS Officer',
            'employment_status' => 'In service',
            'name_with_initials' => 'A. Staff Member',
            'nic_number' => '199512345678',
            'designation' => 'MIS Officer',
            'official_email' => 'staff@example.com',
            'contact_number' => '0771234567',
            'registration_enabled' => true,
        ], $attributes));
    }

    private function credentials(array $attributes = []): array
    {
        return array_merge([
            'email' => 'staff@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $attributes);
    }
}
