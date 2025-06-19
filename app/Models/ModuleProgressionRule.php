<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ModuleProgressionRule extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'progression_type',
        'requires_previous_completion',
        'allows_concurrent_modules',
        'supports_module_prerequisites',
        'minimum_credits_per_period',
        'maximum_credits_per_period',
        'minimum_gpa_to_progress',
        'failure_action',
        'allows_compensation',
        'compensation_threshold',
        'max_compensation_modules',
        'blocking_rules',
        'blocks_on_failed_placement',
        'blocks_on_unpaid_fees',
        'requires_all_modules_passed',
        'overall_programme_threshold',
        'completion_criteria',
        'maximum_duration_months',
        'supports_programme_extensions',
        'default_extension_months',
        'is_active',
    ];

    protected $casts = [
        'requires_previous_completion' => 'boolean',
        'allows_concurrent_modules' => 'boolean',
        'supports_module_prerequisites' => 'boolean',
        'minimum_gpa_to_progress' => 'decimal:2',
        'allows_compensation' => 'boolean',
        'compensation_threshold' => 'decimal:2',
        'blocking_rules' => 'array',
        'blocks_on_failed_placement' => 'boolean',
        'blocks_on_unpaid_fees' => 'boolean',
        'requires_all_modules_passed' => 'boolean',
        'overall_programme_threshold' => 'decimal:2',
        'completion_criteria' => 'array',
        'supports_programme_extensions' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'code', 'progression_type', 'requires_previous_completion',
                'allows_concurrent_modules', 'failure_action', 'allows_compensation',
                'requires_all_modules_passed', 'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByProgressionType($query, string $type)
    {
        return $query->where('progression_type', $type);
    }

    public function scopeSequential($query)
    {
        return $query->where('progression_type', 'sequential');
    }

    public function scopeCreditBased($query)
    {
        return $query->where('progression_type', 'credit_based');
    }

    public function scopeFlexible($query)
    {
        return $query->where('progression_type', 'flexible');
    }

    // Progression logic methods
    public function canProgressToNextModule(array $studentModuleHistory): array
    {
        return match ($this->progression_type) {
            'sequential' => $this->validateSequentialProgression($studentModuleHistory),
            'credit_based' => $this->validateCreditBasedProgression($studentModuleHistory),
            'flexible' => $this->validateFlexibleProgression($studentModuleHistory),
            'competency_ladder' => $this->validateCompetencyProgression($studentModuleHistory),
            'milestone' => $this->validateMilestoneProgression($studentModuleHistory),
            default => $this->validateSequentialProgression($studentModuleHistory),
        };
    }

    private function validateSequentialProgression(array $moduleHistory): array
    {
        $canProgress = true;
        $blockingReasons = [];
        
        if ($this->requires_previous_completion) {
            $lastModule = end($moduleHistory);
            if ($lastModule && $lastModule['status'] !== 'completed') {
                $canProgress = false;
                $blockingReasons[] = 'Previous module must be completed';
            }
        }
        
        // Check for concurrent module restrictions
        if (!$this->allows_concurrent_modules) {
            $activeModules = array_filter($moduleHistory, fn($m) => $m['status'] === 'active');
            if (count($activeModules) > 0) {
                $canProgress = false;
                $blockingReasons[] = 'Cannot take concurrent modules';
            }
        }
        
        return [
            'can_progress' => $canProgress,
            'blocking_reasons' => $blockingReasons,
            'progression_type' => 'sequential',
        ];
    }

    private function validateCreditBasedProgression(array $moduleHistory): array
    {
        $canProgress = true;
        $blockingReasons = [];
        $warnings = [];
        
        // Calculate current credits
        $completedCredits = array_sum(array_column(
            array_filter($moduleHistory, fn($m) => $m['status'] === 'completed'),
            'credits'
        ));
        
        $activeCredits = array_sum(array_column(
            array_filter($moduleHistory, fn($m) => $m['status'] === 'active'),
            'credits'
        ));
        
        // Check minimum credit requirements
        if ($this->minimum_credits_per_period && $activeCredits < $this->minimum_credits_per_period) {
            $warnings[] = "Below minimum credits per period ({$this->minimum_credits_per_period})";
        }
        
        // Check maximum credit restrictions
        if ($this->maximum_credits_per_period && $activeCredits > $this->maximum_credits_per_period) {
            $canProgress = false;
            $blockingReasons[] = "Exceeds maximum credits per period ({$this->maximum_credits_per_period})";
        }
        
        // Check GPA requirements
        if ($this->minimum_gpa_to_progress) {
            $gpa = $this->calculateGPA($moduleHistory);
            if ($gpa < $this->minimum_gpa_to_progress) {
                $canProgress = false;
                $blockingReasons[] = "GPA below minimum requirement ({$this->minimum_gpa_to_progress})";
            }
        }
        
        return [
            'can_progress' => $canProgress,
            'blocking_reasons' => $blockingReasons,
            'warnings' => $warnings,
            'progression_type' => 'credit_based',
            'completed_credits' => $completedCredits,
            'active_credits' => $activeCredits,
        ];
    }

    private function validateFlexibleProgression(array $moduleHistory): array
    {
        $canProgress = true;
        $blockingReasons = [];
        
        // Flexible progression has minimal restrictions
        // Check only for blocking rules if configured
        if ($this->blocking_rules) {
            foreach ($this->blocking_rules as $rule) {
                $violation = $this->evaluateBlockingRule($rule, $moduleHistory);
                if ($violation) {
                    $canProgress = false;
                    $blockingReasons[] = $violation;
                }
            }
        }
        
        return [
            'can_progress' => $canProgress,
            'blocking_reasons' => $blockingReasons,
            'progression_type' => 'flexible',
        ];
    }

    private function validateCompetencyProgression(array $moduleHistory): array
    {
        $canProgress = true;
        $blockingReasons = [];
        
        // Check if all competencies in current level are achieved
        $currentLevelModules = array_filter($moduleHistory, fn($m) => 
            $m['level'] === $this->getCurrentCompetencyLevel($moduleHistory)
        );
        
        foreach ($currentLevelModules as $module) {
            if ($module['status'] !== 'completed' || $module['competency_achieved'] !== true) {
                $canProgress = false;
                $blockingReasons[] = 'All competencies in current level must be achieved';
                break;
            }
        }
        
        return [
            'can_progress' => $canProgress,
            'blocking_reasons' => $blockingReasons,
            'progression_type' => 'competency_ladder',
        ];
    }

    private function validateMilestoneProgression(array $moduleHistory): array
    {
        $canProgress = true;
        $blockingReasons = [];
        
        // Check if required milestones are completed
        $requiredMilestones = $this->getRequiredMilestones();
        foreach ($requiredMilestones as $milestone) {
            $milestoneCompleted = $this->checkMilestoneCompletion($milestone, $moduleHistory);
            if (!$milestoneCompleted) {
                $canProgress = false;
                $blockingReasons[] = "Milestone '{$milestone['name']}' must be completed";
            }
        }
        
        return [
            'can_progress' => $canProgress,
            'blocking_reasons' => $blockingReasons,
            'progression_type' => 'milestone',
        ];
    }

    // Failure handling methods
    public function handleModuleFailure(array $failedModule, array $studentHistory): array
    {
        return match ($this->failure_action) {
            'repeat_module' => $this->createModuleRepeatPlan($failedModule),
            'repeat_components' => $this->createComponentRepeatPlan($failedModule),
            'repeat_programme' => $this->createProgrammeRepeatPlan($studentHistory),
            'compensation' => $this->evaluateCompensationOptions($failedModule, $studentHistory),
            'custom' => $this->createCustomFailureResponse($failedModule, $studentHistory),
            default => $this->createModuleRepeatPlan($failedModule),
        };
    }

    private function createModuleRepeatPlan(array $failedModule): array
    {
        return [
            'action' => 'repeat_module',
            'module_id' => $failedModule['id'],
            'repeat_all_components' => true,
            'fee_required' => true,
            'deadline_extension' => '60 days',
            'grade_cap' => 40,
        ];
    }

    private function createComponentRepeatPlan(array $failedModule): array
    {
        $failedComponents = array_filter(
            $failedModule['components'] ?? [],
            fn($c) => ($c['grade'] ?? 0) < ($c['pass_threshold'] ?? 40)
        );
        
        return [
            'action' => 'repeat_components',
            'module_id' => $failedModule['id'],
            'components_to_repeat' => array_column($failedComponents, 'id'),
            'fee_required' => true,
            'deadline_extension' => '30 days',
            'grade_cap' => 40,
        ];
    }

    private function createProgrammeRepeatPlan(array $studentHistory): array
    {
        return [
            'action' => 'repeat_programme',
            'restart_from_beginning' => true,
            'transfer_credits' => false,
            'fee_required' => true,
            'extended_timeline' => true,
        ];
    }

    private function evaluateCompensationOptions(array $failedModule, array $studentHistory): array
    {
        if (!$this->allows_compensation) {
            return ['compensation_available' => false];
        }
        
        $compensatedModules = count(array_filter(
            $studentHistory,
            fn($m) => $m['compensation_applied'] ?? false
        ));
        
        if ($compensatedModules >= ($this->max_compensation_modules ?? 1)) {
            return [
                'compensation_available' => false,
                'reason' => 'Maximum compensation modules exceeded',
            ];
        }
        
        $grade = $failedModule['final_grade'] ?? 0;
        if ($grade < $this->compensation_threshold) {
            return [
                'compensation_available' => false,
                'reason' => 'Grade below compensation threshold',
            ];
        }
        
        return [
            'compensation_available' => true,
            'action' => 'compensate',
            'module_id' => $failedModule['id'],
            'compensation_grade' => $this->compensation_threshold,
        ];
    }

    private function createCustomFailureResponse(array $failedModule, array $studentHistory): array
    {
        // Custom logic would be implemented based on specific institutional requirements
        return [
            'action' => 'custom',
            'requires_review' => true,
            'escalate_to' => 'academic_committee',
        ];
    }

    // Programme completion validation
    public function validateProgrammeCompletion(array $studentModuleHistory): array
    {
        $isComplete = true;
        $missingRequirements = [];
        
        // Check if all modules are passed
        if ($this->requires_all_modules_passed) {
            $failedModules = array_filter(
                $studentModuleHistory,
                fn($m) => $m['status'] === 'failed' && !($m['compensation_applied'] ?? false)
            );
            
            if (!empty($failedModules)) {
                $isComplete = false;
                $missingRequirements[] = 'All modules must be passed';
            }
        }
        
        // Check overall programme threshold
        if ($this->overall_programme_threshold) {
            $overallGrade = $this->calculateOverallGrade($studentModuleHistory);
            if ($overallGrade < $this->overall_programme_threshold) {
                $isComplete = false;
                $missingRequirements[] = "Overall grade below threshold ({$this->overall_programme_threshold}%)";
            }
        }
        
        // Check additional completion criteria
        if ($this->completion_criteria) {
            foreach ($this->completion_criteria as $criterion) {
                $met = $this->evaluateCompletionCriterion($criterion, $studentModuleHistory);
                if (!$met) {
                    $isComplete = false;
                    $missingRequirements[] = $criterion['description'] ?? 'Additional requirement not met';
                }
            }
        }
        
        return [
            'is_complete' => $isComplete,
            'missing_requirements' => $missingRequirements,
            'overall_grade' => $this->calculateOverallGrade($studentModuleHistory),
        ];
    }

    // Helper methods
    private function calculateGPA(array $moduleHistory): float
    {
        $completedModules = array_filter($moduleHistory, fn($m) => $m['status'] === 'completed');
        
        if (empty($completedModules)) {
            return 0.0;
        }
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($completedModules as $module) {
            $grade = $module['final_grade'] ?? 0;
            $credits = $module['credits'] ?? 1;
            
            $totalPoints += ($grade * $credits);
            $totalCredits += $credits;
        }
        
        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0.0;
    }

    private function calculateOverallGrade(array $moduleHistory): float
    {
        $completedModules = array_filter($moduleHistory, fn($m) => 
            $m['status'] === 'completed' || ($m['compensation_applied'] ?? false)
        );
        
        if (empty($completedModules)) {
            return 0.0;
        }
        
        $totalWeightedGrade = 0;
        $totalCredits = 0;
        
        foreach ($completedModules as $module) {
            $grade = $module['final_grade'] ?? 0;
            $credits = $module['credits'] ?? 1;
            
            // Use compensation grade if applied
            if ($module['compensation_applied'] ?? false) {
                $grade = $this->compensation_threshold;
            }
            
            $totalWeightedGrade += ($grade * $credits);
            $totalCredits += $credits;
        }
        
        return $totalCredits > 0 ? $totalWeightedGrade / $totalCredits : 0.0;
    }

    private function evaluateBlockingRule(array $rule, array $moduleHistory): ?string
    {
        // Implement blocking rule evaluation logic
        return null; // Placeholder
    }

    private function getCurrentCompetencyLevel(array $moduleHistory): int
    {
        // Implement competency level calculation
        return 1; // Placeholder
    }

    private function getRequiredMilestones(): array
    {
        // Return required milestones for progression
        return []; // Placeholder
    }

    private function checkMilestoneCompletion(array $milestone, array $moduleHistory): bool
    {
        // Check if milestone is completed
        return true; // Placeholder
    }

    private function evaluateCompletionCriterion(array $criterion, array $moduleHistory): bool
    {
        // Evaluate completion criterion
        return true; // Placeholder
    }

    // Static factory methods
    public static function createSequentialRule(): self
    {
        return self::create([
            'name' => 'Sequential Progression',
            'code' => 'SEQUENTIAL',
            'description' => 'Students must complete modules in sequence',
            'progression_type' => 'sequential',
            'requires_previous_completion' => true,
            'allows_concurrent_modules' => false,
            'supports_module_prerequisites' => true,
            'failure_action' => 'repeat_module',
            'allows_compensation' => true,
            'compensation_threshold' => 35.00,
            'max_compensation_modules' => 1,
            'blocks_on_failed_placement' => true,
            'blocks_on_unpaid_fees' => false,
            'requires_all_modules_passed' => true,
            'overall_programme_threshold' => 40.00,
            'maximum_duration_months' => 24,
            'supports_programme_extensions' => true,
            'default_extension_months' => 6,
        ]);
    }

    public static function createCreditBasedRule(): self
    {
        return self::create([
            'name' => 'Credit-Based Progression',
            'code' => 'CREDIT',
            'description' => 'Students progress based on credit accumulation',
            'progression_type' => 'credit_based',
            'requires_previous_completion' => false,
            'allows_concurrent_modules' => true,
            'supports_module_prerequisites' => true,
            'minimum_credits_per_period' => 60,
            'maximum_credits_per_period' => 180,
            'minimum_gpa_to_progress' => 2.0,
            'failure_action' => 'compensation',
            'allows_compensation' => true,
            'compensation_threshold' => 35.00,
            'max_compensation_modules' => 2,
            'blocks_on_failed_placement' => false,
            'blocks_on_unpaid_fees' => true,
            'requires_all_modules_passed' => false,
            'overall_programme_threshold' => 40.00,
            'maximum_duration_months' => 48,
            'supports_programme_extensions' => true,
            'default_extension_months' => 12,
        ]);
    }

    public static function createFlexibleRule(): self
    {
        return self::create([
            'name' => 'Flexible Progression',
            'code' => 'FLEXIBLE',
            'description' => 'Students can progress with minimal restrictions',
            'progression_type' => 'flexible',
            'requires_previous_completion' => false,
            'allows_concurrent_modules' => true,
            'supports_module_prerequisites' => false,
            'failure_action' => 'repeat_components',
            'allows_compensation' => true,
            'compensation_threshold' => 30.00,
            'max_compensation_modules' => 3,
            'blocks_on_failed_placement' => false,
            'blocks_on_unpaid_fees' => false,
            'requires_all_modules_passed' => false,
            'overall_programme_threshold' => 35.00,
            'maximum_duration_months' => 60,
            'supports_programme_extensions' => true,
            'default_extension_months' => 12,
        ]);
    }
}