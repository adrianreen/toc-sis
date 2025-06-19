<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AssessmentStrategy extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'assessment_type',
        'typical_component_count',
        'default_component_types',
        'component_rules',
        'supports_resubmission',
        'max_resubmissions',
        'supports_extensions',
        'default_extension_days',
        'requires_moderation',
        'requires_external_examiner',
        'supports_draft_submissions',
        'progress_calculation',
        'allows_partial_completion',
        'repeat_strategy',
        'repeat_rules',
        'is_active',
    ];

    protected $casts = [
        'default_component_types' => 'array',
        'component_rules' => 'array',
        'supports_resubmission' => 'boolean',
        'supports_extensions' => 'boolean',
        'requires_moderation' => 'boolean',
        'requires_external_examiner' => 'boolean',
        'supports_draft_submissions' => 'boolean',
        'allows_partial_completion' => 'boolean',
        'repeat_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'code', 'assessment_type', 'typical_component_count',
                'supports_resubmission', 'requires_moderation', 'repeat_strategy', 'is_active'
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

    public function scopeByType($query, string $type)
    {
        return $query->where('assessment_type', $type);
    }

    public function scopeByProgressCalculation($query, string $calculation)
    {
        return $query->where('progress_calculation', $calculation);
    }

    public function scopeSupportsResubmission($query, bool $supports = true)
    {
        return $query->where('supports_resubmission', $supports);
    }

    // Assessment component generation
    public function generateDefaultComponents(array $moduleInfo = []): array
    {
        $components = [];
        $componentTypes = $this->default_component_types ?: $this->getDefaultComponentTypes();
        
        foreach ($componentTypes as $index => $componentData) {
            $components[] = [
                'name' => $componentData['name'] ?? "Assessment Component " . ($index + 1),
                'type' => $componentData['type'] ?? 'assignment',
                'weight' => $componentData['weight'] ?? (100 / count($componentTypes)),
                'sequence' => $index + 1,
                'description' => $componentData['description'] ?? null,
                'max_marks' => $componentData['max_marks'] ?? 100,
                'is_active' => true,
            ];
        }

        return $components;
    }

    private function getDefaultComponentTypes(): array
    {
        return match ($this->assessment_type) {
            'component_weighted' => [
                ['name' => 'Assignment 1', 'type' => 'assignment', 'weight' => 40],
                ['name' => 'Assignment 2', 'type' => 'assignment', 'weight' => 35],
                ['name' => 'Final Assessment', 'type' => 'exam', 'weight' => 25],
            ],
            'portfolio' => [
                ['name' => 'Portfolio Submission', 'type' => 'portfolio', 'weight' => 60],
                ['name' => 'Reflective Essay', 'type' => 'assignment', 'weight' => 40],
            ],
            'project_based' => [
                ['name' => 'Project Proposal', 'type' => 'assignment', 'weight' => 20],
                ['name' => 'Project Implementation', 'type' => 'project', 'weight' => 50],
                ['name' => 'Project Presentation', 'type' => 'presentation', 'weight' => 30],
            ],
            'competency' => [
                ['name' => 'Competency 1', 'type' => 'competency', 'weight' => 25],
                ['name' => 'Competency 2', 'type' => 'competency', 'weight' => 25],
                ['name' => 'Competency 3', 'type' => 'competency', 'weight' => 25],
                ['name' => 'Competency 4', 'type' => 'competency', 'weight' => 25],
            ],
            'cumulative' => [
                ['name' => 'Ongoing Assessment', 'type' => 'continuous', 'weight' => 70],
                ['name' => 'Final Assessment', 'type' => 'exam', 'weight' => 30],
            ],
            default => [
                ['name' => 'Assessment 1', 'type' => 'assignment', 'weight' => 50],
                ['name' => 'Assessment 2', 'type' => 'assignment', 'weight' => 50],
            ],
        };
    }

    // Progress calculation methods
    public function calculateProgress(array $assessmentStatuses, array $assessmentGrades = []): array
    {
        return match ($this->progress_calculation) {
            'all_complete' => $this->calculateAllComplete($assessmentStatuses),
            'weighted_completion' => $this->calculateWeightedCompletion($assessmentStatuses, $assessmentGrades),
            'milestone_based' => $this->calculateMilestoneBased($assessmentStatuses),
            'continuous' => $this->calculateContinuous($assessmentStatuses, $assessmentGrades),
            default => $this->calculateAllComplete($assessmentStatuses),
        };
    }

    private function calculateAllComplete(array $statuses): array
    {
        $total = count($statuses);
        $completed = count(array_filter($statuses, fn($status) => in_array($status, ['graded', 'passed', 'failed'])));
        
        return [
            'completion_percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'is_complete' => $completed === $total,
            'completed_count' => $completed,
            'total_count' => $total,
        ];
    }

    private function calculateWeightedCompletion(array $statuses, array $grades): array
    {
        $totalWeight = 0;
        $completedWeight = 0;
        
        foreach ($statuses as $index => $status) {
            $weight = $grades[$index]['weight'] ?? (100 / count($statuses));
            $totalWeight += $weight;
            
            if (in_array($status, ['graded', 'passed', 'failed'])) {
                $completedWeight += $weight;
            }
        }
        
        return [
            'completion_percentage' => $totalWeight > 0 ? round(($completedWeight / $totalWeight) * 100, 2) : 0,
            'is_complete' => $completedWeight >= $totalWeight,
            'completed_weight' => $completedWeight,
            'total_weight' => $totalWeight,
        ];
    }

    private function calculateMilestoneBased(array $statuses): array
    {
        // Find the highest milestone achieved (sequential completion)
        $highestMilestone = 0;
        
        foreach ($statuses as $index => $status) {
            if (in_array($status, ['graded', 'passed', 'failed'])) {
                $highestMilestone = $index + 1;
            } else {
                break; // Stop at first incomplete assessment
            }
        }
        
        return [
            'completion_percentage' => count($statuses) > 0 ? round(($highestMilestone / count($statuses)) * 100, 2) : 0,
            'is_complete' => $highestMilestone === count($statuses),
            'milestone_reached' => $highestMilestone,
            'total_milestones' => count($statuses),
        ];
    }

    private function calculateContinuous(array $statuses, array $grades): array
    {
        // For continuous assessment, consider partial submissions
        $totalProgress = 0;
        
        foreach ($statuses as $index => $status) {
            switch ($status) {
                case 'graded':
                case 'passed':
                case 'failed':
                    $totalProgress += 100;
                    break;
                case 'submitted':
                    $totalProgress += 80;
                    break;
                case 'in_progress':
                    $totalProgress += 50;
                    break;
                case 'pending':
                default:
                    $totalProgress += 0;
                    break;
            }
        }
        
        $averageProgress = count($statuses) > 0 ? $totalProgress / count($statuses) : 0;
        
        return [
            'completion_percentage' => round($averageProgress, 2),
            'is_complete' => $averageProgress >= 100,
            'average_progress' => $averageProgress,
        ];
    }

    // Resubmission and extension handling
    public function canResubmit(int $currentAttempts): bool
    {
        if (!$this->supports_resubmission) {
            return false;
        }
        
        if ($this->max_resubmissions === null) {
            return true; // Unlimited resubmissions
        }
        
        return $currentAttempts < $this->max_resubmissions;
    }

    public function canRequestExtension(): bool
    {
        return $this->supports_extensions;
    }

    public function getDefaultExtensionDays(): int
    {
        return $this->default_extension_days ?? 7;
    }

    // Repeat assessment rules
    public function getRepeatAssessmentStrategy(): string
    {
        return $this->repeat_strategy;
    }

    public function generateRepeatAssessmentPlan(array $failedComponents): array
    {
        $repeatRules = $this->repeat_rules ?: [];
        
        return match ($this->repeat_strategy) {
            'component_only' => $this->createComponentOnlyRepeat($failedComponents, $repeatRules),
            'full_module' => $this->createFullModuleRepeat($repeatRules),
            'portfolio_rebuild' => $this->createPortfolioRebuildRepeat($repeatRules),
            'custom' => $this->createCustomRepeat($failedComponents, $repeatRules),
            default => $this->createComponentOnlyRepeat($failedComponents, $repeatRules),
        };
    }

    private function createComponentOnlyRepeat(array $failedComponents, array $rules): array
    {
        return [
            'strategy' => 'component_only',
            'components_to_repeat' => $failedComponents,
            'deadline_extension_days' => $rules['deadline_extension_days'] ?? 30,
            'cap_grade' => $rules['cap_grade'] ?? 40,
            'fee_required' => $rules['fee_required'] ?? true,
            'fee_amount' => $rules['fee_amount'] ?? 50.00,
        ];
    }

    private function createFullModuleRepeat(array $rules): array
    {
        return [
            'strategy' => 'full_module',
            'repeat_all_components' => true,
            'deadline_extension_days' => $rules['deadline_extension_days'] ?? 60,
            'cap_grade' => $rules['cap_grade'] ?? 40,
            'fee_required' => $rules['fee_required'] ?? true,
            'fee_amount' => $rules['fee_amount'] ?? 200.00,
        ];
    }

    private function createPortfolioRebuildRepeat(array $rules): array
    {
        return [
            'strategy' => 'portfolio_rebuild',
            'rebuild_entire_portfolio' => true,
            'deadline_extension_days' => $rules['deadline_extension_days'] ?? 90,
            'cap_grade' => $rules['cap_grade'] ?? null, // No cap for portfolio rebuild
            'fee_required' => $rules['fee_required'] ?? true,
            'fee_amount' => $rules['fee_amount'] ?? 150.00,
        ];
    }

    private function createCustomRepeat(array $failedComponents, array $rules): array
    {
        return array_merge([
            'strategy' => 'custom',
            'components_to_repeat' => $failedComponents,
            'deadline_extension_days' => 30,
            'cap_grade' => 40,
            'fee_required' => true,
            'fee_amount' => 50.00,
        ], $rules);
    }

    // Quality assurance
    public function requiresModeration(): bool
    {
        return $this->requires_moderation;
    }

    public function requiresExternalExaminer(): bool
    {
        return $this->requires_external_examiner;
    }

    public function supportsDraftSubmissions(): bool
    {
        return $this->supports_draft_submissions;
    }

    // Component validation
    public function validateComponentConfiguration(array $components): array
    {
        $errors = [];
        $totalWeight = 0;
        
        foreach ($components as $component) {
            $totalWeight += $component['weight'] ?? 0;
        }
        
        if (abs($totalWeight - 100) > 0.01) {
            $errors[] = "Component weights must total 100% (currently {$totalWeight}%)";
        }
        
        if (count($components) < 1) {
            $errors[] = "At least one assessment component is required";
        }
        
        if ($this->typical_component_count && count($components) > ($this->typical_component_count * 2)) {
            $errors[] = "Too many components for this assessment strategy (recommended: {$this->typical_component_count})";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $this->getConfigurationWarnings($components),
        ];
    }

    private function getConfigurationWarnings(array $components): array
    {
        $warnings = [];
        
        if ($this->typical_component_count && count($components) !== $this->typical_component_count) {
            $warnings[] = "This strategy typically uses {$this->typical_component_count} components";
        }
        
        $componentTypes = array_column($components, 'type');
        $uniqueTypes = array_unique($componentTypes);
        
        if (count($uniqueTypes) === 1 && count($components) > 2) {
            $warnings[] = "Consider varying assessment types for better learning outcomes";
        }
        
        return $warnings;
    }

    // Static factory methods
    public static function createStandardStrategy(): self
    {
        return self::create([
            'name' => 'Standard Component-Weighted',
            'code' => 'STANDARD',
            'description' => 'Traditional weighted component assessment strategy',
            'assessment_type' => 'component_weighted',
            'typical_component_count' => 3,
            'supports_resubmission' => true,
            'max_resubmissions' => 1,
            'supports_extensions' => true,
            'default_extension_days' => 7,
            'requires_moderation' => false,
            'requires_external_examiner' => false,
            'supports_draft_submissions' => false,
            'progress_calculation' => 'all_complete',
            'allows_partial_completion' => true,
            'repeat_strategy' => 'component_only',
        ]);
    }

    public static function createPortfolioStrategy(): self
    {
        return self::create([
            'name' => 'Portfolio Assessment',
            'code' => 'PORTFOLIO',
            'description' => 'Portfolio-based assessment strategy',
            'assessment_type' => 'portfolio',
            'typical_component_count' => 2,
            'supports_resubmission' => true,
            'max_resubmissions' => 2,
            'supports_extensions' => true,
            'default_extension_days' => 14,
            'requires_moderation' => true,
            'requires_external_examiner' => false,
            'supports_draft_submissions' => true,
            'progress_calculation' => 'continuous',
            'allows_partial_completion' => true,
            'repeat_strategy' => 'portfolio_rebuild',
        ]);
    }

    public static function createProjectStrategy(): self
    {
        return self::create([
            'name' => 'Project-Based Assessment',
            'code' => 'PROJECT',
            'description' => 'Project-based assessment strategy',
            'assessment_type' => 'project_based',
            'typical_component_count' => 3,
            'supports_resubmission' => true,
            'max_resubmissions' => 1,
            'supports_extensions' => true,
            'default_extension_days' => 14,
            'requires_moderation' => true,
            'requires_external_examiner' => true,
            'supports_draft_submissions' => true,
            'progress_calculation' => 'milestone_based',
            'allows_partial_completion' => false,
            'repeat_strategy' => 'full_module',
        ]);
    }

    public static function createCompetencyStrategy(): self
    {
        return self::create([
            'name' => 'Competency-Based Assessment',
            'code' => 'COMPETENCY',
            'description' => 'Competency-based assessment strategy',
            'assessment_type' => 'competency',
            'typical_component_count' => 4,
            'supports_resubmission' => true,
            'max_resubmissions' => null, // Unlimited until competency achieved
            'supports_extensions' => true,
            'default_extension_days' => 30,
            'requires_moderation' => false,
            'requires_external_examiner' => false,
            'supports_draft_submissions' => true,
            'progress_calculation' => 'all_complete',
            'allows_partial_completion' => false,
            'repeat_strategy' => 'component_only',
        ]);
    }
}