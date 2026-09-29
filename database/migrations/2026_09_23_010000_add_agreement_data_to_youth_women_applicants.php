<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youth_women_applicants', fn (Blueprint $table) => $table->json('agreement_eoi_data')->nullable());
    }

    public function down(): void
    {
        Schema::table('youth_women_applicants', fn (Blueprint $table) => $table->dropColumn('agreement_eoi_data'));
    }
};
