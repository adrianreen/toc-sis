<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deferrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrolment_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_cohort_id')->nullable()->constrained('cohorts');
            $table->foreignId('to_cohort_id')->nullable()->constrained('cohorts');
            $table->date('deferral_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'returned', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deferrals');
    }
};