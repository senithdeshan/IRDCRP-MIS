<?php

namespace App\Http\Controllers;

use App\Models\YouthWomenApplicant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class IndividualAgreementController extends Controller
{
    public function updateAgreementEoiData(Request $request, YouthWomenApplicant $eoi): RedirectResponse
    {
        abort_unless(in_array($eoi->current_workflow_stage, ['agreement', 'completed'], true), 403);
        $rules = [
            'agreement_sign_date' => ['required', 'date_format:Y-m-d'],
            'full_proposal' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ];
        foreach (['investment', 'tr1', 'tr2', 'tr3', 'revised'] as $stage) {
            if ($stage !== 'investment') {
                $rules["{$stage}.date"] = ['nullable', 'date_format:Y-m-d', "required_with:{$stage}.own,{$stage}.loan,{$stage}.grant"];
            }
            foreach (['own', 'loan', 'grant'] as $source) {
                $rules["{$stage}.{$source}"] = [$stage === 'investment' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:999999999999.99', 'decimal:0,2'];
            }
        }
        $rules['agreement_sign_date'][] = 'before_or_equal:today';
        if ($eoi->current_workflow_stage === 'completed' && filled(data_get($eoi->workflow_data, 'agreement.signed_date'))) {
            $rules['agreement_sign_date'][] = \Illuminate\Validation\Rule::in([data_get($eoi->workflow_data, 'agreement.signed_date')]);
        }
        $validated = $request->validateWithBag('agreementData', $rules);
        $data = $eoi->agreement_eoi_data ?? [];
        $data['agreement_sign_date'] = $validated['agreement_sign_date'];
        foreach (['investment', 'tr1', 'tr2', 'tr3', 'revised'] as $stage) {
            $data[$stage] = $validated[$stage] ?? [];
            $cents = 0;
            foreach (['own', 'loan', 'grant'] as $source) {
                $cents += (int) round((float) ($data[$stage][$source] ?? 0) * 100);
            }
            $data[$stage]['total'] = number_format($cents / 100, 2, '.', '');
        }
        $stored = [];
        try {
            if ($request->hasFile('full_proposal')) {
                $file = $request->file('full_proposal');
                $stored[] = $path = $file->store("individual-agreement-eois/{$eoi->id}", 'local');
                $data['full_proposal'] = ['path' => $path, 'name' => $file->getClientOriginalName()];
            }
            foreach ($request->file('attachments', []) as $file) {
                $stored[] = $path = $file->store("individual-agreement-eois/{$eoi->id}", 'local');
                $data['attachments'][] = ['path' => $path, 'name' => $file->getClientOriginalName()];
            }
            $eoi->agreement_eoi_data = $data; $eoi->save();
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($stored);
            throw $exception;
        }

        return back()->with('status', 'EOI agreement data saved successfully.');
    }

    public function downloadAgreementDocument(YouthWomenApplicant $eoi, string $document)
    {
        abort_unless(in_array($eoi->current_workflow_stage, ['agreement', 'completed'], true), 403);
        $data = $eoi->agreement_eoi_data ?? [];
        $file = $document === 'proposal' ? ($data['full_proposal'] ?? null) : ($data['attachments'][$document] ?? null);
        abort_unless($file && Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name']);
    }

}
