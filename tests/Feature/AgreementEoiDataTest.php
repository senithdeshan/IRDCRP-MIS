<?php

namespace Tests\Feature;

use App\Models\ProductivePartnershipEoi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgreementEoiDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_agreement_data_uploads_totals_and_updates(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $eoi = ProductivePartnershipEoi::create(['eoi_number' => 'EOI-TEST', 'verification_status' => 'approved', 'full_proposal_status' => 'approved']);
        $payload = ['agreement_sign_date' => '2026-09-15', 'investment' => ['own' => '100.10', 'loan' => '200.20', 'grant' => '300.30'], 'tr1' => ['date' => '2026-09-16', 'own' => '10.50']];
        $this->get(route('agreement-sign-fop.index'))->assertOk()->assertSee('Add EOI Data')->assertSee('Revised Investment');
        $this->get(route('selected-eois.show', $eoi))->assertOk()->assertSee('EOI Data View Details')->assertSee('No EOI agreement data saved yet');
        $this->patch(route('agreement-sign-fop.data', $eoi), $payload + [
            'full_proposal' => UploadedFile::fake()->create('proposal.pdf', 10, 'application/pdf'),
            'attachments' => [UploadedFile::fake()->create('support.pdf', 10, 'application/pdf')],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $data = $eoi->fresh()->agreement_eoi_data;
        $this->assertSame('600.60', $data['investment']['total']);
        $this->assertSame('10.50', $data['tr1']['total']);
        Storage::disk('local')->assertExists($data['full_proposal']['path']);
        $this->get(route('agreement-sign-fop.document', [$eoi, 'proposal']))->assertOk();
        $this->patch(route('agreement-sign-fop.data', $eoi), $payload)->assertSessionHasNoErrors();
        $this->assertSame($data['attachments'], $eoi->fresh()->agreement_eoi_data['attachments']);
        $this->get(route('agreement-sign-fop.index'))->assertOk()->assertSee('proposal.pdf');
        $this->get(route('selected-eois.show', $eoi))->assertOk()
            ->assertSee('EOI Agreement &amp; Expenditure Path', false)
            ->assertSee('2026-09-15')->assertSee('2026-09-16')->assertSee('600.60')
            ->assertSee('support.pdf')->assertSee('Not entered');
    }

    public function test_invalid_data_and_unapproved_records_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $eoi = ProductivePartnershipEoi::create(['eoi_number' => 'EOI-INVALID', 'verification_status' => 'approved', 'full_proposal_status' => 'approved']);
        $this->patch(route('agreement-sign-fop.data', $eoi), ['investment' => ['own' => -1], 'tr1' => ['own' => 10]])
            ->assertSessionHasErrors(['agreement_sign_date', 'investment.own', 'tr1.date'], null, 'agreementData');
        $this->assertNull($eoi->fresh()->agreement_eoi_data);
        $eoi->update(['full_proposal_status' => 'pending']);
        $this->patch(route('agreement-sign-fop.data', $eoi), [])->assertForbidden();
    }
}
