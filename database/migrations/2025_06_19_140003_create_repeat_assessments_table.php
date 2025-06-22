<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repeat_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_grade_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_instance_id')->constrained()->onDelete('cascade');
            $table->text('reason'); // Why repeat is needed
            $table->date('repeat_due_date');
            $table->decimal('cap_grade', 5, 2)->nullable(); // Maximum achievable grade
            $table->enum('status', ['pending', 'approved', 'submitted', 'graded', 'passed', 'failed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repeat_assessments');
    }
};