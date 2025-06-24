<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $this->warn('⚠️  Assessment deadline reminders may not be needed with new architecture.');
        $this->info('   Students submit assessments via Moodle, not the SIS.');
        $this->info('   This command is maintained for compatibility but may be deprecated.');

        $daysArray = explode(',', $this->option('days'));
        $sentCount = 0;

        $this->info('Checking for grade records with future submission dates...');

        // Since the new architecture uses StudentGradeRecord and students submit via Moodle,
        // we'll look for any assessment components that might have deadline tracking
        // This is mainly for compatibility - most notifications will come from Moodle integration

        foreach ($daysArray as $days) {
            $days = (int) trim($days);
            $targetDate = Carbon::now()->addDays($days)->startOfDay();

            $this->info("Would check for assessments due in {$days} days ({$targetDate->format('Y-m-d')})...");

            // In new architecture, we don't track individual assessment due dates in the SIS
            // Students get deadline reminders from Moodle directly
            // This could be enhanced later if needed for specific use cases
        }

        $this->info("Assessment deadline reminders: {$sentCount} notifications sent.");
        $this->info('💡 Most assessment reminders now come from Moodle integration.');

        return 0;
    }
}
