<?php

namespace App\Observers;

use App\Models\StudentAssessment;
use App\Models\RepeatAssessment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class StudentAssessmentObserver
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the StudentAssessment "updated" event.
     * Automatically create repeat assessment when an assessment fails
     */
    public function updated(StudentAssessment $studentAssessment): void
    {
        // Only proceed if grade or status changed
        if (!$studentAssessment->isDirty(['grade', 'status'])) {
            return;
        }

        // Check if this assessment has failed (grade < 40 or status = 'failed')
        $hasFailed = ($studentAssessment->status === 'failed') || 
                     ($studentAssessment->grade !== null && $studentAssessment->grade < 40);

        // Only create repeat assessment if:
        // 1. Assessment has failed
        // 2. Assessment is graded (has a grade)
        // 3. No repeat assessment already exists for this assessment
        if ($hasFailed && 
            $studentAssessment->grade !== null && 
            !$studentAssessment->repeatAssessments()->exists()) {
            
            $this->createRepeatAssessment($studentAssessment);
        }
    }

    /**
     * Create a repeat assessment for a failed student assessment
     */
    protected function createRepeatAssessment(StudentAssessment $studentAssessment): void
    {
        try {
            \DB::transaction(function () use ($studentAssessment) {
                // Get related data
                $student = $studentAssessment->studentModuleEnrolment->student;
                $moduleInstance = $studentAssessment->studentModuleEnrolment->moduleInstance;
                $module = $moduleInstance->module;

                // Calculate repeat due date (default: 8 weeks from now)
                $repeatDueDate = now()->addWeeks(8);

                // Create the repeat assessment record
                $repeatAssessment = RepeatAssessment::create([
                    'student_assessment_id' => $studentAssessment->id,
                    'student_id' => $student->id,
                    'module_instance_id' => $moduleInstance->id,
                    'reason' => "Failed assessment - Grade: {$studentAssessment->grade}%",
                    'repeat_due_date' => $repeatDueDate,
                    'cap_grade' => 40, // Standard repeat grade cap
                    'status' => 'pending',
                    'payment_amount' => 150.00, // Default repeat fee
                    'payment_status' => 'pending',
                    'priority' => 'normal',
                    'workflow_stage' => 'identified',
                ]);

                // Send notification to student if they have a user account
                if ($student->user) {
                    try {
                        $this->notificationService->notifyRepeatAssessmentRequired(
                            $student->user,
                            $repeatAssessment
                        );

                        // Mark as notified
                        $repeatAssessment->update([
                            'student_notified' => true,
                            'student_notified_date' => now(),
                            'notification_method' => 'email',
                        ]);

                    } catch (\Exception $notificationError) {
                        // Log notification failure but don't fail the whole process
                        Log::warning('Failed to send repeat assessment notification', [
                            'repeat_assessment_id' => $repeatAssessment->id,
                            'student_id' => $student->id,
                            'error' => $notificationError->getMessage()
                        ]);
                    }
                }

                // Log the automatic creation
                activity()
                    ->performedOn($repeatAssessment)
                    ->causedBy(null) // System-generated
                    ->withProperties([
                        'student_assessment_id' => $studentAssessment->id,
                        'original_grade' => $studentAssessment->grade,
                        'module_title' => $module->title,
                        'auto_created' => true,
                    ])
                    ->log('Repeat assessment automatically created for failed assessment');

                Log::info('Repeat assessment automatically created', [
                    'repeat_assessment_id' => $repeatAssessment->id,
                    'student_id' => $student->id,
                    'student_assessment_id' => $studentAssessment->id,
                    'original_grade' => $studentAssessment->grade,
                    'module' => $module->title,
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Failed to create automatic repeat assessment', [
                'student_assessment_id' => $studentAssessment->id,
                'student_id' => $studentAssessment->studentModuleEnrolment->student_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Determine if auto-creation should be enabled
     * This can be controlled via config or environment variable
     */
    protected function shouldAutoCreateRepeatAssessments(): bool
    {
        return config('app.auto_create_repeat_assessments', true);
    }
}