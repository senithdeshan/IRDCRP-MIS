<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IrrigationInfrastructureAsset extends Model
{
    protected $fillable = [
        'river_basin', 'river_basin_number', 'tank_name', 'tank_registration_number', 'cascade_name',
        'province', 'district', 'ds_division', 'asc', 'gn_division', 'gn_code', 'gn_division_families',
        'tank_capacity_ac_ft', 'catchment_area_ha', 'irrigable_area_existing_ha', 'irrigable_area_cultivated_ha',
        'main_crop_cultivation', 'tank_farmer_families', 'farmer_organization_families', 'scheme_type_tank',
        'scheme_type_anicut', 'canal_length_left_bank_km', 'canal_length_center_bank_km', 'canal_length_right_bank_km', 'tank_status', 'created_by',
    ];

    public function cacMembers(): HasMany
    {
        return $this->hasMany(IrrigationAssetCacMember::class);
    }

    public function farmerOrganization(): HasOne
    {
        return $this->hasOne(IrrigationFarmerOrganization::class);
    }
}
