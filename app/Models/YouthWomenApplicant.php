<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YouthWomenApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_reference',
        'eoi_number',
        'applicant_name',
        'gender',
        'nic',
        'date_of_birth',
        'age_as_at_2026',
        'telephone',
        'whatsapp',
        'email',
        'business_name',
        'legal_status',
        'business_registered_address',
        'province',
        'district',
        'ds_division',
        'business_registration_number',
        'business_registration_date',
        'business_sectors',
        'proposed_total_investment',
        'initial_screening_result',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'business_registration_date' => 'date',
            'age_as_at_2026' => 'integer',
            'business_sectors' => 'array',
            'proposed_total_investment' => 'decimal:2',
            'imported_at' => 'datetime',
            'workflow_data' => 'array',
            'agreement_eoi_data' => 'array',
            'workflow_history' => 'array',
        ];
    }

    public function getCurrentWorkflowStageAttribute(): string
    {
        return $this->initial_screening_result === 'Selected' ? ($this->workflow_stage ?? 'interview') : 'received';
    }

    public function workflowStatus(string $stage): string
    {
        $data = $this->workflow_data[$stage] ?? [];

        return match ($stage) {
            'interview' => ! isset($data['marks']) ? 'pending' : ((float) $data['marks'] > 50 ? 'passed' : 'not_passed'),
            'verification', 'proposal' => $data['result'] ?? 'pending',
            'agreement' => $this->workflow_stage === 'completed' ? 'signed' : 'pending',
            default => isset($this->workflow_data['approved']) ? 'advanced' : 'pending',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (self $applicant) {
            if ($applicant->exists && $applicant->isDirty('initial_screening_result') && $applicant->initial_screening_result !== 'Selected') {
                $history = $applicant->workflow_history ?? [];
                $history[] = ['from' => $applicant->workflow_stage ?? 'interview', 'to' => 'received', 'action' => 'Screening changed; workflow reset', 'data' => $applicant->workflow_data, 'by' => auth()->id(), 'at' => now()->toIso8601String()];
                $applicant->workflow_stage = null;
                $applicant->workflow_data = null;
                $applicant->workflow_history = $history;
            }
        });
    }
}
