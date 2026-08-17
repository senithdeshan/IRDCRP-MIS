<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->string('full_proposal_status')->default('pending')->after('business_proposal_pdf')->index();
            $table->text('full_proposal_notes')->nullable()->after('full_proposal_status');
            $table->timestamp('full_proposal_reviewed_at')->nullable()->after('full_proposal_notes');
        });
    }

    public function down(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->dropColumn([
                'full_proposal_status',
                'full_proposal_notes',
                'full_proposal_reviewed_at',
            ]);
        });
    }
};
