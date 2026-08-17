<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tank_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('tank_id')->unique();
            $table->string('tank_name');
            $table->string('river_basin')->nullable();
            $table->string('cascade_name')->nullable();
            $table->string('province')->nullable()->index();
            $table->string('district')->nullable()->index();
            $table->string('ds_division')->nullable();
            $table->string('gn_division')->nullable();
            $table->string('as_centre')->nullable();
            $table->string('agency')->nullable();
            $table->unsignedInteger('no_of_family')->nullable();
            $table->decimal('longitude', 15, 7)->nullable();
            $table->decimal('latitude', 15, 7)->nullable();
            $table->decimal('progress', 5, 2)->nullable();
            $table->string('contractor')->nullable();
            $table->text('contractor_address')->nullable();
            $table->string('contractor_contact_number')->nullable();
            $table->string('contractor_cida_grade')->nullable();
            $table->date('construction_start_date')->nullable();
            $table->string('payment')->nullable()->index();
            $table->date('awarded_date')->nullable();
            $table->unsignedInteger('construction_period_days')->nullable();
            $table->unsignedInteger('extension_of_time_months')->nullable();
            $table->string('status')->nullable()->index();
            $table->text('remarks')->nullable();
            $table->string('open_ref_no')->nullable();
            $table->decimal('cumulative_amount', 15, 2)->nullable();
            $table->decimal('paid_advanced_amount', 15, 2)->nullable();
            $table->string('recommended_ipc_no')->nullable();
            $table->decimal('recommended_ipc_amount', 15, 2)->nullable();
            $table->decimal('base_cost', 15, 2)->nullable();
            $table->decimal('physical_contingencies', 15, 2)->nullable();
            $table->decimal('price_contingencies', 15, 2)->nullable();
            $table->decimal('net_value', 15, 2)->nullable();
            $table->decimal('vat', 15, 2)->nullable();
            $table->decimal('grand_total', 15, 2)->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tank_registrations');
    }
};
