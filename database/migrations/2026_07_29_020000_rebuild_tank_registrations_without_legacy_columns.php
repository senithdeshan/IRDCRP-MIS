<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tank_registrations') || ! Schema::hasColumn('tank_registrations', 'tank_code')) {
            return;
        }

        $records = DB::table('tank_registrations')->orderBy('id')->get();

        Schema::dropIfExists('tank_registrations_rebuilt');

        Schema::create('tank_registrations_rebuilt', function (Blueprint $table) {
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

        foreach ($records as $record) {
            DB::table('tank_registrations_rebuilt')->insert([
                'id' => $record->id,
                'tank_id' => $this->value($record, 'tank_id') ?: $this->value($record, 'tank_code') ?: 'TANK-'.str_pad((string) $record->id, 4, '0', STR_PAD_LEFT),
                'tank_name' => $this->value($record, 'tank_name') ?: 'Unnamed Tank',
                'river_basin' => $this->value($record, 'river_basin') ?: $this->value($record, 'river_basin_name'),
                'cascade_name' => $this->value($record, 'cascade_name'),
                'province' => $this->value($record, 'province'),
                'district' => $this->value($record, 'district'),
                'ds_division' => $this->value($record, 'ds_division') ?: $this->value($record, 'dsd'),
                'gn_division' => $this->value($record, 'gn_division') ?: $this->value($record, 'gnd'),
                'as_centre' => $this->value($record, 'as_centre') ?: $this->value($record, 'asc'),
                'agency' => $this->value($record, 'agency') ?: $this->value($record, 'ownership_agency'),
                'no_of_family' => $this->value($record, 'no_of_family') ?: $this->value($record, 'beneficiary_families'),
                'longitude' => $this->value($record, 'longitude'),
                'latitude' => $this->value($record, 'latitude'),
                'progress' => $this->value($record, 'progress'),
                'contractor' => $this->value($record, 'contractor'),
                'payment' => $this->value($record, 'payment'),
                'awarded_date' => $this->value($record, 'awarded_date'),
                'construction_period_days' => $this->value($record, 'construction_period_days'),
                'extension_of_time_months' => $this->value($record, 'extension_of_time_months'),
                'status' => $this->value($record, 'status') ?: $this->value($record, 'rehabilitation_status') ?: $this->value($record, 'tank_status'),
                'remarks' => $this->value($record, 'remarks'),
                'open_ref_no' => $this->value($record, 'open_ref_no'),
                'cumulative_amount' => $this->value($record, 'cumulative_amount'),
                'paid_advanced_amount' => $this->value($record, 'paid_advanced_amount'),
                'recommended_ipc_no' => $this->value($record, 'recommended_ipc_no'),
                'recommended_ipc_amount' => $this->value($record, 'recommended_ipc_amount'),
                'base_cost' => $this->value($record, 'base_cost'),
                'physical_contingencies' => $this->value($record, 'physical_contingencies'),
                'price_contingencies' => $this->value($record, 'price_contingencies'),
                'net_value' => $this->value($record, 'net_value'),
                'vat' => $this->value($record, 'vat'),
                'grand_total' => $this->value($record, 'grand_total'),
                'imported_at' => $this->value($record, 'imported_at'),
                'created_at' => $this->value($record, 'created_at') ?: now(),
                'updated_at' => $this->value($record, 'updated_at') ?: now(),
            ]);
        }

        Schema::drop('tank_registrations');
        Schema::rename('tank_registrations_rebuilt', 'tank_registrations');
    }

    public function down(): void
    {
        //
    }

    private function value(object $record, string $field): mixed
    {
        return property_exists($record, $field) ? $record->{$field} : null;
    }
};
