<?php

namespace App\Console\Commands;

use App\Models\Enrolment;
use App\Models\ModuleInstance;
use App\Services\EnrolmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStudentAssessments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'assessments:sync 
                          {--student-id= : Sync assessments for specific student}
                          {--cohort-id= : Sync assessments for specific cohort}
                          {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Sync student assessments - ensures all enrolled students have assessment records';

    protected $enrolmentService;

    public function __construct(EnrolmentService $enrolmentService)
    {
        parent::__construct();
        $this->enrolmentService = $enrolmentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->error('❌ This command is deprecated and incompatible with the new 4-level architecture.');
        $this->info('   Please use the EnrolmentService methods instead.');
        return Command::FAILURE;
        
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $stats = [
            'students_processed' => 0,
            'module_enrolments_created' => 0,
            'assessments_created' => 0,
            'errors' => 0,
        ];

        try {
            // Get enrollments to process
            $enrolments = $this->getEnrolmentsToProcess();
            
            $this->info("Found {$enrolments->count()} student enrolments to process");

            $bar = $this->output->createProgressBar($enrolments->count());
            $bar->start();

            foreach ($enrolments as $enrolment) {
                try {
                    $result = $this->processEnrolment($enrolment, $dryRun);
                    $stats['students_processed']++;
                    $stats['module_enrolments_created'] += $result['module_enrolments'];
                    $stats['assessments_created'] += $result['assessments'];
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->error("\nError processing student {$enrolment->student->student_number}: " . $e->getMessage());
                }
                
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Display results
            $this->displayResults($stats, $dryRun);

        } catch (\Exception $e) {
            $this->error('Command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Get enrolments that need processing
     */
    protected function getEnrolmentsToProcess()
    {
        $query = Enrolment::with(['student', 'programme', 'cohort'])
            ->where('status', 'active')
            ->whereNotNull('cohort_id'); // Only cohort-based programmes

        if ($studentId = $this->option('student-id')) {
            $query->where('student_id', $studentId);
        }

        if ($cohortId = $this->option('cohort-id')) {
            $query->where('cohort_id', $cohortId);
        }

        return $query->get();
    }

    /**
     * Process a single enrolment
     */
    protected function processEnrolment(Enrolment $enrolment, bool $dryRun): array
    {
        $student = $enrolment->student;
        $cohort = $enrolment->cohort;
        
        $stats = ['module_enrolments' => 0, 'assessments' => 0];

        // Get module instances for this cohort
        $moduleInstances = ModuleInstance::where('cohort_id', $cohort->id)
            ->with(['module.assessmentComponents'])
            ->get();

        foreach ($moduleInstances as $moduleInstance) {
            // Check if student is already enrolled in this module instance
            $existingEnrolment = $student->studentModuleEnrolments()
                ->where('module_instance_id', $moduleInstance->id)
                ->first();

            if (!$existingEnrolment) {
                if (!$dryRun) {
                    $this->enrolmentService->enrolInSingleModuleInstance($student, $enrolment, $moduleInstance);
                }
                $stats['module_enrolments']++;
                
                // Count assessments that would be created
                $assessmentCount = $moduleInstance->module->assessmentComponents()
                    ->where('is_active', true)
                    ->count();
                $stats['assessments'] += $assessmentCount;
                
                $this->line("\n  ✓ Enrolled {$student->student_number} in {$moduleInstance->instance_code}");
            } else {
                // Check if all assessments exist
                $missingAssessments = $this->checkMissingAssessments($existingEnrolment, $moduleInstance);
                if ($missingAssessments > 0) {
                    if (!$dryRun) {
                        $this->createMissingAssessments($existingEnrolment, $moduleInstance);
                    }
                    $stats['assessments'] += $missingAssessments;
                    $this->line("\n  ✓ Created {$missingAssessments} missing assessments for {$student->student_number} in {$moduleInstance->instance_code}");
                }
            }
        }

        return $stats;
    }

    /**
     * Check for missing assessments
     */
    protected function checkMissingAssessments($studentModuleEnrolment, $moduleInstance): int
    {
        $totalComponents = $moduleInstance->module->assessmentComponents()
            ->where('is_active', true)
            ->count();

        $existingAssessments = $studentModuleEnrolment->studentAssessments()->count();

        return max(0, $totalComponents - $existingAssessments);
    }

    /**
     * Create missing assessments
     */
    protected function createMissingAssessments($studentModuleEnrolment, $moduleInstance): void
    {
        $existingComponentIds = $studentModuleEnrolment->studentAssessments()
            ->pluck('assessment_component_id')
            ->toArray();

        $missingComponents = $moduleInstance->module->assessmentComponents()
            ->where('is_active', true)
            ->whereNotIn('id', $existingComponentIds)
            ->get();

        foreach ($missingComponents as $component) {
            $dueDate = $this->enrolmentService->calculateDueDate($moduleInstance, $component);

            $studentModuleEnrolment->studentAssessments()->create([
                'assessment_component_id' => $component->id,
                'attempt_number' => 1,
                'status' => 'pending',
                'due_date' => $dueDate,
            ]);
        }
    }

    /**
     * Display command results
     */
    protected function displayResults(array $stats, bool $dryRun): void
    {
        $this->info('📊 Sync Results:');
        $this->table(['Metric', 'Count'], [
            ['Students Processed', $stats['students_processed']],
            ['Module Enrolments Created', $stats['module_enrolments_created']],
            ['Assessments Created', $stats['assessments_created']],
            ['Errors', $stats['errors']],
        ]);

        if ($dryRun) {
            $this->warn('DRY RUN COMPLETE - No actual changes were made');
            $this->info('Run without --dry-run to apply these changes');
        } else {
            $this->info('✅ Assessment sync completed successfully!');
        }

        if ($stats['errors'] > 0) {
            $this->error("⚠️  {$stats['errors']} errors occurred during processing");
        }
    }
}