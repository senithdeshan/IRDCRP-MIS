<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tank_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('tank_registrations', 'contractor_address')) {
                $table->text('contractor_address')->nullable()->after('contractor');
            }

            if (! Schema::hasColumn('tank_registrations', 'contractor_contact_number')) {
                $table->string('contractor_contact_number')->nullable()->after('contractor_address');
            }

            if (! Schema::hasColumn('tank_registrations', 'contractor_cida_grade')) {
                $table->string('contractor_cida_grade')->nullable()->after('contractor_contact_number');
            }

            if (! Schema::hasColumn('tank_registrations', 'construction_start_date')) {
                $table->date('construction_start_date')->nullable()->after('contractor_cida_grade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tank_registrations', function (Blueprint $table) {
            foreach ([
                'contractor_address',
                'contractor_contact_number',
                'contractor_cida_grade',
                'construction_start_date',
            ] as $column) {
                if (Schema::hasColumn('tank_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
