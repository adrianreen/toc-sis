<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GradingScheme extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'calculation_method',
        'grade_boundaries',
        'grade_mappings',
        'components_graded_out_of_total',
        'all_components_required',
        'component_pass_threshold',
        'overall_pass_threshold',
        'compensatory_grading_allowed',
        'compensation_threshold',
        'display_format',
        'is_active',
    ];

    protected $casts = [
        'grade_boundaries' => 'array',
        'grade_mappings' => 'array',
        'components_graded_out_of_total' => 'boolean',
        'all_components_required' => 'boolean',
        'component_pass_threshold' => 'decimal:2',
        'overall_pass_threshold' => 'decimal:2',
        'compensatory_grading_allowed' => 'boolean',
        'compensation_threshold' => 'decimal:2',
        'display_format' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'code', 'type', 'calculation_method',
                'overall_pass_threshold', 'all_components_required',
                'compensatory_grading_allowed', 'is_active'
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
        return $query->where('type', $type);
    }

    public function scopeByCalculationMethod($query, string $method)
    {
        return $query->where('calculation_method', $method);
    }

    // Grade calculation methods
    public function calculateFinalGrade(array $componentGrades, array $componentWeights = null): array
    {
        switch ($this->calculation_method) {
            case 'weighted_average':
                return $this->calculateWeightedAverage($componentGrades, $componentWeights);
            case 'points_total':
                return $this->calculatePointsTotal($componentGrades);
            case 'grade_boundaries':
                return $this->calculateWithBoundaries($componentGrades, $componentWeights);
            case 'competency_based':
                return $this->calculateCompetencyBased($componentGrades);
            default:
                return $this->calculateWeightedAverage($componentGrades, $componentWeights);
        }
    }

    private function calculateWeightedAverage(array $componentGrades, array $componentWeights = null): array
    {
        $totalWeightedScore = 0;
        $totalWeight = 0;
        $passedComponents = 0;
        $failedComponents = [];

        foreach ($componentGrades as $index => $grade) {
            if ($grade === null) continue;

            $weight = $componentWeights[$index] ?? (100 / count($componentGrades));
            $totalWeightedScore += ($grade * $weight / 100);
            $totalWeight += $weight;

            // Check component pass requirement
            if ($this->all_components_required && $this->component_pass_threshold) {
                if ($grade >= $this->component_pass_threshold) {
                    $passedComponents++;
                } else {
                    $failedComponents[] = $index;
                }
            }
        }

        $finalGrade = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) * 100 : 0;

        // Apply component pass requirements
        $passed = $finalGrade >= $this->overall_pass_threshold;
        if ($this->all_components_required && !empty($failedComponents)) {
            $passed = false;
        }

        // Apply compensation rules
        if (!$passed && $this->compensatory_grading_allowed && $this->compensation_threshold) {
            $passed = $finalGrade >= $this->compensation_threshold && count($failedComponents) <= 1;
        }

        return [
            'final_grade' => round($finalGrade, 2),
            'passed' => $passed,
            'failed_components' => $failedComponents,
            'compensation_applied' => !$passed && $this->compensatory_grading_allowed,
            'grade_display' => $this->formatGradeForDisplay($finalGrade, $passed),
        ];
    }

    private function calculatePointsTotal(array $componentGrades): array
    {
        $totalPoints = array_sum(array_filter($componentGrades));
        $maxPoints = count(array_filter($componentGrades)) * 100; // Assuming 100 points max per component
        
        $finalGrade = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
        $passed = $finalGrade >= $this->overall_pass_threshold;

        return [
            'final_grade' => round($finalGrade, 2),
            'total_points' => $totalPoints,
            'max_points' => $maxPoints,
            'passed' => $passed,
            'grade_display' => $this->formatGradeForDisplay($finalGrade, $passed),
        ];
    }

    private function calculateWithBoundaries(array $componentGrades, array $componentWeights = null): array
    {
        $result = $this->calculateWeightedAverage($componentGrades, $componentWeights);
        
        // Apply grade boundaries if configured
        if ($this->grade_boundaries) {
            foreach ($this->grade_boundaries as $boundary) {
                if ($result['final_grade'] >= $boundary['min'] && $result['final_grade'] <= $boundary['max']) {
                    $result['classification'] = $boundary['grade'] ?? $boundary['classification'] ?? null;
                    break;
                }
            }
        }

        return $result;
    }

    private function calculateCompetencyBased(array $componentGrades): array
    {
        $competenciesAchieved = 0;
        $totalCompetencies = count($componentGrades);
        
        foreach ($componentGrades as $grade) {
            if ($grade !== null && $grade >= ($this->component_pass_threshold ?? 40)) {
                $competenciesAchieved++;
            }
        }

        $completionPercentage = $totalCompetencies > 0 ? ($competenciesAchieved / $totalCompetencies) * 100 : 0;
        $passed = $competenciesAchieved === $totalCompetencies; // All competencies must be achieved

        return [
            'competencies_achieved' => $competenciesAchieved,
            'total_competencies' => $totalCompetencies,
            'completion_percentage' => round($completionPercentage, 2),
            'passed' => $passed,
            'grade_display' => $passed ? 'Competent' : 'Not Yet Competent',
        ];
    }

    public function formatGradeForDisplay(float $grade, bool $passed): string
    {
        if ($this->display_format) {
            $format = $this->display_format;
            
            if (isset($format['show_percentage']) && $format['show_percentage']) {
                return round($grade, 1) . '%';
            }
            
            if (isset($format['show_letter_grade']) && $format['show_letter_grade']) {
                return $this->convertToLetterGrade($grade);
            }
            
            if (isset($format['show_pass_fail']) && $format['show_pass_fail']) {
                return $passed ? 'Pass' : 'Fail';
            }
        }

        // Default format
        return round($grade, 1) . '%';
    }

    private function convertToLetterGrade(float $grade): string
    {
        if ($this->grade_boundaries) {
            foreach ($this->grade_boundaries as $boundary) {
                if ($grade >= $boundary['min'] && $grade <= $boundary['max']) {
                    return $boundary['grade'] ?? 'N/A';
                }
            }
        }

        // Default letter grade conversion
        if ($grade >= 90) return 'A+';
        if ($grade >= 85) return 'A';
        if ($grade >= 80) return 'A-';
        if ($grade >= 75) return 'B+';
        if ($grade >= 70) return 'B';
        if ($grade >= 65) return 'B-';
        if ($grade >= 60) return 'C+';
        if ($grade >= 55) return 'C';
        if ($grade >= 50) return 'C-';
        if ($grade >= 45) return 'D+';
        if ($grade >= 40) return 'D';
        return 'F';
    }

    // Component grading helpers
    public function getComponentMaxGrade(): int
    {
        return $this->components_graded_out_of_total ? 100 : null;
    }

    public function isDirectComponentGrading(): bool
    {
        return !$this->components_graded_out_of_total;
    }

    public function requiresAllComponentsPassed(): bool
    {
        return $this->all_components_required;
    }

    public function allowsCompensation(): bool
    {
        return $this->compensatory_grading_allowed;
    }

    // Validation methods
    public function validateComponentGrade(float $grade, float $componentMax = null): array
    {
        $errors = [];
        
        if ($this->components_graded_out_of_total) {
            if ($grade < 0 || $grade > 100) {
                $errors[] = 'Grade must be between 0 and 100%';
            }
        } else {
            if ($componentMax && ($grade < 0 || $grade > $componentMax)) {
                $errors[] = "Grade must be between 0 and {$componentMax}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    // Static factory methods
    public static function createPercentageScheme(): self
    {
        return self::create([
            'name' => 'Standard Percentage Grading',
            'code' => 'PERCENTAGE',
            'description' => 'Traditional percentage-based grading system',
            'type' => 'percentage',
            'calculation_method' => 'weighted_average',
            'components_graded_out_of_total' => true,
            'all_components_required' => false,
            'overall_pass_threshold' => 40.00,
            'compensatory_grading_allowed' => true,
            'compensation_threshold' => 35.00,
            'display_format' => ['show_percentage' => true],
        ]);
    }

    public static function createDirectComponentScheme(): self
    {
        return self::create([
            'name' => 'Direct Component Grading',
            'code' => 'DIRECT',
            'description' => 'Components graded out of their individual maximum marks',
            'type' => 'direct',
            'calculation_method' => 'weighted_average',
            'components_graded_out_of_total' => false,
            'all_components_required' => true,
            'component_pass_threshold' => 40.00,
            'overall_pass_threshold' => 40.00,
            'compensatory_grading_allowed' => false,
            'display_format' => ['show_percentage' => true],
        ]);
    }

    public static function createClassificationScheme(): self
    {
        return self::create([
            'name' => 'Degree Classification',
            'code' => 'CLASSIFICATION',
            'description' => 'University degree classification system',
            'type' => 'classification',
            'calculation_method' => 'grade_boundaries',
            'components_graded_out_of_total' => true,
            'all_components_required' => false,
            'overall_pass_threshold' => 40.00,
            'compensatory_grading_allowed' => true,
            'compensation_threshold' => 35.00,
            'grade_boundaries' => [
                ['min' => 70, 'max' => 100, 'grade' => 'First Class', 'classification' => '1st'],
                ['min' => 60, 'max' => 69, 'grade' => 'Upper Second Class', 'classification' => '2:1'],
                ['min' => 50, 'max' => 59, 'grade' => 'Lower Second Class', 'classification' => '2:2'],
                ['min' => 40, 'max' => 49, 'grade' => 'Third Class', 'classification' => '3rd'],
                ['min' => 0, 'max' => 39, 'grade' => 'Fail', 'classification' => 'Fail'],
            ],
            'display_format' => ['show_classification' => true, 'show_percentage' => true],
        ]);
    }

    public static function createCompetencyScheme(): self
    {
        return self::create([
            'name' => 'Competency-Based Assessment',
            'code' => 'COMPETENCY',
            'description' => 'Competency-based assessment system',
            'type' => 'competency',
            'calculation_method' => 'competency_based',
            'components_graded_out_of_total' => false,
            'all_components_required' => true,
            'component_pass_threshold' => 100.00, // Must achieve competency
            'overall_pass_threshold' => 100.00,
            'compensatory_grading_allowed' => false,
            'display_format' => ['show_competency' => true],
        ]);
    }
}