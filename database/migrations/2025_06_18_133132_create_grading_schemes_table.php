<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Percentage Grading, Direct Component Grading, Classifications, etc.
            $table->string('code')->unique(); // PERCENTAGE, DIRECT, CLASSIFICATION, COMPETENCY
            $table->text('description')->nullable();
            
            // Grading system type
            $table->enum('type', ['percentage', 'direct', 'classification', 'competency', 'points']);
            
            // Calculation method
            $table->enum('calculation_method', ['weighted_average', 'points_total', 'grade_boundaries', 'competency_based']);
            
            // Grade boundaries and mappings
            $table->json('grade_boundaries')->nullable(); // e.g., [{"min": 70, "max": 100, "grade": "A", "classification": "First Class"}]
            $table->json('grade_mappings')->nullable(); // Additional mapping configurations
            
            // Component handling
            $table->boolean('components_graded_out_of_total')->default(true); // false = graded out of component max
            $table->boolean('all_components_required')->default(false); // must pass all components
            $table->decimal('component_pass_threshold', 5, 2)->nullable(); // individual component pass mark
            
            // Overall pass requirements
            $table->decimal('overall_pass_threshold', 5, 2)->default(40.00);
            $table->boolean('compensatory_grading_allowed')->default(true); // can compensate failed components
            $table->decimal('compensation_threshold', 5, 2)->nullable(); // minimum grade for compensation
            
            // Display formatting
            $table->json('display_format')->nullable(); // How grades are shown to students/staff
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['code', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_schemes');
    }
};