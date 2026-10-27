<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cerc_infrastructure_assets', function (Blueprint $table) {
            $table->id();
            $table->string('tank_type', 12)->index();
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
            $table->decimal('canal_length_center_bank_km', 10, 3)->nullable();
            $table->decimal('canal_length_right_bank_km', 10, 3)->nullable();
            $table->string('tank_status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cerc_asset_cac_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cerc_infrastructure_asset_id')->constrained()->cascadeOnDelete();
            $table->string('cmc_name')->nullable();
            $table->string('cac_name')->nullable();
            $table->string('member_name');
            $table->string('position');
            $table->string('nic_number', 12)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('contact_number', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('cerc_farmer_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cerc_infrastructure_asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('legal_registration_status');
            $table->string('registration_number')->nullable();
            $table->date('registration_date')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('total_members')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cerc_farmer_organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cerc_farmer_organization_id')->constrained()->cascadeOnDelete();
            $table->string('membership_number')->nullable();
            $table->string('name_with_initials');
            $table->string('gender', 20)->nullable();
            $table->string('nic_number', 12)->nullable();
            $table->string('phone_number', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ds_division')->nullable();
            $table->string('gn_division')->nullable();
            $table->decimal('land_extent_acres', 12, 3)->nullable();
            $table->string('role_in_organization')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cerc_farmer_organization_members');
        Schema::dropIfExists('cerc_farmer_organizations');
        Schema::dropIfExists('cerc_asset_cac_members');
        Schema::dropIfExists('cerc_infrastructure_assets');
    }
};
