<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Assignment 1", "Final Exam"
            $table->enum('type', ['assignment', 'exam', 'project', 'presentation', 'other']);
            $table->decimal('weight', 5, 2); // Percentage weight in final grade
            $table->integer('sequence')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['module_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_components');
    }
};