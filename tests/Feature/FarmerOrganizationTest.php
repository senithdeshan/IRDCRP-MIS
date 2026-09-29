<?php

namespace Tests\Feature;

use App\Models\ProductivePartnershipEoi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FarmerOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $changes = []): array
    {
        return array_replace([
            'eoi_number' => 'EOI/PP/2026/1/131', 'organization_name' => 'Green Valley Group',
            'number_of_members' => 42, 'legal_status' => 'Registered Society',
            'contact_person_name' => 'Saman', 'contact_person_designation' => 'Chairperson',
            'contact_person_telephone' => '0712345678', 'contact_person_email' => 'group@example.com',
            'organization_registered_address' => 'Main Road', 'proposed_business_location_address' => 'Dambulla',
            'province' => 'Central', 'district' => 'Matale', 'ds_division' => 'Dambulla',
            'business_proposal_title' => 'Vegetable Processing', 'sector' => 'Agriculture',
            'proposed_total_investment' => 1000000, 'expected_grant_irdcrp' => 500000,
            'screening_result' => 'Pending',
        ], $changes);
    }

    public function test_farmer_screens_require_authentication_and_render_for_staff(): void
    {
        foreach (['productive-partnership.index', 'farmer-organizations.index', 'farmer-organizations.records', 'farmer-organizations.create'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->actingAs(User::factory()->create());
        foreach (['productive-partnership.index', 'farmer-organizations.index', 'farmer-organizations.records', 'farmer-organizations.create'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Add Farmer Group');
        }
        $this->get(route('farmer-organizations.create'))->assertSee('Number of Members')->assertDontSee('name="nic"', false);
    }

    public function test_groups_share_the_eoi_registry_and_selected_groups_enter_interview_review(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('farmer-organizations.store'), $this->payload(['screening_result' => 'Selected']))
            ->assertSessionHasNoErrors()->assertRedirect(route('farmer-organizations.records'));
        $eoi = ProductivePartnershipEoi::firstOrFail();
        $this->assertTrue($eoi->initial_stage);
        $this->assertSame(42, $eoi->number_of_members);
        $this->assertSame(1, $eoi->eoi_call_number);
        $this->assertDatabaseCount('youth_women_applicants', 0);
        $this->get(route('selected-eois.index'))->assertSee('Green Valley Group');
        $this->get(route('reviewed-interviews.index'))->assertSee('Green Valley Group');
        $this->get(route('farmer-organizations.records', ['initial_screening_result' => 'Selected']))->assertSee('Green Valley Group');
        $this->get(route('farmer-organizations.records', ['initial_screening_result' => 'Reject']))->assertDontSee('Green Valley Group');
        $this->get(route('farmer-organizations.index'))->assertViewHas('summary', fn ($summary) => $summary['total'] === 1 && $summary['selected'] === 1);
    }

    public function test_edit_restores_group_fields_and_changes_screening_on_the_same_record(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('farmer-organizations.store'), $this->payload(['screening_result' => 'Selected']))->assertSessionHasNoErrors();
        $eoi = ProductivePartnershipEoi::firstOrFail();
        $this->get(route('farmer-organizations.edit', $eoi))->assertOk()
            ->assertSee('value="Green Valley Group"', false)->assertSee('value="42"', false);
        $this->patch(route('farmer-organizations.update', $eoi), $this->payload(['organization_name' => 'Updated Group', 'screening_result' => 'Reject']))
            ->assertSessionHasNoErrors()->assertRedirect(route('farmer-organizations.records'));
        $this->assertDatabaseCount('productive_partnership_eois', 1);
        $this->assertFalse($eoi->fresh()->initial_stage);
        $this->get(route('reviewed-interviews.index'))->assertDontSee('Updated Group');
        $this->get(route('farmer-organizations.records', ['initial_screening_result' => 'Reject']))->assertSee('Updated Group');
    }

    public function test_validation_and_group_filters_work_without_changing_youth_records(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('farmer-organizations.store'), $this->payload(['district' => 'Colombo', 'number_of_members' => -1]))
            ->assertSessionHasErrors(['district', 'number_of_members']);
        $this->post(route('farmer-organizations.store'), $this->payload())->assertSessionHasNoErrors();
        $this->post(route('farmer-organizations.store'), $this->payload())->assertSessionHasErrors('eoi_number');
        $this->get(route('farmer-organizations.records', ['search' => 'Green', 'province' => 'Central', 'district' => 'Matale']))->assertSee('Green Valley Group');
        $this->get(route('farmer-organizations.records', ['province' => 'Western']))->assertDontSee('Green Valley Group');
    }

    public function test_csv_import_updates_groups_and_screening_without_duplicates(): void
    {
        $this->actingAs(User::factory()->create());
        $headers = 'EOI Number,Name of the Organization,Number of Members,Status after Initial Desk Review';
        $csv = $headers."\nEOI/PP/2026/1/131,Imported Group,25,Selected\n";
        $this->post(route('farmer-organizations.import'), ['eoi_file' => UploadedFile::fake()->createWithContent('groups.csv', $csv)])
            ->assertSessionHasNoErrors()->assertRedirect(route('farmer-organizations.records'));
        $this->get(route('reviewed-interviews.index'))->assertSee('Imported Group');
        $csv = $headers."\nEOI/PP/2026/1/131,Updated Import,26,Reject\n";
        $this->post(route('farmer-organizations.import'), ['eoi_file' => UploadedFile::fake()->createWithContent('groups.csv', $csv)])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('productive_partnership_eois', 1);
        $this->assertDatabaseHas('productive_partnership_eois', ['organization_name' => 'Updated Import', 'number_of_members' => 26, 'initial_stage' => false]);
        $this->get(route('farmer-organizations.template'))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_existing_legacy_eoi_number_can_be_edited_without_renumbering(): void
    {
        $this->actingAs(User::factory()->create());
        $eoi = ProductivePartnershipEoi::create(['eoi_number' => 'EOI/PP/2026/0131']);
        $this->patch(route('farmer-organizations.update', $eoi), $this->payload(['eoi_number' => $eoi->eoi_number]))
            ->assertSessionHasNoErrors()->assertRedirect(route('farmer-organizations.records'));
        $this->assertSame('EOI/PP/2026/0131', $eoi->fresh()->eoi_number);
    }
}
