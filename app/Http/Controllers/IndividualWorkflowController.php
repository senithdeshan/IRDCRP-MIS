<?php

namespace App\Http\Controllers;

use App\Models\ProductivePartnershipEoi;
use App\Models\YouthWomenApplicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IndividualWorkflowController extends Controller
{
    public const STAGES = [
        'reviewed-interviews' => ['interview', 'Selected for Reviewed Interview'],
        'field-visits' => ['verification', 'Selected for Verification Field Visit Pass'],
        'approved' => ['approved', 'Approved Individual Entrepreneurs'],
        'full-proposals' => ['proposal', 'Selected for Full Proposal Preparation'],
        'agreements' => ['agreement', 'Agreement Sign Individual Enterpreneur Information'],
    ];

    public function index(Request $request)
    {
        $page = $request->route('stage');
        [$stage, $title] = self::STAGES[$page];
        $query = YouthWomenApplicant::where('initial_screening_result', 'Selected');
        if ($stage === 'interview') {
            $query->where(fn ($q) => $q->whereNull('workflow_stage')->orWhere('workflow_stage', 'interview'));
        } elseif ($stage === 'agreement') {
            $query->whereIn('workflow_stage', ['agreement', 'completed']);
        } else {
            $query->where('workflow_stage', $stage);
        }
        $total = (clone $query)->count();
        // Keep summaries independent of search and pagination, including earlier reviews
        // for applicants who have already advanced to the next step.
        $records = YouthWomenApplicant::where('initial_screening_result', 'Selected')->get();
        $order = ['interview', 'verification', 'approved', 'proposal', 'agreement', 'completed'];
        $reviewed = $records->filter(fn ($record) => array_search($record->current_workflow_stage, $order) >= array_search($stage, $order));
        $statusCounts = $reviewed->countBy(fn ($record) => $record->workflowStatus($stage))->all();
        $statusOptions = match ($stage) {
            'interview' => ['pending' => 'Pending', 'passed' => 'Field Visit Pass', 'not_passed' => 'Not Passed'],
            'verification' => ['pending' => 'Pending', 'approved' => 'Approved', 'not_approved' => 'Not Approved'],
            'proposal' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'],
            'agreement' => ['pending' => 'Ready for Agreement', 'signed' => 'Agreement Signed'],
            default => ['pending' => 'Ready for Full Proposal', 'advanced' => 'Moved to Full Proposal'],
        };
        $summaryCards = ['Total' => $reviewed->count()];
        foreach ($statusOptions as $key => $label) {
            $summaryCards[$label] = $statusCounts[$key] ?? 0;
        }
        $interviewStatusCounts = $reviewed->countBy(fn ($record) => data_get($record->workflow_data, 'interview.interview_status', 'pending'));
        $paymentSummary = [];
        foreach (['investment', 'tr1', 'tr2', 'tr3', 'revised'] as $key) {
            $paymentSummary[$key] = $reviewed->sum(fn ($record) => collect(['own', 'loan', 'grant'])->sum(fn ($source) => (int) round((float) data_get($record->agreement_eoi_data, "$key.$source", 0) * 100))) / 100;
        }
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(fn ($q) => $q->where('eoi_number', 'like', "%{$search}%")->orWhere('applicant_name', 'like', "%{$search}%")->orWhere('business_name', 'like', "%{$search}%"));
        }
        if ($status = $request->string('status')->toString()) {
            $ids = (clone $query)->get()->filter(fn ($record) => $record->workflowStatus($stage) === $status)->modelKeys();
            $query->whereIn('id', $ids);
        }
        return view('youth-women.workflow', [
            'summaryCards' => $summaryCards, 'statusOptions' => $statusOptions,
            'interviewStatusCounts' => $interviewStatusCounts, 'paymentSummary' => $paymentSummary,
            'applicants' => $query->latest('updated_at')->paginate(15)->withQueryString(),
            'stage' => $stage, 'title' => $title, 'total' => $total,
            'stages' => self::STAGES, 'interviewStatuses' => ProductivePartnershipEoi::INTERVIEW_STATUSES,
        ]);
    }

    public function advance(Request $request, YouthWomenApplicant $applicant)
    {
        $action = $request->validate(['action' => ['required', Rule::in(['interview', 'verification', 'approved', 'proposal', 'agreement'])]])['action'];
        $rules = match ($action) {
            'interview' => ['marks' => ['required', 'numeric', 'between:0,100'], 'interview_status' => ['required', Rule::in(array_keys(ProductivePartnershipEoi::INTERVIEW_STATUSES))]],
            'verification' => ['result' => ['required', 'in:approved,not_approved,pending'], 'visit_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']],
            'approved' => [],
            'proposal' => ['result' => ['required', 'in:approved,rejected,pending']],
            'agreement' => ['agreement_number' => ['required', 'string', 'max:255'], 'signed_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']],
        };
        $data = $request->validate($rules + ['notes' => ['nullable', 'string', 'max:5000']]);
        $next = DB::transaction(function () use ($request, $applicant, $action, $data) {
            $record = YouthWomenApplicant::lockForUpdate()->findOrFail($applicant->id);
            if ($record->current_workflow_stage !== $action) {
                throw ValidationException::withMessages(['action' => 'This applicant is not eligible for this step, or has already moved forward. Refresh the page.']);
            }
            if ($action === 'agreement' && filled(data_get($record->agreement_eoi_data, 'agreement_sign_date'))
                && $data['signed_date'] !== $record->agreement_eoi_data['agreement_sign_date']) {
                throw ValidationException::withMessages(['signed_date' => 'The signed date must match the agreement sign date saved in Add EOI Data.']);
            }
            $next = match ($action) {
                'interview' => (float) $data['marks'] > 50 ? 'verification' : 'interview',
                'verification' => $data['result'] === 'approved' ? 'approved' : 'verification',
                'approved' => 'proposal',
                'proposal' => $data['result'] === 'approved' ? 'agreement' : 'proposal',
                'agreement' => 'completed',
            };
            $details = $record->workflow_data ?? [];
            $details[$action] = $data + ['recorded_at' => now()->toIso8601String()];
            $history = $record->workflow_history ?? [];
            $history[] = ['from' => $action, 'to' => $next, 'action' => $action, 'data' => $data, 'by' => $request->user()->id, 'at' => now()->toIso8601String()];
            $record->workflow_stage = $next;
            $record->workflow_data = $details;
            $record->workflow_history = $history;
            $record->save();
            return $next;
        });
        $page = match ($next) {
            'interview' => 'reviewed-interviews', 'verification' => 'field-visits',
            'approved' => 'approved', 'proposal' => 'full-proposals', default => 'agreements',
        };
        return redirect()->route('individual-workflow.'.$page)->with('status', $next === 'completed' ? 'Agreement signed. Individual workflow completed.' : ($next === $action ? 'Review saved. Applicant remains in this stage.' : 'Review saved. Applicant moved to the next stage.'));
    }
}
