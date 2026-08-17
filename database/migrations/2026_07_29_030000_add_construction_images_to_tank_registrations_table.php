<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tank_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('tank_registrations', 'pre_construction_images')) {
                $table->json('pre_construction_images')->nullable()->after('grand_total');
            }

            if (! Schema::hasColumn('tank_registrations', 'during_construction_images')) {
                $table->json('during_construction_images')->nullable()->after('pre_construction_images');
            }

            if (! Schema::hasColumn('tank_registrations', 'post_construction_images')) {
                $table->json('post_construction_images')->nullable()->after('during_construction_images');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tank_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'pre_construction_images',
                'during_construction_images',
                'post_construction_images',
            ]);
        });
    }
};
