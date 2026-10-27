<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationAssetCacMember extends Model
{
    protected $fillable = ['cmc_name', 'cac_name', 'member_name', 'position', 'nic_number', 'gender', 'contact_number'];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(IrrigationInfrastructureAsset::class, 'irrigation_infrastructure_asset_id');
    }
}
