<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->string('interview_status')->nullable()->after('interview_marks')->index();
            $table->timestamp('interview_resubmitted_at')->nullable()->after('interview_status');
        });
    }

    public function down(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->dropIndex(['interview_status']);
            $table->dropColumn(['interview_status', 'interview_resubmitted_at']);
        });
    }
};
