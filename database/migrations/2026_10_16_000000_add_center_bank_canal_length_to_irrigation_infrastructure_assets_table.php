<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('irrigation_infrastructure_assets', function (Blueprint $table) {
            $table->decimal('canal_length_center_bank_km', 10, 3)->nullable()->after('canal_length_left_bank_km');
        });
    }

    public function down(): void
    {
        Schema::table('irrigation_infrastructure_assets', function (Blueprint $table) {
            $table->dropColumn('canal_length_center_bank_km');
        });
    }
};
