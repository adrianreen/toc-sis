<?php

namespace App\Console\Commands;

use App\Models\StudentAssessment;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendAssessmentDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:assessment-reminders {--days=3,7 : Days before due date to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send assessment deadline reminder notifications';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysArray = explode(',', $this->option('days'));
        $sentCount = 0;

        foreach ($daysArray as $days) {
            $days = (int) trim($days);
            $targetDate = Carbon::now()->addDays($days)->startOfDay();
            
            $this->info("Checking for assessments due in {$days} days ({$targetDate->format('Y-m-d')})...");

            // Find assessments due on the target date that haven't been notified
            $assessments = StudentAssessment::whereDate('due_date', $targetDate)
                ->where('status', 'pending')
                ->whereHas('studentModuleEnrolment.student.user')
                ->get();

            foreach ($assessments as $assessment) {
                // Check if we've already sent a notification for this assessment and timeframe
                $existingNotification = Notification::where('user_id', $assessment->studentModuleEnrolment->student->user->id)
                    ->where('type', Notification::TYPE_ASSESSMENT_DUE)
                    ->whereJsonContains('data->assessment_id', $assessment->id)
                    ->whereJsonContains('data->days_before', $days)
                    ->exists();

                if (!$existingNotification) {
                    $this->notificationService->notifyAssessmentDue($assessment, $days);
                    $sentCount++;
                    
                    $studentName = $assessment->studentModuleEnrolment->student->user->name;
                    $assessmentName = $assessment->assessmentComponent->name;
                    $this->line("  ✓ Sent reminder to {$studentName} for {$assessmentName}");
                }
            }
        }

        $this->info("Sent {$sentCount} assessment deadline reminders.");

        return 0;
    }
}
