<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CercInfrastructureAsset extends Model
{
    protected $fillable = [
        'tank_type', 'created_by', 'institution', 'programme_type', 'contract_status', 'province', 'district',
        'ds_division', 'asc', 'tank_name', 'civil_cost', 'preliminaries_cost', 'direct_cost', 'vat_18',
        'contingencies_cost', 'total_cost_estimate', 'awarded_civil_cost', 'awarded_preliminaries_cost',
        'awarded_amount_without_vat_contingencies', 'advance_imprest', 'provisional_sum', 'category', 'year',
        'size', 'beneficiary_families', 'command_area_ac', 'lot', 'cluster', 'date_of_award', 'agreement_sign_date',
        'contract_period_days', 'completion_date', 'contract_number', 'contractor_name', 'contractor_address',
        'contractor_contact_number', 'emp_clearance_received_date', 'smp_clearance_received_date',
        'esmp_submitted_to_procurement_date',
        'progress', 'before_work_photo_path', 'current_photo_1_path', 'current_photo_2_path',
        'contractor_board_photo_path', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'civil_cost' => 'decimal:2', 'preliminaries_cost' => 'decimal:2', 'direct_cost' => 'decimal:2',
            'vat_18' => 'decimal:2', 'contingencies_cost' => 'decimal:2', 'total_cost_estimate' => 'decimal:2',
            'awarded_civil_cost' => 'decimal:2', 'awarded_preliminaries_cost' => 'decimal:2',
            'awarded_amount_without_vat_contingencies' => 'decimal:2', 'advance_imprest' => 'decimal:2',
            'provisional_sum' => 'decimal:2', 'command_area_ac' => 'decimal:2',
            'progress' => 'decimal:2', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7',
            'date_of_award' => 'date', 'agreement_sign_date' => 'date', 'completion_date' => 'date',
            'emp_clearance_received_date' => 'date', 'smp_clearance_received_date' => 'date',
            'esmp_submitted_to_procurement_date' => 'date',
        ];
    }
}
