<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\StudentGradeRecord;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $data = null,
        ?\DateTime $scheduledFor = null
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
            'scheduled_for' => $scheduledFor,
        ]);

        // If not scheduled, send immediately
        if (! $scheduledFor) {
            $this->sendNotification($notification);
        }

        return $notification;
    }

    public function sendNotification(Notification $notification): void
    {
        try {
            $user = $notification->user;
            $preferences = $this->getUserPreferences($user, $notification->type);

            // Send email notification if enabled
            if ($preferences['email_enabled'] && ! $notification->email_sent) {
                $this->sendEmailNotification($notification);
            }

            // Log the notification creation
            Log::info('Notification created', [
                'user_id' => $user->id,
                'type' => $notification->type,
                'title' => $notification->title,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            // Don't re-throw - notification failures shouldn't break the app
        }
    }

    public function notifyApprovalRequired(User $staffUser, string $type, string $itemName, string $actionUrl): void
    {
        $title = "Approval Required: {$type}";
        $message = "A {$type} for {$itemName} requires your approval.";

        $this->createNotification(
            $staffUser,
            Notification::TYPE_APPROVAL_REQUIRED,
            $title,
            $message,
            $actionUrl,
            [
                'approval_type' => $type,
                'item_name' => $itemName,
            ]
        );
    }

    public function notifyExtensionApproved(User $user, string $assessmentName, \DateTime $newDueDate): void
    {
        $title = 'Extension Approved';
        $message = "Your extension request for '{$assessmentName}' has been approved. New due date: {$newDueDate->format('d M Y')}.";
        $actionUrl = route('students.assessments');

        $this->createNotification(
            $user,
            Notification::TYPE_EXTENSION_APPROVED,
            $title,
            $message,
            $actionUrl,
            [
                'assessment_name' => $assessmentName,
                'new_due_date' => $newDueDate->format('Y-m-d'),
            ]
        );
    }

    public function notifyDeferralApproved(User $user, string $programmeName): void
    {
        $title = 'Deferral Approved';
        $message = "Your deferral request for '{$programmeName}' has been approved.";
        $actionUrl = route('students.enrolments');

        $this->createNotification(
            $user,
            Notification::TYPE_DEFERRAL_APPROVED,
            $title,
            $message,
            $actionUrl,
            [
                'programme_name' => $programmeName,
            ]
        );
    }

    public function createAnnouncement(array $userIds, string $title, string $message, ?string $actionUrl = null): void
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->createNotification(
                    $user,
                    Notification::TYPE_ANNOUNCEMENT,
                    $title,
                    $message,
                    $actionUrl
                );
            }
        }
    }

    // ===============================================
    // NEW ARCHITECTURE METHODS - StudentGradeRecord
    // ===============================================

    public function notifyGradeReleasedV2(User $user, string $moduleName, string $assessmentName, float $grade): Notification
    {
        $title = "Grade Released: {$assessmentName}";
        $message = "Your grade for '{$assessmentName}' in {$moduleName} is now available. Grade: {$grade}%";
        $actionUrl = route('students.progress');

        return $this->createNotification(
            $user,
            Notification::TYPE_GRADE_RELEASED,
            $title,
            $message,
            $actionUrl,
            [
                'module_name' => $moduleName,
                'assessment_name' => $assessmentName,
                'grade' => $grade,
            ]
        );
    }

    public function notifyAssessmentDeadline(User $user, string $assessmentName, \DateTime $dueDate, string $moduleName, int $daysBeforeDue = 3): Notification
    {
        $title = "Assessment Due Soon: {$assessmentName}";
        $message = "Your assessment '{$assessmentName}' for {$moduleName} is due in {$daysBeforeDue} days on {$dueDate->format('d M Y')}.";
        $actionUrl = route('students.progress');

        return $this->createNotification(
            $user,
            Notification::TYPE_ASSESSMENT_DUE,
            $title,
            $message,
            $actionUrl,
            [
                'assessment_name' => $assessmentName,
                'module_name' => $moduleName,
                'days_before' => $daysBeforeDue,
                'due_date' => $dueDate->format('Y-m-d'),
            ]
        );
    }

    public function notifyStudentGradeRecord(StudentGradeRecord $gradeRecord): ?Notification
    {
        $student = $gradeRecord->student;
        $user = $student->user;

        if (! $user || ! $gradeRecord->is_visible_to_student) {
            return null;
        }

        $moduleInstance = $gradeRecord->moduleInstance;
        $module = $moduleInstance->module;

        $title = "Grade Released: {$gradeRecord->assessment_component_name}";
        $message = "Your grade for '{$gradeRecord->assessment_component_name}' in {$module->title} is now available.";

        if ($gradeRecord->grade !== null) {
            $message .= " Grade: {$gradeRecord->grade}%";
        }

        $actionUrl = route('students.progress');

        return $this->createNotification(
            $user,
            Notification::TYPE_GRADE_RELEASED,
            $title,
            $message,
            $actionUrl,
            [
                'grade_record_id' => $gradeRecord->id,
                'module_name' => $module->title,
                'assessment_name' => $gradeRecord->assessment_component_name,
                'grade' => $gradeRecord->grade,
            ]
        );
    }

    public function processScheduledNotifications(): int
    {
        $scheduledNotifications = Notification::scheduled()
            ->where('email_sent', false)
            ->get();

        $processed = 0;
        foreach ($scheduledNotifications as $notification) {
            try {
                $this->sendNotification($notification);
                $processed++;
            } catch (\Exception $e) {
                Log::error('Failed to process scheduled notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue processing other notifications
            }
        }

        return $processed;
    }

    private function getUserPreferences(User $user, string $notificationType): array
    {
        $preference = $user->notificationPreferences()
            ->where('notification_type', $notificationType)
            ->first();

        if ($preference) {
            return [
                'email_enabled' => $preference->email_enabled,
                'in_app_enabled' => $preference->in_app_enabled,
                'advance_days' => $preference->advance_days,
            ];
        }

        // Return default preferences
        $defaults = NotificationPreference::getDefaultPreferences();

        return $defaults[$notificationType] ?? [
            'email_enabled' => true,
            'in_app_enabled' => true,
            'advance_days' => 3,
        ];
    }

    private function sendEmailNotification(Notification $notification): void
    {
        try {
            // For now, we'll use a simple mail send
            // In production, you'd want to use proper Mail templates
            Mail::raw($notification->message, function ($message) use ($notification) {
                $message->to($notification->user->email)
                    ->subject($notification->title);
            });

            $notification->update(['email_sent' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyCourseExtensionApproved(User $user, string $courseName, \DateTime $newCompletionDate): void
    {
        $title = 'Extension Request Approved';
        $message = "Your extension request for '{$courseName}' has been approved. New completion date: ".$newCompletionDate->format('F j, Y');
        $actionUrl = route('extension-requests.index');

        $this->createNotification(
            $user,
            'extension_approved',
            $title,
            $message,
            $actionUrl,
            [
                'course_name' => $courseName,
                'new_completion_date' => $newCompletionDate->format('Y-m-d'),
            ]
        );
    }

    public function notifyRepeatAssessmentRequired(User $user, $repeatAssessment): void
    {
        $this->createNotification(
            $user,
            'repeat_assessment_required',
            'Repeat Assessment Required',
            "You need to complete a repeat assessment for {$repeatAssessment->assessment_component_name}. ".
            "Payment of €{$repeatAssessment->payment_amount} is required before you can proceed.",
            route('students.grades'), // Student grades view route
            [
                'repeat_assessment_id' => $repeatAssessment->id,
                'assessment_name' => $repeatAssessment->assessment_component_name,
                'module_name' => $repeatAssessment->moduleInstance->module->name,
                'payment_amount' => $repeatAssessment->payment_amount,
                'due_date' => $repeatAssessment->repeat_due_date->format('Y-m-d'),
                'deadline_date' => $repeatAssessment->deadline_date->format('Y-m-d'),
            ]
        );
    }

    public function notifyRepeatAssessmentPaymentReceived(User $user, $repeatAssessment): void
    {
        $this->createNotification(
            $user,
            'repeat_assessment_payment_received',
            'Repeat Assessment Payment Received',
            "Your payment for the repeat assessment of {$repeatAssessment->assessment_component_name} has been received. ".
            'Your repeat assessment will be set up shortly.',
            route('students.grades'),
            [
                'repeat_assessment_id' => $repeatAssessment->id,
                'assessment_name' => $repeatAssessment->assessment_component_name,
                'payment_amount' => $repeatAssessment->payment_amount,
                'payment_method' => $repeatAssessment->payment_method,
            ]
        );
    }

    public function notifyRepeatAssessmentReady(User $user, $repeatAssessment): void
    {
        $this->createNotification(
            $user,
            'repeat_assessment_ready',
            'Repeat Assessment Ready',
            "Your repeat assessment for {$repeatAssessment->assessment_component_name} is now ready. ".
            'You can access it through your student portal.',
            route('students.grades'),
            [
                'repeat_assessment_id' => $repeatAssessment->id,
                'assessment_name' => $repeatAssessment->assessment_component_name,
                'due_date' => $repeatAssessment->repeat_due_date->format('Y-m-d'),
                'moodle_course_id' => $repeatAssessment->moodle_course_id,
            ]
        );
    }

    public function notifyRepeatAssessmentReminderStaff(User $staffUser, $repeatAssessment): void
    {
        $studentName = $repeatAssessment->student->full_name;
        $assessmentName = $repeatAssessment->assessment_component_name;

        $this->createNotification(
            $staffUser,
            'repeat_assessment_reminder_staff',
            'Repeat Assessment Action Required',
            "Repeat assessment for {$studentName} - {$assessmentName} requires attention. ".
            "Status: {$repeatAssessment->workflow_stage}, Priority: {$repeatAssessment->priority_level}",
            route('repeat-assessments.show', $repeatAssessment),
            [
                'repeat_assessment_id' => $repeatAssessment->id,
                'student_name' => $studentName,
                'assessment_name' => $assessmentName,
                'workflow_stage' => $repeatAssessment->workflow_stage,
                'priority_level' => $repeatAssessment->priority_level,
                'deadline_date' => $repeatAssessment->deadline_date->format('Y-m-d'),
            ]
        );
    }

    public function initializeUserPreferences(User $user): void
    {
        $defaults = NotificationPreference::getDefaultPreferences();

        foreach ($defaults as $type => $settings) {
            NotificationPreference::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_type' => $type,
                ],
                $settings
            );
        }
    }
}
