<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_grade_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_instance_id')->constrained()->onDelete('cascade');
            $table->string('assessment_component_name');
            $table->decimal('grade', 5, 2)->nullable();
            $table->decimal('max_grade', 5, 2)->default(100);
            $table->text('feedback')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('graded_date')->nullable();
            $table->foreignId('graded_by_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_visible_to_student')->default(false);
            $table->date('release_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['student_id', 'module_instance_id']);
            $table->index(['module_instance_id', 'assessment_component_name'], 'sgr_module_component_idx');
            $table->index('is_visible_to_student');
            $table->index('release_date');
            
            $table->unique(['student_id', 'module_instance_id', 'assessment_component_name'], 'student_module_component_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grade_records');
    }
};
