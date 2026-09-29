<?php

namespace Tests\Feature;

use App\Models\ProductivePartnershipEoi;
use App\Models\YouthWomenApplicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgreementPrintTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['agreement_sign_date' => '2026-09-15',
            'investment' => ['own' => '100.10', 'loan' => '200.20', 'grant' => '300.30', 'total' => '999999'],
            'tr1' => ['date' => '2026-09-16', 'own' => '10.50', 'loan' => '20', 'grant' => '30'],
            'tr2' => ['date' => '2026-09-17', 'own' => '50'],
        ];
    }

    public function test_farmer_full_and_individual_stage_reports_use_saved_amounts(): void
    {
        $eoi = ProductivePartnershipEoi::create(['eoi_number' => 'EOI/PP/2026/1/11', 'organization_name' => 'Print Group', 'verification_status' => 'approved', 'full_proposal_status' => 'approved', 'agreement_eoi_data' => $this->data()]);
        $this->get(route('agreement-sign-fop.print', $eoi))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get(route('agreement-sign-fop.print', $eoi))->assertOk()
            ->assertSee('Print Group')->assertSee('600.60')->assertDontSee('999999')
            ->assertSee('TR3 — Approval details')->assertSee('Approved by')->assertSee('Actual payment / transfer date');
        $this->get(route('agreement-sign-fop.print', [$eoi, 'tr1']))->assertOk()
            ->assertSee('TR1 — Approval details')->assertSee('2026-09-16')->assertSee('60.50')
            ->assertDontSee('TR2 — Approval details')->assertDontSee('2026-09-17');
        $this->get(route('agreement-sign-fop.print', [$eoi, 'invalid']))->assertNotFound();
        foreach (['all', 'investment', 'tr1', 'tr2', 'tr3', 'revised'] as $stage) {
            $pdf = $this->get(route('agreement-sign-fop.print', [$eoi, $stage, 'format' => 'pdf']))
                ->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $pdf->getContent());
            $this->assertStringContainsString($stage.'-payment-report.pdf', $pdf->headers->get('Content-Disposition'));
        }
        $this->get(route('agreement-sign-fop.index'))->assertOk()->assertSee('Print TR1')->assertSee('Print Full EOI / Payment Report');
        $eoi->update(['verification_status' => 'pending']);
        $this->get(route('agreement-sign-fop.print', $eoi))->assertForbidden();
    }

    public function test_individual_printing_is_separate_and_requires_agreement_eligibility(): void
    {
        $eoi = YouthWomenApplicant::create(['eoi_number' => 'EOI/YW/2026/1', 'applicant_name' => 'Print Individual', 'gender' => 'Female', 'nic' => '200012345678', 'telephone' => '0712345678', 'business_name' => 'Sample Business', 'legal_status' => 'Proprietor', 'initial_screening_result' => 'Selected']);
        $eoi->workflow_stage = 'agreement';
        $eoi->agreement_eoi_data = $this->data();
        $eoi->save();
        $this->actingAs(User::factory()->create());
        foreach (['all', 'investment', 'tr1', 'tr2', 'tr3', 'revised'] as $stage) {
            $this->get(route('individual-agreement.print', [$eoi, $stage]))->assertOk()->assertSee('Print Individual')->assertSee('200012345678');
            $pdf = $this->get(route('individual-agreement.print', [$eoi, $stage, 'format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        }
        $this->get(route('individual-workflow.agreements'))->assertOk()->assertSee('Print TR1');
        $eoi->agreement_eoi_data = null;
        $eoi->save();
        $this->get(route('individual-agreement.print', $eoi))->assertNotFound();
        $eoi->workflow_stage = 'proposal';
        $eoi->save();
        $this->get(route('individual-agreement.print', $eoi))->assertForbidden();
    }
}
