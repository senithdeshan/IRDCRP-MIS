<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->boolean('verification_stage')->default(false)->after('interview_notes')->index();
            $table->string('verification_status')->default('pending')->after('verification_stage')->index();
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verification_notes');
        });

        DB::table('productive_partnership_eois')
            ->where('interview_marks', '>', 50)
            ->update([
                'verification_stage' => true,
                'verification_status' => 'approved',
                'verified_at' => DB::raw('COALESCE(approved_at, updated_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->dropIndex(['verification_stage']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_stage',
                'verification_status',
                'verification_notes',
                'verified_at',
            ]);
        });
    }
};
