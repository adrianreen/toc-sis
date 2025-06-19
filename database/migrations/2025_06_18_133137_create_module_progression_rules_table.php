<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_progression_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Sequential Progression, Credit Accumulation, Flexible Progression, etc.
            $table->string('code')->unique(); // SEQUENTIAL, CREDIT, FLEXIBLE, MIXED
            $table->text('description')->nullable();
            
            // Progression strategy
            $table->enum('progression_type', ['sequential', 'credit_based', 'flexible', 'competency_ladder', 'milestone']);
            
            // Module dependency rules
            $table->boolean('requires_previous_completion')->default(true); // Must complete previous modules
            $table->boolean('allows_concurrent_modules')->default(false); // Can take multiple modules simultaneously
            $table->boolean('supports_module_prerequisites')->default(true); // Individual module prerequisites
            
            // Credit and accumulation rules
            $table->integer('minimum_credits_per_period')->nullable(); // Minimum credits to take per term/semester
            $table->integer('maximum_credits_per_period')->nullable(); // Maximum credits to take per term/semester
            $table->decimal('minimum_gpa_to_progress', 5, 2)->nullable(); // Minimum GPA required to continue
            
            // Failure and repeat handling
            $table->enum('failure_action', ['repeat_module', 'repeat_components', 'repeat_programme', 'compensation', 'custom']);
            $table->boolean('allows_compensation')->default(true); // Can compensate failed modules with higher grades
            $table->decimal('compensation_threshold', 5, 2)->nullable(); // Minimum grade required for compensation
            $table->integer('max_compensation_modules')->nullable(); // Maximum modules that can be compensated
            
            // Progression blocking rules
            $table->json('blocking_rules')->nullable(); // Conditions that prevent progression
            $table->boolean('blocks_on_failed_placement')->default(true); // Placement failure blocks progression
            $table->boolean('blocks_on_unpaid_fees')->default(false); // Unpaid fees block progression
            
            // Completion requirements
            $table->boolean('requires_all_modules_passed')->default(true); // Must pass every module
            $table->decimal('overall_programme_threshold', 5, 2)->nullable(); // Overall programme pass mark
            $table->json('completion_criteria')->nullable(); // Additional completion requirements
            
            // Time limits and extensions
            $table->integer('maximum_duration_months')->nullable(); // Programme time limit
            $table->boolean('supports_programme_extensions')->default(true);
            $table->integer('default_extension_months')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['code', 'progression_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_progression_rules');
    }
};