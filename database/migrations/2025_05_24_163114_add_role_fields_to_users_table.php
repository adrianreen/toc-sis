<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('azure_id')->unique()->nullable()->after('id');
            $table->enum('role', ['student', 'teacher', 'student_services', 'manager'])->default('student')->after('email');
            $table->json('azure_groups')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['azure_id', 'role', 'azure_groups', 'last_login_at']);
        });
    }
};
