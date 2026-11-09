<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cerc_infrastructure_assets', function (Blueprint $table): void {
            $table->string('institution')->nullable();
            $table->string('programme_type')->nullable();
            $table->string('contract_status')->nullable();
            $table->decimal('civil_cost', 15, 2)->nullable();
            $table->decimal('preliminaries_cost', 15, 2)->nullable();
            $table->decimal('direct_cost', 15, 2)->nullable();
            $table->decimal('vat_18', 15, 2)->nullable();
            $table->decimal('contingencies_cost', 15, 2)->nullable();
            $table->decimal('total_cost_estimate', 15, 2)->nullable();
            $table->decimal('awarded_civil_cost', 15, 2)->nullable();
            $table->decimal('awarded_preliminaries_cost', 15, 2)->nullable();
            $table->decimal('awarded_amount_without_vat_contingencies', 15, 2)->nullable();
            $table->decimal('advance_imprest', 15, 2)->nullable();
            $table->decimal('provisional_sum', 15, 2)->nullable();
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('size')->nullable();
            $table->unsignedInteger('beneficiary_families')->nullable();
            $table->decimal('command_area_ac', 12, 2)->nullable();
            $table->string('lot')->nullable();
            $table->string('cluster')->nullable();
            $table->date('date_of_award')->nullable();
            $table->date('agreement_sign_date')->nullable();
            $table->unsignedInteger('contract_period_days')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('contractor_name')->nullable();
            $table->text('contractor_address')->nullable();
            $table->string('contractor_contact_number', 50)->nullable();
            $table->date('emp_clearance_received_date')->nullable();
            $table->date('smp_clearance_received_date')->nullable();
            $table->date('esmp_submitted_to_procurement_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cerc_infrastructure_assets', function (Blueprint $table): void {
            $table->dropColumn([
                'institution', 'programme_type', 'contract_status', 'civil_cost', 'preliminaries_cost', 'direct_cost',
                'vat_18', 'contingencies_cost', 'total_cost_estimate', 'awarded_civil_cost', 'awarded_preliminaries_cost',
                'awarded_amount_without_vat_contingencies', 'advance_imprest', 'provisional_sum', 'category', 'year',
                'size', 'beneficiary_families', 'command_area_ac', 'lot', 'cluster', 'date_of_award', 'agreement_sign_date',
                'contract_period_days', 'completion_date', 'contract_number', 'contractor_name', 'contractor_address',
                'contractor_contact_number', 'emp_clearance_received_date', 'smp_clearance_received_date',
                'esmp_submitted_to_procurement_date',
            ]);
        });
    }
};
