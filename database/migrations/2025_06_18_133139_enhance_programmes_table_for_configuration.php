<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            // Link to configuration system
            $table->foreignId('programme_type_id')->nullable()->constrained('programme_types')->onDelete('set null');
            $table->foreignId('grading_scheme_id')->nullable()->constrained('grading_schemes')->onDelete('set null');
            $table->foreignId('assessment_strategy_id')->nullable()->constrained('assessment_strategies')->onDelete('set null');
            $table->foreignId('module_progression_rule_id')->nullable()->constrained('module_progression_rules')->onDelete('set null');
            
            // Programme-specific overrides (JSON to override defaults from linked configurations)
            $table->json('grading_overrides')->nullable(); // Override grading scheme settings
            $table->json('assessment_overrides')->nullable(); // Override assessment strategy settings
            $table->json('progression_overrides')->nullable(); // Override progression rule settings
            
            // Academic framework
            $table->string('awarding_body')->nullable(); // QQI, Oxford Brookes, etc.
            $table->string('nfq_level')->nullable(); // National Framework of Qualifications level
            $table->integer('credit_value')->nullable(); // Total credits for programme
            $table->decimal('minimum_pass_grade', 5, 2)->nullable(); // Programme-specific pass grade
            
            // Workflow configuration
            $table->boolean('requires_placement')->default(false);
            $table->boolean('requires_external_verification')->default(false);
            $table->boolean('requires_portfolio_submission')->default(false);
            
            // Delivery and scheduling
            $table->enum('delivery_mode', ['on_campus', 'online', 'blended', 'work_based'])->default('on_campus');
            $table->json('intake_schedule')->nullable(); // When new students can start
            $table->integer('typical_duration_months')->nullable();
            
            // Quality and compliance
            $table->boolean('external_examiner_required')->default(false);
            $table->boolean('professional_body_accredited')->default(false);
            $table->string('accreditation_body')->nullable();
            $table->date('last_accreditation_review')->nullable();
            $table->date('next_accreditation_review')->nullable();
            
            // Business rules
            $table->json('enrollment_rules')->nullable(); // Entry requirements, prerequisites
            $table->json('fee_structure')->nullable(); // Programme fees and payment rules
            $table->json('graduation_requirements')->nullable(); // What's needed to graduate
            
            // Add indexes for performance
            $table->index(['programme_type_id', 'is_active']);
            $table->index(['grading_scheme_id', 'is_active']);
            $table->index(['assessment_strategy_id', 'is_active']);
            $table->index(['delivery_mode', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropForeign(['programme_type_id']);
            $table->dropForeign(['grading_scheme_id']);
            $table->dropForeign(['assessment_strategy_id']);
            $table->dropForeign(['module_progression_rule_id']);
            
            $table->dropColumn([
                'programme_type_id',
                'grading_scheme_id',
                'assessment_strategy_id',
                'module_progression_rule_id',
                'grading_overrides',
                'assessment_overrides',
                'progression_overrides',
                'awarding_body',
                'nfq_level',
                'credit_value',
                'minimum_pass_grade',
                'requires_placement',
                'requires_external_verification',
                'requires_portfolio_submission',
                'delivery_mode',
                'intake_schedule',
                'typical_duration_months',
                'external_examiner_required',
                'professional_body_accredited',
                'accreditation_body',
                'last_accreditation_review',
                'next_accreditation_review',
                'enrollment_rules',
                'fee_structure',
                'graduation_requirements'
            ]);
        });
    }
};