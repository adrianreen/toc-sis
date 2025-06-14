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
        Schema::table('student_module_enrolments', function (Blueprint $table) {
            $table->boolean('is_final_grade_visible')->default(true)->after('final_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_module_enrolments', function (Blueprint $table) {
            $table->dropColumn('is_final_grade_visible');
        });
    }
};
