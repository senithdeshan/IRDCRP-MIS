<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_farmer_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_infrastructure_asset_id')->unique()->constrained()->cascadeOnDelete();
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

        Schema::create('irrigation_farmer_organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('irrigation_farmer_organization_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('irrigation_farmer_organization_members');
        Schema::dropIfExists('irrigation_farmer_organizations');
    }
};
