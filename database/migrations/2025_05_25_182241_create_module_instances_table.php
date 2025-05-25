<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('cohort_id')->nullable()->constrained()->onDelete('set null');
            $table->string('instance_code')->unique(); // ELC501-2501
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['planned', 'active', 'completed'])->default('planned');
            $table->json('settings')->nullable(); // For storing instance-specific settings
            $table->timestamps();
            
            $table->index(['module_id', 'cohort_id']);
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_instances');
    }
};