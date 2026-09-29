<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\YouthWomenApplicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function applicant(string $screening = 'Selected'): YouthWomenApplicant
    {
        return YouthWomenApplicant::create([
            'eoi_number' => 'EOI/YW/2026/001', 'applicant_name' => 'Test Individual',
            'gender' => 'Female', 'nic' => '200012345678', 'telephone' => '0712345678',
            'business_name' => 'Test Business', 'legal_status' => 'Proprietor',
            'initial_screening_result' => $screening,
        ]);
    }

    public function test_individual_moves_through_every_stage_and_signs_once(): void
    {
        $this->actingAs(User::factory()->create());
        $applicant = $this->applicant();
        $url = route('individual-workflow.advance', $applicant);
        $this->get(route('individual-workflow.reviewed-interviews'))->assertSee('Test Individual');
        foreach ([
            [['action' => 'interview', 'marks' => 75, 'interview_status' => 'likely_mature'], 'field-visits', 'verification'],
            [['action' => 'verification', 'visit_date' => '2026-01-01', 'result' => 'approved'], 'approved', 'approved'],
            [['action' => 'approved'], 'full-proposals', 'proposal'],
            [['action' => 'proposal', 'result' => 'approved'], 'agreements', 'agreement'],
            [['action' => 'agreement', 'agreement_number' => 'AGR-001', 'signed_date' => '2026-01-02'], 'agreements', 'completed'],
        ] as [$data, $page, $stage]) {
            $this->patch($url, $data)->assertSessionHasNoErrors()->assertRedirect(route('individual-workflow.'.$page));
            $this->assertSame($stage, $applicant->fresh()->workflow_stage);
            $this->get(route('individual-workflow.'.$page))->assertOk()->assertSee('Test Individual');
        }
        $this->get(route('individual-workflow.reviewed-interviews'))->assertDontSee('Test Individual');
        $this->get(route('individual-workflow.agreements'))->assertSee('AGR-001')->assertDontSee('Record Signed Agreement');
        $this->assertCount(5, $applicant->fresh()->workflow_history);
        $this->patch($url, ['action' => 'agreement', 'agreement_number' => 'DUPLICATE', 'signed_date' => '2026-01-02'])->assertSessionHasErrors('action');
        $this->assertCount(5, $applicant->fresh()->workflow_history);
        $this->assertDatabaseCount('productive_partnership_eois', 0);
    }

    public function test_failures_stay_in_stage_and_skipping_is_blocked(): void
    {
        $this->actingAs(User::factory()->create());
        $applicant = $this->applicant();
        $url = route('individual-workflow.advance', $applicant);
        $this->patch($url, ['action' => 'approved'])->assertSessionHasErrors('action');
        $this->patch($url, ['action' => 'interview', 'marks' => 101, 'interview_status' => 'invalid'])->assertSessionHasErrors(['marks', 'interview_status']);
        $this->patch($url, ['action' => 'interview', 'marks' => 50, 'interview_status' => 'resubmit'])->assertRedirect(route('individual-workflow.reviewed-interviews'));
        $this->assertSame('interview', $applicant->fresh()->workflow_stage);
        $this->patch($url, ['action' => 'interview', 'marks' => 51, 'interview_status' => 'likely_mature'])->assertRedirect(route('individual-workflow.field-visits'));
        $this->patch($url, ['action' => 'verification', 'result' => 'not_approved', 'visit_date' => '2026-01-01'])->assertRedirect(route('individual-workflow.field-visits'));
        $this->assertSame('verification', $applicant->fresh()->workflow_stage);
        $this->get(route('individual-workflow.approved'))->assertDontSee('Test Individual');
    }

    public function test_screening_revocation_resets_workflow_and_retains_history(): void
    {
        $user = User::factory()->create();
        $applicant = $this->applicant('Reject');
        $url = route('individual-workflow.advance', $applicant);
        $this->patch($url, ['action' => 'approved'])->assertRedirect(route('login'));
        $this->actingAs($user);
        $this->get(route('individual-workflow.reviewed-interviews'))->assertDontSee('Test Individual');
        $this->patch($url, ['action' => 'interview', 'marks' => 80, 'interview_status' => 'likely_mature'])->assertSessionHasErrors('action');
        $applicant->update(['initial_screening_result' => 'Selected']);
        $this->patch($url, ['action' => 'interview', 'marks' => 80, 'interview_status' => 'likely_mature'])->assertSessionHasNoErrors();
        $applicant->refresh()->update(['initial_screening_result' => 'Reject']);
        $this->assertNull($applicant->fresh()->workflow_stage);
        $this->assertNull($applicant->fresh()->workflow_data);
        $this->assertCount(2, $applicant->fresh()->workflow_history);
        $applicant->update(['initial_screening_result' => 'Selected']);
        $this->assertSame('interview', $applicant->fresh()->current_workflow_stage);
    }
}
