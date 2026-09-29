<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProductivePartnershipEoi;
use App\Models\TankRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SingleEoiEntryTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'eoi_number' => 'EOI/PP/2026/1/131',
            'organization_name' => 'Green Valley', 'number_of_members' => 25,
            'legal_status' => 'Registered Society', 'contact_person_name' => 'Saman',
            'contact_person_designation' => 'Chairperson', 'contact_person_telephone' => '+94 71 234 5678',
            'contact_person_email' => 'saman@example.com', 'organization_registered_address' => 'Main Street',
            'proposed_business_location_address' => 'Colombo', 'province' => 'Western',
            'district' => 'Colombo', 'ds_division' => 'Colombo',
            'business_proposal_title' => 'Vegetable Processing', 'sector' => 'Agriculture',
            'proposed_total_investment' => '100000.50', 'expected_grant_irdcrp' => '50000.25',
        ];
    }

    public function test_manual_entry_saves_and_rejects_duplicate_numbers(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('selected-eois.store'), $this->payload())
            ->assertRedirect(route('selected-eois.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('productive_partnership_eois', [
            'eoi_number' => 'EOI/PP/2026/1/131', 'expected_grant_irdcrp' => 50000.25,
            'initial_stage' => false, 'imported_at' => null,
        ]);
        $this->post(route('selected-eois.store'), $this->payload())->assertSessionHasErrors('eoi_number');
        $this->assertDatabaseCount('productive_partnership_eois', 1);
    }

    public function test_year_and_call_are_derived_from_eoi_number_for_filters(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('selected-eois.create'))->assertOk()
            ->assertSee('placeholder="EOI/PP/2026/1/131"', false)
            ->assertDontSee('name="eoi_year"', false)
            ->assertDontSee('name="eoi_call_number"', false);

        $this->post(route('selected-eois.store'), array_merge($this->payload(), [
            'eoi_year' => 2020, 'eoi_call_number' => 99,
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('productive_partnership_eois', [
            'eoi_number' => 'EOI/PP/2026/1/131', 'eoi_year' => 2026, 'eoi_call_number' => 1,
        ]);
        $this->get(route('selected-eois.index', ['eoi_year' => 2026, 'eoi_call_number' => 1]))
            ->assertOk()->assertSee('EOI/PP/2026/1/131');
        $this->get(route('selected-eois.index', ['eoi_year' => 2026, 'eoi_call_number' => 2]))
            ->assertOk()->assertDontSee('EOI/PP/2026/1/131');
    }

    public function test_manual_eoi_number_requires_year_call_and_record_number(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['EOI/PP/2026/131', 'EOI/PP/2026/0/131', 'EOI/PP/2026/65536/131', 'EOI/PP/year/1/131', 'EOI/PP/2026/1/ABC'] as $number) {
            $this->post(route('selected-eois.store'), array_replace($this->payload(), ['eoi_number' => $number]))
                ->assertSessionHasErrors('eoi_number');
        }
        $this->assertDatabaseCount('productive_partnership_eois', 0);
    }

    public function test_invalid_fields_return_inline_errors_and_preserve_input(): void
    {
        $this->actingAs(User::factory()->create());
        $payload = array_replace($this->payload(), [
            'organization_name' => '', 'number_of_members' => -1,
            'contact_person_email' => 'invalid', 'contact_person_telephone' => 'letters',
            'district' => 'Matale', 'ds_division' => 'Dambulla', 'sector' => 'Invalid',
            'proposed_total_investment' => 'abc', 'expected_grant_irdcrp' => '-3',
            'registration_date' => '2026-02-30', 'notes' => str_repeat('x', 5001),
        ]);
        $this->from(route('selected-eois.create'))->post(route('selected-eois.store'), $payload)
            ->assertRedirect(route('selected-eois.create'))
            ->assertSessionHasErrors(['organization_name', 'number_of_members', 'contact_person_email',
                'contact_person_telephone', 'district', 'ds_division', 'sector',
                'proposed_total_investment', 'expected_grant_irdcrp', 'registration_date', 'notes']);
        $this->get(route('selected-eois.create'))->assertOk()
            ->assertSee('Choose a district belonging to the selected province.')
            ->assertSee('value="EOI/PP/2026/1/131"', false)
            ->assertSee('id="error_sector"', false);
        $this->assertDatabaseCount('productive_partnership_eois', 0);
    }

    public function test_required_fields_and_money_limits_are_enforced(): void
    {
        $this->post(route('selected-eois.store'), $this->payload())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->post(route('selected-eois.store'), [])->assertSessionHasErrors(array_keys($this->payload()));
        foreach (['100001', '0.001', '10000000000000'] as $grant) {
            $this->post(route('selected-eois.store'), array_replace($this->payload(), ['expected_grant_irdcrp' => $grant]))
                ->assertSessionHasErrors('expected_grant_irdcrp');
        }
        $this->post(route('selected-eois.store'), array_replace($this->payload(), ['province' => ['Western']]))
            ->assertSessionHasErrors('province');
        $this->assertDatabaseCount('productive_partnership_eois', 0);
    }

    public function test_all_optional_fields_save_and_location_details_survive_excel_round_trip(): void
    {
        $this->actingAs(User::factory()->create());
        $extra = [
            'as_centre' => 'Test ASC', 'gn_division' => 'Test GN',
            'place_of_registration' => 'Colombo', 'registration_number' => 'REG-25',
            'registration_date' => '2025-01-01',
            'completeness_mandatory_requirement' => 'Complete', 'initial_desk_review_status' => 'Pending',
            'initial_screening_date' => '2025-02-01', 'notes' => 'Manual record',
            'kobo_id' => '123', 'kobo_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'submission_time' => '2025-01-01T10:30', 'validation_status' => 'validated', 'status' => 'submitted',
        ];
        $this->post(route('selected-eois.store'), array_merge($this->payload(), $extra))
            ->assertSessionHasNoErrors()->assertRedirect(route('selected-eois.index'));
        $eoi = ProductivePartnershipEoi::firstOrFail();
        foreach ($extra as $field => $value) {
            if (!in_array($field, ['registration_date', 'initial_screening_date', 'submission_time'])) {
                $this->assertEquals($value, $eoi->$field);
            }
        }
        $this->assertSame('2025-01-01 10:30', $eoi->submission_time->format('Y-m-d H:i'));
        $this->get(route('selected-eois.show', $eoi))->assertOk()->assertSee('Test ASC')->assertSee('Test GN');
        $excel = $this->get(route('selected-eois.export'))->assertOk()->streamedContent();
        $eoi->update(['as_centre' => null, 'gn_division' => null]);
        $this->post(route('selected-eois.import'), [
            'eoi_file' => UploadedFile::fake()->createWithContent('roundtrip.xlsx', $excel),
        ])->assertSessionHasNoErrors();
        $this->assertSame('Test ASC', $eoi->fresh()->as_centre);
        $this->assertSame('Test GN', $eoi->fresh()->gn_division);
    }

    public function test_location_suggestions_use_existing_records_and_source_fields_are_validated(): void
    {
        TankRegistration::create([
            'tank_id' => 'TEST-1', 'tank_name' => 'Test Tank', 'province' => 'Western',
            'district' => 'Colombo', 'ds_division' => 'Colombo', 'as_centre' => 'Test ASC', 'gn_division' => 'Test GN',
        ]);
        $this->actingAs(User::factory()->create());
        $this->get(route('selected-eois.create'))->assertOk()->assertViewHas('locationRecords', function ($records) {
            return $records->contains(fn ($row) => $row->as_centre === 'Test ASC' && $row->gn_division === 'Test GN');
        });
        $this->post(route('selected-eois.store'), array_merge($this->payload(), [
            'as_centre' => str_repeat('a', 256), 'gn_division' => ['invalid'],
            'kobo_uuid' => 'invalid', 'submission_time' => 'not-a-date',
        ]))->assertSessionHasErrors(['as_centre', 'gn_division', 'kobo_uuid', 'submission_time']);
        $this->assertDatabaseCount('productive_partnership_eois', 0);
    }
}
