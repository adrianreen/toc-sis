<?php

namespace App\Console\Commands;

use App\Models\StudentGradeRecord;
use App\Services\NotificationService;
use Illuminate\Console\Command;

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

        // Find grade records ready for release
        $gradeRecords = StudentGradeRecord::where('is_visible_to_student', false)
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now())
            ->whereNotNull('grade')
            ->with([
                'student.user',
                'moduleInstance.module',
            ])
            ->get();

        if ($gradeRecords->count() === 0) {
            $this->info('✅ No grade records ready for release');

            return Command::SUCCESS;
        }

        $this->info("Found {$gradeRecords->count()} grade record(s) ready for release:");

        $releasedCount = 0;

        foreach ($gradeRecords as $gradeRecord) {
            $student = $gradeRecord->student;
            $module = $gradeRecord->moduleInstance->module;

            $this->line("  📋 {$student->student_number} - {$module->code} - {$gradeRecord->assessment_component_name} (Grade: {$gradeRecord->grade}%)");

            if (! $dryRun) {
                $gradeRecord->update([
                    'is_visible_to_student' => true,
                ]);

                activity()
                    ->performedOn($gradeRecord)
                    ->log('Grade record auto-released on schedule');

                // Send grade release notification using new architecture method
                $this->notificationService->notifyStudentGradeRecord($gradeRecord);

                $releasedCount++;
            }
        }

        if ($dryRun) {
            $this->warn("DRY RUN COMPLETE - {$gradeRecords->count()} grade records would be released");
            $this->info('Run without --dry-run to apply these changes');
        } else {
            $this->info("✅ Successfully released {$releasedCount} grade records");

            // Log summary
            \Log::info("Auto-released {$releasedCount} scheduled grade records", [
                'released_count' => $releasedCount,
                'released_at' => now(),
            ]);
        }

        return Command::SUCCESS;
    }
}
