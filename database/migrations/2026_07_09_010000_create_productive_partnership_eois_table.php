<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productive_partnership_eois', function (Blueprint $table) {
            $table->id();
            $table->string('eoi_number')->unique();
            $table->string('organization_name')->nullable();
            $table->unsignedInteger('number_of_members')->nullable();
            $table->string('legal_status')->nullable();
            $table->string('place_of_registration')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_designation')->nullable();
            $table->string('contact_person_telephone', 30)->nullable();
            $table->text('organization_registered_address')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->text('proposed_business_location_address')->nullable();
            $table->string('province')->nullable()->index();
            $table->string('district')->nullable()->index();
            $table->string('ds_division')->nullable();
            $table->string('business_proposal_title')->nullable();
            $table->string('sector')->nullable()->index();
            $table->decimal('proposed_total_investment', 15, 2)->default(0);
            $table->decimal('expected_grant_irdcrp', 15, 2)->default(0);
            $table->string('completeness_mandatory_requirement')->nullable();
            $table->string('initial_desk_review_status')->nullable()->index();
            $table->date('initial_screening_date')->nullable();
            $table->string('kobo_id')->nullable();
            $table->string('kobo_uuid')->nullable()->index();
            $table->timestamp('submission_time')->nullable();
            $table->string('validation_status')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->boolean('initial_stage')->default(false)->index();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productive_partnership_eois');
    }
};
