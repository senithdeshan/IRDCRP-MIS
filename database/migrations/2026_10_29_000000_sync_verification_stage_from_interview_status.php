<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VERIFICATION_PASS_STATUSES = [
        'likely_mature',
        'likely_immature',
        'likely_immature_possible_to_improve',
    ];

    public function up(): void
    {
        DB::table('productive_partnership_eois')
            ->whereIn('interview_status', self::VERIFICATION_PASS_STATUSES)
            ->update(['verification_stage' => true]);

        DB::table('productive_partnership_eois')
            ->where(function ($query) {
                $query->whereNull('interview_status')
                    ->orWhereNotIn('interview_status', self::VERIFICATION_PASS_STATUSES);
            })
            ->update(['verification_stage' => false]);

        // Older records may have been marked approved by the marks-based backfill.
        DB::table('productive_partnership_eois')
            ->where('verification_status', 'approved')
            ->where(function ($query) {
                $query->whereNull('interview_status')
                    ->orWhereNotIn('interview_status', self::VERIFICATION_PASS_STATUSES);
            })
            ->update([
                'verification_status' => 'pending',
                'approved_at' => null,
                'verified_at' => null,
            ]);
    }

    public function down(): void
    {
        // The prior stage values were derived from marks and cannot be restored reliably.
    }
};
