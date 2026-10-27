<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationFarmerOrganizationMember extends Model
{
    protected $fillable = [
        'membership_number', 'name_with_initials', 'gender', 'nic_number', 'phone_number', 'address',
        'province', 'district', 'ds_division', 'gn_division', 'land_extent_acres', 'role_in_organization',
    ];

    public function farmerOrganization(): BelongsTo
    {
        return $this->belongsTo(IrrigationFarmerOrganization::class);
    }
}
