<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cerc_infrastructure_assets', function (Blueprint $table): void {
            $table->decimal('progress', 5, 2)->nullable();
            $table->string('before_work_photo_path')->nullable();
            $table->string('current_photo_1_path')->nullable();
            $table->string('current_photo_2_path')->nullable();
            $table->string('contractor_board_photo_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cerc_infrastructure_assets', function (Blueprint $table): void {
            $table->dropColumn([
                'progress', 'before_work_photo_path', 'current_photo_1_path', 'current_photo_2_path',
                'contractor_board_photo_path', 'latitude', 'longitude',
            ]);
        });
    }
};
