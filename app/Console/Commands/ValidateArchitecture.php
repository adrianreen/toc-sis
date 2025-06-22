<?php

namespace App\Console\Commands;

use App\Services\ArchitectureValidationService;
use Illuminate\Console\Command;

class ValidateArchitecture extends Command
{
    protected $signature = 'architecture:validate 
                            {--fix : Attempt to auto-fix common issues}
                            {--stats : Show detailed statistics}
                            {--programme= : Validate specific programme instance by ID}';

    protected $description = 'Comprehensive validation of the 4-level Programme-Module architecture';

    private $validationService;

    public function __construct(ArchitectureValidationService $validationService)
    {
        parent::__construct();
        $this->validationService = $validationService;
    }

    public function handle()
    {
        $this->info('🏗️  Validating 4-Level Programme-Module Architecture...');
        $this->newLine();

        // Specific programme validation
        if ($this->option('programme')) {
            return $this->validateSpecificProgramme();
        }

        // Auto-fix if requested
        if ($this->option('fix')) {
            $this->info('🔧 Attempting to auto-fix common issues...');
            $fixResults = $this->validationService->autoFixIssues();
            
            foreach ($fixResults['fixed'] as $fix) {
                $this->info("✅ {$fix}");
            }
            
            foreach ($fixResults['failed'] as $failure) {
                $this->error("❌ {$failure}");
            }
            
            $this->newLine();
        }

        // Run comprehensive validation
        $results = $this->validationService->validateEntireArchitecture();

        // Display results
        $this->displayValidationResults($results);

        return $results['valid'] ? 0 : 1;
    }

    private function validateSpecificProgramme()
    {
        $programmeId = $this->option('programme');
        
        try {
            $programme = \App\Models\ProgrammeInstance::findOrFail($programmeId);
            $results = $this->validationService->validateProgrammeCurriculum($programme);
            
            $this->info("Validating Programme Instance: {$programme->programme->title} - {$programme->label}");
            $this->newLine();
            
            if (empty($results['errors']) && empty($results['warnings'])) {
                $this->info('✅ Programme curriculum is valid!');
            } else {
                foreach ($results['errors'] as $error) {
                    $this->error("❌ ERROR: {$error}");
                }
                
                foreach ($results['warnings'] as $warning) {
                    $this->warn("⚠️  WARNING: {$warning}");
                }
            }
            
            return empty($results['errors']) ? 0 : 1;
            
        } catch (\Exception $e) {
            $this->error("Programme Instance ID {$programmeId} not found");
            return 1;
        }
    }

    private function displayValidationResults(array $results)
    {
        // System Statistics
        if ($this->option('stats') || !empty($results['errors'])) {
            $this->displaySystemStats($results['stats']);
        }

        // Errors
        if (!empty($results['errors'])) {
            $this->error('❌ VALIDATION ERRORS FOUND:');
            $this->newLine();
            
            foreach ($results['errors'] as $error) {
                $this->line("   • {$error}");
            }
            $this->newLine();
        }

        // Warnings
        if (!empty($results['warnings'])) {
            $this->warn('⚠️  WARNINGS:');
            $this->newLine();
            
            foreach ($results['warnings'] as $warning) {
                $this->line("   • {$warning}");
            }
            $this->newLine();
        }

        // Final status
        if ($results['valid']) {
            $this->info('✅ Architecture validation PASSED!');
            $this->info('   All critical validation checks successful.');
            
            if (!empty($results['warnings'])) {
                $this->warn('   However, ' . count($results['warnings']) . ' warnings were found.');
            }
        } else {
            $this->error('❌ Architecture validation FAILED!');
            $this->error('   ' . count($results['errors']) . ' critical errors must be resolved.');
            
            $this->newLine();
            $this->info('💡 Try running with --fix to automatically resolve common issues:');
            $this->line('   php artisan architecture:validate --fix');
        }
    }

    private function displaySystemStats(array $stats)
    {
        $this->info('📊 SYSTEM STATISTICS:');
        $this->newLine();

        $this->table(
            ['Component', 'Count'],
            [
                ['Programmes (Blueprints)', $stats['programmes']],
                ['Programme Instances (Live)', $stats['programme_instances']],
                ['Modules (Blueprints)', $stats['modules']],
                ['Module Instances (Live)', $stats['module_instances']],
                ['Total Enrolments', $stats['total_enrolments']],
                ['  └─ Active Enrolments', $stats['active_enrolments']],
                ['  └─ Programme Enrolments', $stats['programme_enrolments']],
                ['  └─ Module Enrolments', $stats['module_enrolments']],
                ['Grade Records', $stats['grade_records']],
                ['  └─ With Grades', $stats['graded_records']],
                ['Curriculum Links', $stats['curriculum_links']],
            ]
        );

        $this->newLine();
    }
}