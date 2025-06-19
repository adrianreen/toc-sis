<?php

namespace App\Console\Commands;

use App\Models\Programme;
use App\Services\GradingService;
use Illuminate\Console\Command;

class ValidateProgrammeConfigurations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'programme:validate-configurations {--programme= : Validate specific programme by ID} {--fix : Fix missing configurations where possible}';

    /**
     * The console command description.
     */
    protected $description = 'Validate programme configurations and report any issues';

    /**
     * Execute the console command.
     */
    public function handle(GradingService $gradingService)
    {
        $this->info('🔍 Validating programme configurations...');
        
        $query = Programme::with([
            'programmeType',
            'gradingScheme',
            'assessmentStrategy',
            'moduleProgressionRule'
        ]);
        
        if ($this->option('programme')) {
            $query->where('id', $this->option('programme'));
        }
        
        $programmes = $query->get();
        
        if ($programmes->isEmpty()) {
            $this->warn('No programmes found to validate.');
            return 0;
        }
        
        $totalProgrammes = $programmes->count();
        $validProgrammes = 0;
        $warningProgrammes = 0;
        $errorProgrammes = 0;
        
        $this->newLine();
        
        foreach ($programmes as $programme) {
            $this->info("📋 Validating: {$programme->code} - {$programme->title}");
            
            $validation = $gradingService->validateGradingConfiguration($programme);
            
            if ($validation['valid']) {
                $this->line("  ✅ Configuration is valid");
                $validProgrammes++;
            } else {
                $this->error("  ❌ Configuration has errors:");
                foreach ($validation['errors'] as $error) {
                    $this->line("     • {$error}");
                }
                $errorProgrammes++;
            }
            
            if (!empty($validation['warnings'])) {
                $this->warn("  ⚠️  Configuration warnings:");
                foreach ($validation['warnings'] as $warning) {
                    $this->line("     • {$warning}");
                }
                $warningProgrammes++;
            }
            
            // Show configuration status
            $this->line("  Configuration Status:");
            $this->line("     • Grading Scheme: " . ($validation['has_grading_scheme'] ? '✓' : '✗'));
            $this->line("     • Assessment Strategy: " . ($validation['has_assessment_strategy'] ? '✓' : '✗'));
            $this->line("     • Progression Rule: " . ($validation['has_progression_rule'] ? '✓' : '✗'));
            
            if ($this->option('fix') && !$validation['valid']) {
                $this->attemptToFixConfiguration($programme);
            }
            
            $this->newLine();
        }
        
        // Summary
        $this->info('📊 Validation Summary:');
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Valid', $validProgrammes, round(($validProgrammes / $totalProgrammes) * 100, 1) . '%'],
                ['Warnings', $warningProgrammes, round(($warningProgrammes / $totalProgrammes) * 100, 1) . '%'],
                ['Errors', $errorProgrammes, round(($errorProgrammes / $totalProgrammes) * 100, 1) . '%'],
                ['Total', $totalProgrammes, '100%'],
            ]
        );
        
        if ($errorProgrammes > 0) {
            $this->error('Some programmes have configuration errors. Use --fix to attempt automatic fixes.');
            return 1;
        }
        
        if ($warningProgrammes > 0) {
            $this->warn('Some programmes have configuration warnings.');
        }
        
        $this->info('✅ Validation completed successfully.');
        return 0;
    }
    
    private function attemptToFixConfiguration(Programme $programme): void
    {
        $this->line("  🔧 Attempting to fix configuration issues...");
        
        $fixed = false;
        
        // Try to assign programme type if missing
        if (!$programme->programme_type_id && !$programme->gradingScheme) {
            $defaultType = $this->suggestProgrammeType($programme);
            if ($defaultType) {
                $programme->update(['programme_type_id' => $defaultType->id]);
                $this->line("     ✓ Assigned programme type: {$defaultType->name}");
                $fixed = true;
            }
        }
        
        // Set minimum pass grade if missing
        if (!$programme->minimum_pass_grade) {
            $programme->update(['minimum_pass_grade' => 40.0]);
            $this->line("     ✓ Set minimum pass grade to 40%");
            $fixed = true;
        }
        
        if ($fixed) {
            $this->info("  ✅ Configuration fixes applied");
        } else {
            $this->warn("  ⚠️  No automatic fixes available");
        }
    }
    
    private function suggestProgrammeType(Programme $programme)
    {
        // Suggest based on NFQ level or code patterns
        if ($programme->nfq_level === '5') {
            return \App\Models\ProgrammeType::where('code', 'QQI5')->first();
        }
        
        if ($programme->nfq_level === '6') {
            return \App\Models\ProgrammeType::where('code', 'QQI6')->first();
        }
        
        if (in_array($programme->nfq_level, ['7', '8', '9', '10'])) {
            return \App\Models\ProgrammeType::where('code', 'DEGREE')->first();
        }
        
        // Default to QQI5
        return \App\Models\ProgrammeType::where('code', 'QQI5')->first();
    }
}