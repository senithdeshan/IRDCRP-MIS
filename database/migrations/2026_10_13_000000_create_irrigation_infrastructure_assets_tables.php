<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_infrastructure_assets', function (Blueprint $table) {
            $table->id();
            $table->string('river_basin')->nullable();
            $table->string('river_basin_number')->nullable();
            $table->string('tank_name');
            $table->string('tank_registration_number')->nullable();
            $table->string('cascade_name')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ds_division')->nullable();
            $table->string('asc')->nullable();
            $table->string('gn_division')->nullable();
            $table->string('gn_code', 30)->nullable();
            $table->unsignedInteger('gn_division_families')->nullable();
            $table->decimal('tank_capacity_ac_ft', 12, 2)->nullable();
            $table->decimal('catchment_area_ha', 12, 2)->nullable();
            $table->decimal('irrigable_area_existing_ha', 12, 2)->nullable();
            $table->decimal('irrigable_area_cultivated_ha', 12, 2)->nullable();
            $table->string('main_crop_cultivation')->nullable();
            $table->unsignedInteger('tank_farmer_families')->nullable();
            $table->unsignedInteger('farmer_organization_families')->nullable();
            $table->string('scheme_type_tank')->nullable();
            $table->string('scheme_type_anicut')->nullable();
            $table->decimal('canal_length_left_bank_km', 10, 3)->nullable();
            $table->decimal('canal_length_right_bank_km', 10, 3)->nullable();
            $table->string('tank_status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('irrigation_asset_cac_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_infrastructure_asset_id')->constrained()->cascadeOnDelete();
            $table->string('cmc_name')->nullable();
            $table->string('cac_name')->nullable();
            $table->string('member_name');
            $table->string('position');
            $table->string('nic_number', 12)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('contact_number', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_asset_cac_members');
        Schema::dropIfExists('irrigation_infrastructure_assets');
    }
};
