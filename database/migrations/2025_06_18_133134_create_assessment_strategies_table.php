<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_strategies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard Portfolio, Project-Based, Competency Assessment, etc.
            $table->string('code')->unique(); // STANDARD, PROJECT, COMPETENCY, PORTFOLIO
            $table->text('description')->nullable();
            
            // Assessment structure
            $table->enum('assessment_type', ['component_weighted', 'portfolio', 'project_based', 'competency', 'cumulative']);
            $table->integer('typical_component_count')->nullable(); // Suggested number of assessment components
            
            // Component configuration
            $table->json('default_component_types')->nullable(); // Default component types and weights
            $table->json('component_rules')->nullable(); // Rules for component creation and weighting
            
            // Submission and deadline handling
            $table->boolean('supports_resubmission')->default(true);
            $table->integer('max_resubmissions')->nullable();
            $table->boolean('supports_extensions')->default(true);
            $table->integer('default_extension_days')->nullable();
            
            // Grading workflow
            $table->boolean('requires_moderation')->default(false);
            $table->boolean('requires_external_examiner')->default(false);
            $table->boolean('supports_draft_submissions')->default(false);
            
            // Progress tracking
            $table->enum('progress_calculation', ['all_complete', 'weighted_completion', 'milestone_based', 'continuous']);
            $table->boolean('allows_partial_completion')->default(true);
            
            // Repeat assessment rules
            $table->enum('repeat_strategy', ['component_only', 'full_module', 'portfolio_rebuild', 'custom']);
            $table->json('repeat_rules')->nullable(); // Specific rules for repeat assessments
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['code', 'assessment_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_strategies');
    }
};