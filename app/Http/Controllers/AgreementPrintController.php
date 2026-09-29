<?php

namespace App\Http\Controllers;

use App\Models\ProductivePartnershipEoi;
use App\Models\YouthWomenApplicant;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AgreementPrintController extends Controller
{
    public function farmer(ProductivePartnershipEoi $eoi, string $stage = 'all')
    {
        abort_unless($eoi->verification_status === 'approved' && $eoi->full_proposal_status === 'approved', 403);
        return $this->report($eoi, $stage, 'Component 1.2 · Farmer Producer Group', [
            'Farmer organization' => $eoi->organization_name,
            'Registration number' => $eoi->registration_number,
            'Members' => $eoi->number_of_members,
            'Contact person' => $eoi->contact_person_name,
            'Telephone' => $eoi->contact_person_telephone,
            'Email' => $eoi->contact_person_email,
            'Business proposal' => $eoi->business_proposal_title,
            'Address' => $eoi->organization_registered_address,
        ]);
    }

    public function individual(YouthWomenApplicant $eoi, string $stage = 'all')
    {
        abort_unless(in_array($eoi->current_workflow_stage, ['agreement', 'completed'], true), 403);
        return $this->report($eoi, $stage, 'Component 1.3 · Individual Entrepreneur', [
            'Applicant' => $eoi->applicant_name,
            'NIC' => $eoi->nic,
            'Business' => $eoi->business_name,
            'Registration number' => $eoi->business_registration_number,
            'Telephone' => $eoi->telephone,
            'Email' => $eoi->email,
            'Address' => $eoi->business_registered_address,
            'Agreement number' => data_get($eoi->workflow_data, 'agreement.agreement_number'),
        ]);
    }

    private function report($eoi, string $stage, string $component, array $identity)
    {
        $labels = ['investment' => 'Total Investment', 'tr1' => 'TR1', 'tr2' => 'TR2', 'tr3' => 'TR3', 'revised' => 'Revised Investment'];
        abort_unless($stage === 'all' || isset($labels[$stage]), 404);
        $data = $eoi->agreement_eoi_data ?? [];
        abort_if(empty($data), 404, 'Save EOI agreement data before printing.');
        $identity += ['Province' => $eoi->province, 'District' => $eoi->district, 'DS Division' => $eoi->ds_division];
        $cents = fn ($key) => collect(['own', 'loan', 'grant'])->sum(fn ($source) => (int) round((float) data_get($data, "$key.$source", 0) * 100));
        $viewData = [
            'eoi' => $eoi, 'stage' => $stage, 'component' => $component,
            'identity' => $identity, 'data' => $data, 'labels' => $labels,
            'sections' => $stage === 'all' ? $labels : [$stage => $labels[$stage]],
            'cents' => $cents, 'budget' => $cents('investment'),
            'printedAt' => now(), 'printedBy' => auth()->user()->name,
        ];
        if (request()->query('format') === 'pdf') {
            $temporaryDirectory = storage_path('framework/cache/agreement-pdf');
            File::ensureDirectoryExists($temporaryDirectory);
            $options = new Options;
            $options->set('isRemoteEnabled', false);
            $options->set('isJavascriptEnabled', false);
            $options->set('isPhpEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('tempDir', $temporaryDirectory);
            $pdf = new Dompdf($options);
            $pdf->loadHtml(view('agreements.print', $viewData + ['pdf' => true])->render(), 'UTF-8');
            $pdf->setPaper('A4');
            $pdf->render();
            $filename = Str::slug($eoi->eoi_number).'-'.$stage.'-payment-report.pdf';
            return response($pdf->output())->withHeaders([
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'private, no-store',
            ]);
        }
        return response()->view('agreements.print', $viewData)->header('Cache-Control', 'private, no-store');
    }
}
