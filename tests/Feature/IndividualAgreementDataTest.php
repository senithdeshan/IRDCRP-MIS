<?php

namespace Tests\Feature;

use App\Models\YouthWomenApplicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IndividualAgreementDataTest extends TestCase
{
    use RefreshDatabase;

    private function applicant(): YouthWomenApplicant
    {
        $applicant = YouthWomenApplicant::create([
            'eoi_number' => 'EOI/YW/2026/1', 'applicant_name' => 'Individual Test',
            'gender' => 'Female', 'nic' => '200012345678', 'telephone' => '0712345678',
            'business_name' => 'Test Business', 'legal_status' => 'Proprietor',
            'initial_screening_result' => 'Selected',
        ]);
        $applicant->workflow_stage = 'agreement';
        $applicant->save();
        return $applicant;
    }

    public function test_agreement_data_uploads_totals_and_updates(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $eoi = $this->applicant();
        $payload = ['agreement_sign_date' => '2026-09-15', 'investment' => ['own' => '100.10', 'loan' => '200.20', 'grant' => '300.30'], 'tr1' => ['date' => '2026-09-16', 'own' => '10.50']];
        $this->get(route('individual-workflow.agreements'))->assertOk()->assertSee('Add EOI Data')->assertSee('Revised Investment');
        $this->get(route('individual-workflow.agreements'))->assertOk()->assertSee('EOI Data View Details')->assertSee('No EOI agreement data saved yet');
        $this->patch(route('individual-agreement.data', $eoi), $payload + [
            'full_proposal' => UploadedFile::fake()->create('proposal.pdf', 10, 'application/pdf'),
            'attachments' => [UploadedFile::fake()->create('support.pdf', 10, 'application/pdf')],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $data = $eoi->fresh()->agreement_eoi_data;
        $this->assertSame('600.60', $data['investment']['total']);
        $this->assertSame('10.50', $data['tr1']['total']);
        Storage::disk('local')->assertExists($data['full_proposal']['path']);
        $this->get(route('individual-agreement.document', [$eoi, 'proposal']))->assertOk();
        $this->patch(route('individual-agreement.data', $eoi), $payload)->assertSessionHasNoErrors();
        $this->assertSame($data['attachments'], $eoi->fresh()->agreement_eoi_data['attachments']);
        $this->get(route('individual-workflow.agreements'))->assertOk()->assertSee('proposal.pdf');
        $this->get(route('individual-workflow.agreements'))->assertOk()
            ->assertSee('EOI Agreement &amp; Expenditure Path', false)
            ->assertSee('2026-09-15')->assertSee('2026-09-16')->assertSee('600.60')
            ->assertSee('support.pdf')->assertSee('Not entered');
    }

    public function test_invalid_data_and_unapproved_records_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $eoi = $this->applicant();
        $this->patch(route('individual-agreement.data', $eoi), ['investment' => ['own' => -1], 'tr1' => ['own' => 10]])
            ->assertSessionHasErrors(['agreement_sign_date', 'investment.own', 'tr1.date'], null, 'agreementData');
        $this->assertNull($eoi->fresh()->agreement_eoi_data);
        $eoi->workflow_stage = 'proposal'; $eoi->save();
        $this->patch(route('individual-agreement.data', $eoi), [])->assertForbidden();
    }

    public function test_signed_agreement_dates_stay_consistent_and_uploads_are_private(): void
    {
        Storage::fake('local');
        $eoi = $this->applicant();
        $this->get(route('individual-agreement.document', [$eoi, 'proposal']))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $payload = ['agreement_sign_date' => '2026-09-15', 'investment' => ['own' => 100, 'loan' => 0, 'grant' => 200]];
        $this->patch(route('individual-agreement.data', $eoi), $payload)->assertSessionHasNoErrors();
        $this->patch(route('individual-workflow.advance', $eoi), ['action' => 'agreement', 'agreement_number' => 'AGR-01', 'signed_date' => '2026-09-16'])->assertSessionHasErrors('signed_date');
        $this->patch(route('individual-workflow.advance', $eoi), ['action' => 'agreement', 'agreement_number' => 'AGR-01', 'signed_date' => '2026-09-15'])->assertSessionHasNoErrors();
        $this->patch(route('individual-agreement.data', $eoi), array_replace($payload, ['agreement_sign_date' => '2026-09-16']))->assertSessionHasErrors('agreement_sign_date', null, 'agreementData');
        $this->patch(route('individual-agreement.data', $eoi), $payload + ['tr1' => ['date' => '2026-09-16', 'own' => 10]])->assertSessionHasNoErrors();
        $this->assertSame('10.00', $eoi->fresh()->agreement_eoi_data['tr1']['total']);
        $this->assertSame('completed', $eoi->fresh()->workflow_stage);
    }
}
