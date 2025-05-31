<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_assessments', function (Blueprint $table) {
            // Manual visibility control
            $table->boolean('is_visible_to_student')->default(false)->after('feedback');
            
            // Automatic release datetime
            $table->timestamp('release_date')->nullable()->after('is_visible_to_student');
            
            // Who manually changed visibility and when
            $table->foreignId('visibility_changed_by')->nullable()->constrained('users')->after('release_date');
            $table->timestamp('visibility_changed_at')->nullable()->after('visibility_changed_by');
            
            // Optional: release notes/message for students
            $table->text('release_notes')->nullable()->after('visibility_changed_at');
            
            $table->index(['is_visible_to_student', 'release_date']);
        });
    }

    public function down(): void
    {
        Schema::table('student_assessments', function (Blueprint $table) {
            $table->dropForeign(['visibility_changed_by']);
            $table->dropIndex(['is_visible_to_student', 'release_date']);
            $table->dropColumn([
                'is_visible_to_student',
                'release_date', 
                'visibility_changed_by',
                'visibility_changed_at',
                'release_notes'
            ]);
        });
    }
};