<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youth_women_applicants', function (Blueprint $table) {
            $table->id();
            $table->string('project_reference')->nullable();
            $table->string('eoi_number')->unique();
            $table->string('applicant_name');
            $table->string('gender');
            $table->string('nic')->index();
            $table->date('date_of_birth')->nullable();
            $table->unsignedSmallInteger('age_as_at_2026')->nullable();
            $table->string('telephone', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('business_name');
            $table->string('legal_status');
            $table->text('business_registered_address')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ds_division')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->date('business_registration_date')->nullable();
            $table->json('business_sectors')->nullable();
            $table->decimal('proposed_total_investment', 15, 2)->default(0);
            $table->string('initial_screening_result')->nullable()->index();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youth_women_applicants');
    }
};
