<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->decimal('interview_marks', 5, 2)->nullable()->after('initial_stage');
            $table->text('interview_notes')->nullable()->after('interview_marks');
            $table->timestamp('approved_at')->nullable()->after('interview_notes');
            $table->date('pre_construction_date')->nullable()->after('approved_at');
            $table->text('pre_construction_notes')->nullable()->after('pre_construction_date');
            $table->json('pre_construction_images')->nullable()->after('pre_construction_notes');
            $table->date('during_construction_date')->nullable()->after('pre_construction_images');
            $table->text('during_construction_notes')->nullable()->after('during_construction_date');
            $table->json('during_construction_images')->nullable()->after('during_construction_notes');
            $table->date('post_construction_date')->nullable()->after('during_construction_images');
            $table->text('post_construction_notes')->nullable()->after('post_construction_date');
            $table->json('post_construction_images')->nullable()->after('post_construction_notes');
            $table->string('business_registration_image')->nullable()->after('post_construction_images');
            $table->string('business_proposal_pdf')->nullable()->after('business_registration_image');
        });
    }

    public function down(): void
    {
        Schema::table('productive_partnership_eois', function (Blueprint $table) {
            $table->dropColumn([
                'interview_marks',
                'interview_notes',
                'approved_at',
                'pre_construction_date',
                'pre_construction_notes',
                'pre_construction_images',
                'during_construction_date',
                'during_construction_notes',
                'during_construction_images',
                'post_construction_date',
                'post_construction_notes',
                'post_construction_images',
                'business_registration_image',
                'business_proposal_pdf',
            ]);
        });
    }
};
