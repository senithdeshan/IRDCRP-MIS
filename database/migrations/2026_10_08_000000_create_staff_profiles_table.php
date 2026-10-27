<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('staff_type', 100);
            $table->string('name_with_initials');
            $table->string('nic_number', 30)->unique();
            $table->string('grade', 100)->nullable();
            $table->string('designation');
            $table->text('permanent_address')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('mobile_fixed', 30)->nullable();
            $table->string('official_email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('w_op_number', 100)->nullable();
            $table->text('highest_education_qualifications')->nullable();
            $table->string('personal_file_number', 100)->nullable();
            $table->date('first_appointment_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('appointment_letter_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
