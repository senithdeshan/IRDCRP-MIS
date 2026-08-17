<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tank_registrations')) {
            return;
        }

        Schema::table('tank_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('tank_registrations', 'tank_id')) {
                $table->string('tank_id')->nullable()->unique()->after('id');
            }

            foreach ([
                'river_basin' => fn () => $table->string('river_basin')->nullable()->after('tank_name'),
                'ds_division' => fn () => $table->string('ds_division')->nullable()->after('district'),
                'gn_division' => fn () => $table->string('gn_division')->nullable()->after('ds_division'),
                'as_centre' => fn () => $table->string('as_centre')->nullable()->after('gn_division'),
                'agency' => fn () => $table->string('agency')->nullable()->after('as_centre'),
                'no_of_family' => fn () => $table->unsignedInteger('no_of_family')->nullable()->after('agency'),
                'longitude' => fn () => $table->decimal('longitude', 15, 7)->nullable()->after('no_of_family'),
                'latitude' => fn () => $table->decimal('latitude', 15, 7)->nullable()->after('longitude'),
                'progress' => fn () => $table->decimal('progress', 5, 2)->nullable()->after('latitude'),
                'contractor' => fn () => $table->string('contractor')->nullable()->after('progress'),
                'payment' => fn () => $table->string('payment')->nullable()->after('contractor'),
                'awarded_date' => fn () => $table->date('awarded_date')->nullable()->after('payment'),
                'construction_period_days' => fn () => $table->unsignedInteger('construction_period_days')->nullable()->after('awarded_date'),
                'extension_of_time_months' => fn () => $table->unsignedInteger('extension_of_time_months')->nullable()->after('construction_period_days'),
                'status' => fn () => $table->string('status')->nullable()->after('extension_of_time_months'),
                'open_ref_no' => fn () => $table->string('open_ref_no')->nullable()->after('remarks'),
                'cumulative_amount' => fn () => $table->decimal('cumulative_amount', 15, 2)->nullable()->after('open_ref_no'),
                'paid_advanced_amount' => fn () => $table->decimal('paid_advanced_amount', 15, 2)->nullable()->after('cumulative_amount'),
                'recommended_ipc_no' => fn () => $table->string('recommended_ipc_no')->nullable()->after('paid_advanced_amount'),
                'recommended_ipc_amount' => fn () => $table->decimal('recommended_ipc_amount', 15, 2)->nullable()->after('recommended_ipc_no'),
                'base_cost' => fn () => $table->decimal('base_cost', 15, 2)->nullable()->after('recommended_ipc_amount'),
                'physical_contingencies' => fn () => $table->decimal('physical_contingencies', 15, 2)->nullable()->after('base_cost'),
                'price_contingencies' => fn () => $table->decimal('price_contingencies', 15, 2)->nullable()->after('physical_contingencies'),
                'net_value' => fn () => $table->decimal('net_value', 15, 2)->nullable()->after('price_contingencies'),
                'vat' => fn () => $table->decimal('vat', 15, 2)->nullable()->after('net_value'),
                'grand_total' => fn () => $table->decimal('grand_total', 15, 2)->nullable()->after('vat'),
            ] as $column => $definition) {
                if (! Schema::hasColumn('tank_registrations', $column)) {
                    $definition();
                }
            }
        });

        if (Schema::hasColumn('tank_registrations', 'tank_code')) {
            DB::table('tank_registrations')
                ->whereNull('tank_id')
                ->update(['tank_id' => DB::raw('tank_code')]);
        }

        $columnPairs = [
            'river_basin_name' => 'river_basin',
            'dsd' => 'ds_division',
            'gnd' => 'gn_division',
            'asc' => 'as_centre',
            'ownership_agency' => 'agency',
            'beneficiary_families' => 'no_of_family',
        ];

        foreach ($columnPairs as $old => $new) {
            if (Schema::hasColumn('tank_registrations', $old) && Schema::hasColumn('tank_registrations', $new)) {
                DB::table('tank_registrations')
                    ->whereNull($new)
                    ->update([$new => DB::raw($old)]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
