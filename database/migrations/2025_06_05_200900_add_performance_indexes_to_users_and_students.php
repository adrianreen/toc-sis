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
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index('status', 'idx_students_status');
        });

        // Legacy table indexes removed - new architecture has different schema
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_status');
        });

        // Legacy table indexes removed - new architecture has different schema
    }
};
