<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_module_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrolment_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_instance_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['enrolled', 'active', 'completed', 'failed', 'deferred', 'withdrawn'])->default('enrolled');
            $table->integer('attempt_number')->default(1);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'module_instance_id', 'attempt_number'], 'student_module_attempt_unique');
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_module_enrolments');
    }
};