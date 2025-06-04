<?php

namespace App\Console\Commands;

use App\Models\StudentAssessment;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ReleaseScheduledAssessments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'assessments:release-scheduled 
                          {--dry-run : Show what would be released without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Release assessments that are scheduled for automatic release';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for scheduled assessment releases...');
        
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Find assessments ready for release
        $assessments = StudentAssessment::where('is_visible_to_student', false)
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now())
            ->whereNotNull('grade')
            ->with([
                'studentModuleEnrolment.student',
                'studentModuleEnrolment.moduleInstance.module',
                'assessmentComponent'
            ])
            ->get();

        if ($assessments->count() === 0) {
            $this->info('✅ No assessments ready for release');
            return Command::SUCCESS;
        }

        $this->info("Found {$assessments->count()} assessment(s) ready for release:");

        $releasedCount = 0;
        
        foreach ($assessments as $assessment) {
            $student = $assessment->studentModuleEnrolment->student;
            $module = $assessment->studentModuleEnrolment->moduleInstance->module;
            $component = $assessment->assessmentComponent;
            
            $this->line("  📋 {$student->student_number} - {$module->code} - {$component->name} (Grade: {$assessment->grade}%)");
            
            if (!$dryRun) {
                $assessment->update([
                    'is_visible_to_student' => true,
                    'visibility_changed_at' => now(),
                ]);

                activity()
                    ->performedOn($assessment)
                    ->log('Assessment auto-released on schedule');

                // Send grade release notification
                $this->notificationService->notifyGradeReleased($assessment);

                $releasedCount++;
            }
        }

        if ($dryRun) {
            $this->warn("DRY RUN COMPLETE - {$assessments->count()} assessments would be released");
            $this->info('Run without --dry-run to apply these changes');
        } else {
            $this->info("✅ Successfully released {$releasedCount} assessments");
            
            // Log summary
            \Log::info("Auto-released {$releasedCount} scheduled assessments", [
                'released_count' => $releasedCount,
                'released_at' => now(),
            ]);
        }

        return Command::SUCCESS;
    }
}