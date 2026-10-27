<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('photo_name')->nullable()->after('photo_path');
            $table->string('appointment_letter_name')->nullable()->after('appointment_letter_path');
            $table->string('cv_name')->nullable()->after('cv_path');
        });
    }

    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn(['photo_name', 'appointment_letter_name', 'cv_name']);
        });
    }
};
