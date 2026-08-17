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
            $table->unsignedSmallInteger('eoi_year')->nullable()->after('eoi_number')->index();
            $table->unsignedSmallInteger('eoi_call_number')->nullable()->after('eoi_year')->index();
        });

        DB::table('productive_partnership_eois')
            ->select(['id', 'eoi_number'])
            ->orderBy('id')
            ->chunk(100, function ($eois) {
                foreach ($eois as $eoi) {
                    $parts = $this->parseEoiNumber($eoi->eoi_number);

                    DB::table('productive_partnership_eois')
                        ->where('id', $eoi->id)
                        ->update([
                            'eoi_year' => $parts['year'],
                            'eoi_call_number' => $parts['call_number'],
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->dropIndex(['eoi_year']);
            $table->dropIndex(['eoi_call_number']);
            $table->dropColumn(['eoi_year', 'eoi_call_number']);
        });
    }

    private function parseEoiNumber(?string $eoiNumber): array
    {
        $segments = array_values(array_filter(
            explode('/', trim((string) $eoiNumber)),
            fn ($segment) => $segment !== ''
        ));

        $year = null;
        $callNumber = null;

        if (
            count($segments) >= 4
            && strtoupper($segments[0]) === 'EOI'
            && strtoupper($segments[1]) === 'PP'
            && preg_match('/^\d{4}$/', $segments[2])
        ) {
            $year = (int) $segments[2];
        }

        if (count($segments) >= 5 && preg_match('/^\d+$/', $segments[3])) {
            $callNumber = (int) $segments[3];
        }

        return [
            'year' => $year,
            'call_number' => $callNumber,
        ];
    }
};
