<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CercAssetCacMember extends Model
{
    protected $fillable = ['cmc_name', 'cac_name', 'member_name', 'position', 'nic_number', 'gender', 'contact_number'];
    public function asset(): BelongsTo { return $this->belongsTo(CercInfrastructureAsset::class, 'cerc_infrastructure_asset_id'); }
}
