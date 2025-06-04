<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\StudentAssessment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        if (!$scheduledFor) {
            $this->sendNotification($notification);
        }

        return $notification;
    }

    public function sendNotification(Notification $notification): void
    {
        $user = $notification->user;
        $preferences = $this->getUserPreferences($user, $notification->type);

        // Send email notification if enabled
        if ($preferences['email_enabled'] && !$notification->email_sent) {
            $this->sendEmailNotification($notification);
        }

        // Log the notification creation
        Log::info('Notification created', [
            'user_id' => $user->id,
            'type' => $notification->type,
            'title' => $notification->title
        ]);
    }

    public function notifyAssessmentDue(StudentAssessment $assessment, int $daysBeforeDue = 3): void
    {
        $student = $assessment->studentModuleEnrolment->student;
        $user = $student->user;

        if (!$user) {
            return;
        }

        $module = $assessment->studentModuleEnrolment->moduleInstance->module;
        $title = "Assessment Due Soon: {$assessment->assessmentComponent->name}";
        $message = "Your assessment '{$assessment->assessmentComponent->name}' for {$module->title} is due in {$daysBeforeDue} days on {$assessment->due_date->format('d M Y')}.";
        $actionUrl = route('students.assessments');

        $this->createNotification(
            $user,
            Notification::TYPE_ASSESSMENT_DUE,
            $title,
            $message,
            $actionUrl,
            [
                'assessment_id' => $assessment->id,
                'days_before' => $daysBeforeDue,
                'due_date' => $assessment->due_date->toISOString()
            ]
        );
    }

    public function notifyGradeReleased(StudentAssessment $assessment): void
    {
        $student = $assessment->studentModuleEnrolment->student;
        $user = $student->user;

        if (!$user || !$assessment->isVisibleToStudent()) {
            return;
        }

        $module = $assessment->studentModuleEnrolment->moduleInstance->module;
        $title = "Grade Released: {$assessment->assessmentComponent->name}";
        $message = "Your grade for '{$assessment->assessmentComponent->name}' in {$module->title} is now available.";
        $actionUrl = route('students.progress');

        $this->createNotification(
            $user,
            Notification::TYPE_GRADE_RELEASED,
            $title,
            $message,
            $actionUrl,
            [
                'assessment_id' => $assessment->id,
                'grade' => $assessment->grade,
                'status' => $assessment->status
            ]
        );
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
                'item_name' => $itemName
            ]
        );
    }

    public function notifyExtensionApproved(User $user, string $assessmentName, \DateTime $newDueDate): void
    {
        $title = "Extension Approved";
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
                'new_due_date' => $newDueDate->format('Y-m-d')
            ]
        );
    }

    public function notifyDeferralApproved(User $user, string $programmeName): void
    {
        $title = "Deferral Approved";
        $message = "Your deferral request for '{$programmeName}' has been approved.";
        $actionUrl = route('students.enrolments');

        $this->createNotification(
            $user,
            Notification::TYPE_DEFERRAL_APPROVED,
            $title,
            $message,
            $actionUrl,
            [
                'programme_name' => $programmeName
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

    public function processScheduledNotifications(): int
    {
        $scheduledNotifications = Notification::scheduled()
            ->where('email_sent', false)
            ->get();

        $processed = 0;
        foreach ($scheduledNotifications as $notification) {
            $this->sendNotification($notification);
            $processed++;
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
                'advance_days' => $preference->advance_days
            ];
        }

        // Return default preferences
        $defaults = NotificationPreference::getDefaultPreferences();
        return $defaults[$notificationType] ?? [
            'email_enabled' => true,
            'in_app_enabled' => true,
            'advance_days' => 3
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
                'error' => $e->getMessage()
            ]);
        }
    }

    public function initializeUserPreferences(User $user): void
    {
        $defaults = NotificationPreference::getDefaultPreferences();

        foreach ($defaults as $type => $settings) {
            NotificationPreference::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_type' => $type
                ],
                $settings
            );
        }
    }
}