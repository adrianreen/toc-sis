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
        Schema::table('students', function (Blueprint $table) {
            // Individual indexes for exact matches (fastest)
            $table->index('student_number', 'idx_students_number');
            $table->index('email', 'idx_students_email');

            // Composite indexes for name searches
            $table->index(['first_name', 'last_name'], 'idx_students_name');
            $table->index(['last_name', 'first_name'], 'idx_students_name_reverse');

            // General search index for status filtering
            $table->index(['status', 'created_at'], 'idx_students_status_created');

            // Soft delete index for active records only
            $table->index(['deleted_at', 'status'], 'idx_students_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_number');
            $table->dropIndex('idx_students_email');
            $table->dropIndex('idx_students_name');
            $table->dropIndex('idx_students_name_reverse');
            $table->dropIndex('idx_students_status_created');
            $table->dropIndex('idx_students_active');
        });
    }
};
