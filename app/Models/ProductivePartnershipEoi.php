<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductivePartnershipEoi extends Model
{
    use HasFactory;

    public const INTERVIEW_STATUSES = [
        'likely_mature' => 'Likely a Mature',
        'likely_immature' => 'Likely an Immature',
        'likely_immature_possible_to_improve' => 'Likely an Immature (Possible to Improve)',
        'ineligible' => 'Ineligible',
        'resubmit' => 'Resubmit',
    ];

    public const VERIFICATION_PASS_STATUSES = [
        'likely_mature',
        'likely_immature',
        'likely_immature_possible_to_improve',
    ];

    public const AGREEMENT_TRACKING_STAGES = [
        'draft_proposal_fop' => 'PMU Received Draft Proposal from FPO',
        'fp_handed_over_to_isf' => 'Draft Proposal Handed Over to ISF',
        'pmu_received_fp_from_isf' => 'PMU Received the Full Proposal from ISF',
        'handed_over_iec' => 'Full Proposal Handed Over to IEC',
        'iec_evaluation' => 'PMU Received the Full Proposal from IEC',
        'nsc_submission' => 'Full Proposal Submitted to NSC',
        'agreement_sign' => 'Agreement Signed',
    ];

    protected $fillable = [
        'eoi_number',
        'eoi_year',
        'eoi_call_number',
        'organization_name',
        'number_of_members',
        'legal_status',
        'place_of_registration',
        'registration_number',
        'registration_date',
        'contact_person_name',
        'contact_person_designation',
        'contact_person_telephone',
        'organization_registered_address',
        'contact_person_email',
        'proposed_business_location_address',
        'province',
        'district',
        'ds_division',
        'gn_division',
        'as_centre',
        'business_proposal_title',
        'sector',
        'proposed_total_investment',
        'expected_grant_irdcrp',
        'completeness_mandatory_requirement',
        'initial_desk_review_status',
        'initial_screening_date',
        'kobo_id',
        'kobo_uuid',
        'submission_time',
        'validation_status',
        'notes',
        'status',
        'initial_stage',
        'interview_marks',
        'interview_status',
        'interview_resubmitted_at',
        'interview_notes',
        'verification_stage',
        'verification_status',
        'verification_notes',
        'verified_at',
        'approved_at',
        'pre_construction_date',
        'pre_construction_notes',
        'pre_construction_images',
        'during_construction_date',
        'during_construction_notes',
        'during_construction_images',
        'post_construction_date',
        'post_construction_notes',
        'post_construction_images',
        'business_registration_image',
        'business_proposal_pdf',
        'fop_agreement_tracking',
        'agreement_eoi_data',
        'full_proposal_status',
        'full_proposal_notes',
        'full_proposal_reviewed_at',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'number_of_members' => 'integer',
            'registration_date' => 'date',
            'initial_screening_date' => 'date',
            'submission_time' => 'datetime',
            'proposed_total_investment' => 'decimal:2',
            'expected_grant_irdcrp' => 'decimal:2',
            'initial_stage' => 'boolean',
            'interview_marks' => 'decimal:2',
            'interview_resubmitted_at' => 'datetime',
            'verification_stage' => 'boolean',
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'pre_construction_date' => 'date',
            'pre_construction_images' => 'array',
            'during_construction_date' => 'date',
            'during_construction_images' => 'array',
            'post_construction_date' => 'date',
            'post_construction_images' => 'array',
            'fop_agreement_tracking' => 'array',
            'agreement_eoi_data' => 'array',
            'full_proposal_reviewed_at' => 'datetime',
            'imported_at' => 'datetime',
            'eoi_year' => 'integer',
            'eoi_call_number' => 'integer',
        ];
    }

    public function getFarmerScreeningResultAttribute(): string
    {
        return $this->initial_stage ? 'Selected' : (in_array($this->initial_desk_review_status, ['Reject', 'Rejected'], true) ? 'Reject' : 'Pending');
    }

    public function getInterviewStatusLabelAttribute(): ?string
    {
        return self::INTERVIEW_STATUSES[$this->interview_status] ?? null;
    }

    public function passesInterviewReview(): bool
    {
        return in_array($this->interview_status, self::VERIFICATION_PASS_STATUSES, true);
    }

    public function getWasResubmittedForInterviewAttribute(): bool
    {
        return filled($this->interview_resubmitted_at);
    }

    public function agreementTrackingRows(): array
    {
        $tracking = $this->fop_agreement_tracking ?? [];
        $tracking['draft_proposal_fop'] = $tracking['draft_proposal_fop'] ?? $tracking['pmu_received_draft_fp_from_po'] ?? null;
        $tracking['pmu_received'] = $tracking['pmu_received'] ?? $tracking['iec_evaluation'] ?? null;

        return collect(self::AGREEMENT_TRACKING_STAGES)
            ->map(function (string $label, string $key) use ($tracking) {
                $stage = $tracking[$key] ?? [];
                $date = $stage['date'] ?? null;
                $value = $stage['value'] ?? null;

                return [
                    'key' => $key,
                    'label' => $label,
                    'date' => $date,
                    'value' => $value,
                    'complete' => filled($date),
                ];
            })
            ->values()
            ->all();
    }

    public function currentAgreementTrackingStage(): array
    {
        $rows = $this->agreementTrackingRows();
        $current = collect($rows)->filter(fn (array $row) => $row['complete'])->last();

        if (! $current) {
            return [
                'key' => null,
                'label' => 'Not started',
                'date' => null,
                'value' => null,
                'complete' => false,
                'number' => 0,
            ];
        }

        $current['number'] = collect($rows)->search(fn (array $row) => $row['key'] === $current['key']) + 1;

        return $current;
    }

    public function agreementTrackingCompletedCount(): int
    {
        return collect($this->agreementTrackingRows())
            ->filter(fn (array $row) => $row['complete'])
            ->count();
    }

    public function agreementTrackingProgressPercent(): int
    {
        return (int) round(($this->agreementTrackingCompletedCount() / max(count(self::AGREEMENT_TRACKING_STAGES), 1)) * 100);
    }

    public function interviewHistories(): HasMany
    {
        return $this->hasMany(ProductivePartnershipEoiInterviewHistory::class)->latest('changed_at');
    }

    protected static function booted(): void
    {
        static::saving(function (ProductivePartnershipEoi $eoi) {
            if (! $eoi->isDirty('eoi_number')) {
                return;
            }

            $parts = self::parseEoiNumber($eoi->eoi_number);

            if (! $eoi->isDirty('eoi_year')) {
                $eoi->eoi_year = $parts['year'];
            }
            if (! $eoi->isDirty('eoi_call_number')) {
                $eoi->eoi_call_number = $parts['call_number'];
            }
        });
    }

    public static function parseEoiNumber(?string $eoiNumber): array
    {
        $segments = array_values(array_filter(
            explode('/', trim((string) $eoiNumber)),
            fn ($segment) => $segment !== ''
        ));

        $year = null;
        $callNumber = null;

        if (
            count($segments) >= 4
            && strtoupper($segments[0]) === 'EOI'
            && strtoupper($segments[1]) === 'PP'
            && preg_match('/^\d{4}$/', $segments[2])
        ) {
            $year = (int) $segments[2];
        }

        if (count($segments) >= 5 && preg_match('/^\d+$/', $segments[3])) {
            $callNumber = (int) $segments[3];
        }

        return [
            'year' => $year,
            'call_number' => $callNumber,
        ];
    }
}
