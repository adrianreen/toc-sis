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
        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('enrolment_type', ['programme', 'module']);
            $table->foreignId('programme_instance_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('module_instance_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('enrolment_date');
            $table->enum('status', ['active', 'completed', 'withdrawn', 'deferred'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['student_id', 'enrolment_type']);
            $table->index('programme_instance_id');
            $table->index('module_instance_id');
            
            // Note: Check constraint logic will be enforced at application level
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrolments');
    }
};
