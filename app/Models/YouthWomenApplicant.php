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
        ];
    }
}
