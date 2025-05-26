<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_module_enrolment_id')->constrained('student_module_enrolments')->onDelete('cascade');
            $table->foreignId('assessment_component_id')->constrained()->onDelete('cascade');
            $table->integer('attempt_number')->default(1);
            $table->decimal('grade', 5, 2)->nullable();
            $table->enum('status', ['pending', 'submitted', 'graded', 'failed', 'passed'])->default('pending');
            $table->date('due_date');
            $table->date('submission_date')->nullable();
            $table->date('graded_date')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users');
            $table->text('feedback')->nullable();
            $table->timestamps();
            
            $table->unique(['student_module_enrolment_id', 'assessment_component_id', 'attempt_number'], 'student_assessment_attempt_unique');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_assessments');
    }
};