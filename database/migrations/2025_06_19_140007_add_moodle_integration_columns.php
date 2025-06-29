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
            $table->unsignedBigInteger('moodle_user_id')->nullable()->after('email');
            $table->index('moodle_user_id');
        });

        Schema::table('module_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('moodle_course_id')->nullable()->after('delivery_style');
            $table->index('moodle_course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['moodle_user_id']);
            $table->dropColumn('moodle_user_id');
        });

        Schema::table('module_instances', function (Blueprint $table) {
            $table->dropIndex(['moodle_course_id']);
            $table->dropColumn('moodle_course_id');
        });
    }
};
