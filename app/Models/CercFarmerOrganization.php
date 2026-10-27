<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CercFarmerOrganization extends Model
{
    protected $fillable = ['cerc_infrastructure_asset_id', 'name', 'legal_registration_status', 'registration_number', 'registration_date', 'address', 'contact_number', 'email', 'total_members', 'created_by'];
    protected $casts = ['registration_date' => 'date'];
    public function asset(): BelongsTo { return $this->belongsTo(CercInfrastructureAsset::class, 'cerc_infrastructure_asset_id'); }
    public function members(): HasMany { return $this->hasMany(CercFarmerOrganizationMember::class); }
}
