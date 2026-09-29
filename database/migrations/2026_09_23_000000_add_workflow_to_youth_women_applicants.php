<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youth_women_applicants', function (Blueprint $table) {
            $table->string('workflow_stage')->nullable()->index();
            $table->json('workflow_data')->nullable();
            $table->json('workflow_history')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('youth_women_applicants', fn (Blueprint $table) => $table->dropColumn(['workflow_stage', 'workflow_data', 'workflow_history']));
    }
};
