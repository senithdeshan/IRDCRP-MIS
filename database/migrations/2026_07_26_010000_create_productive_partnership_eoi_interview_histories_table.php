<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productive_partnership_eoi_interview_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('productive_partnership_eoi_id')->constrained()->cascadeOnDelete();
            $table->string('from_interview_status')->nullable();
            $table->string('to_interview_status')->index();
            $table->decimal('interview_marks', 5, 2)->nullable();
            $table->text('interview_notes')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productive_partnership_eoi_interview_histories');
    }
};
