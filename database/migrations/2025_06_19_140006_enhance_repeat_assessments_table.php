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
        Schema::table('repeat_assessments', function (Blueprint $table) {
            // Payment tracking fields
            $table->enum('payment_status', ['pending', 'paid', 'waived', 'overdue'])->default('pending')->after('status');
            $table->enum('payment_method', ['online', 'bank_transfer', 'cheque', 'cash', 'waived'])->nullable()->after('payment_status');
            $table->decimal('payment_amount', 8, 2)->nullable()->after('payment_method');
            $table->date('payment_date')->nullable()->after('payment_amount');
            $table->text('payment_notes')->nullable()->after('payment_date');

            // Notification tracking fields
            $table->boolean('notification_sent')->default(false)->after('payment_notes');
            $table->timestamp('notification_date')->nullable()->after('notification_sent');
            $table->enum('notification_method', ['email', 'post', 'phone', 'in_person'])->nullable()->after('notification_date');
            $table->text('notification_notes')->nullable()->after('notification_method');

            // Moodle integration fields
            $table->enum('moodle_setup_status', ['pending', 'in_progress', 'completed', 'failed', 'not_required'])->default('pending')->after('notification_notes');
            $table->timestamp('moodle_setup_date')->nullable()->after('moodle_setup_status');
            $table->string('moodle_course_id')->nullable()->after('moodle_setup_date');
            $table->text('moodle_notes')->nullable()->after('moodle_course_id');

            // Workflow management fields
            $table->enum('workflow_stage', ['identified', 'notified', 'payment_pending', 'moodle_setup', 'active', 'completed', 'cancelled'])->default('identified')->after('moodle_notes');
            $table->date('deadline_date')->nullable()->after('workflow_stage');
            $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('deadline_date');
            $table->text('staff_notes')->nullable()->after('priority_level');

            // Student communication fields
            $table->text('student_response')->nullable()->after('staff_notes');
            $table->timestamp('student_response_date')->nullable()->after('student_response');

            // Additional tracking
            $table->foreignId('assigned_to')->nullable()->constrained('users')->after('approved_by'); // Staff member handling the case
            $table->timestamp('last_contact_date')->nullable()->after('assigned_to');
            $table->text('contact_history')->nullable()->after('last_contact_date'); // JSON field for contact log

            // Add indexes for better performance
            $table->index(['payment_status', 'workflow_stage']);
            $table->index(['notification_sent', 'moodle_setup_status']);
            $table->index(['assigned_to', 'priority_level']);
            $table->index(['deadline_date', 'workflow_stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repeat_assessments', function (Blueprint $table) {
            // Remove indexes first
            $table->dropIndex(['deadline_date', 'workflow_stage']);
            $table->dropIndex(['assigned_to', 'priority_level']);
            $table->dropIndex(['notification_sent', 'moodle_setup_status']);
            $table->dropIndex(['payment_status', 'workflow_stage']);

            // Remove foreign key constraints
            $table->dropForeign(['assigned_to']);

            // Remove all added columns
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'payment_amount',
                'payment_date',
                'payment_notes',
                'notification_sent',
                'notification_date',
                'notification_method',
                'notification_notes',
                'moodle_setup_status',
                'moodle_setup_date',
                'moodle_course_id',
                'moodle_notes',
                'workflow_stage',
                'deadline_date',
                'priority_level',
                'staff_notes',
                'student_response',
                'student_response_date',
                'assigned_to',
                'last_contact_date',
                'contact_history',
            ]);
        });
    }
};
