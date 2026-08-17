<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductivePartnershipEoiInterviewHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'productive_partnership_eoi_id',
        'from_interview_status',
        'to_interview_status',
        'interview_marks',
        'interview_notes',
        'changed_by_user_id',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'interview_marks' => 'decimal:2',
            'changed_at' => 'datetime',
        ];
    }

    public function eoi(): BelongsTo
    {
        return $this->belongsTo(ProductivePartnershipEoi::class, 'productive_partnership_eoi_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function getFromInterviewStatusLabelAttribute(): ?string
    {
        return ProductivePartnershipEoi::INTERVIEW_STATUSES[$this->from_interview_status] ?? null;
    }

    public function getToInterviewStatusLabelAttribute(): ?string
    {
        return ProductivePartnershipEoi::INTERVIEW_STATUSES[$this->to_interview_status] ?? null;
    }
}
