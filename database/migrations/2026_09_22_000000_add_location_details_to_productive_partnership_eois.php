<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->string('gn_division')->nullable();
            $table->string('as_centre')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->dropColumn(['gn_division', 'as_centre']);
        });
    }
};
