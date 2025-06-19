<?php

namespace App\Services;

use App\Models\StudentAssessment;
use App\Models\StudentModuleEnrolment;
use App\Models\Programme;
use App\Models\GradingScheme;
use Illuminate\Support\Facades\Log;

class GradingService
{
    /**
     * Calculate grade for a single assessment component using programme's grading scheme
     */
    public function calculateComponentGrade(StudentAssessment $assessment, float $grade, array $options = []): array
    {
        try {
            $programme = $assessment->studentModuleEnrolment->moduleInstance->cohort->programme ?? null;
            
            if (!$programme) {
                Log::debug('No programme found for assessment calculation', ['assessment_id' => $assessment->id]);
                return $this->calculateDefaultComponentGrade($grade, $options);
            }

            $gradingScheme = $programme->getEffectiveGradingScheme();
            
            if (!$gradingScheme) {
                Log::debug('No grading scheme found for programme', ['programme_id' => $programme->id]);
                return $this->calculateDefaultComponentGrade($grade, $options, $programme);
            }
        } catch (\Exception $e) {
            Log::warning('Error accessing programme/grading scheme in component calculation', [
                'assessment_id' => $assessment->id,
                'error' => $e->getMessage()
            ]);
            return $this->calculateDefaultComponentGrade($grade, $options);
        }

        // Validate the grade against the grading scheme
        $validation = $gradingScheme->validateComponentGrade(
            $grade, 
            $options['component_max'] ?? null
        );

        if (!$validation['valid']) {
            throw new \InvalidArgumentException('Invalid grade: ' . implode(', ', $validation['errors']));
        }

        // Determine pass/fail based on grading scheme and programme configuration
        $passThreshold = $this->getEffectiveComponentPassThreshold($gradingScheme, $programme);
        $passed = $grade >= $passThreshold;

        // Apply grading scheme specific logic
        $result = [
            'grade' => $grade,
            'passed' => $passed,
            'status' => $passed ? 'passed' : 'failed',
            'pass_threshold' => $passThreshold,
            'grading_scheme' => $gradingScheme->name,
            'calculation_method' => $gradingScheme->calculation_method,
        ];

        // Add scheme-specific formatting
        if ($gradingScheme->type === 'competency') {
            $result['grade_display'] = $passed ? 'Competent' : 'Not Yet Competent';
        } else {
            $result['grade_display'] = $gradingScheme->formatGradeForDisplay($grade, $passed);
        }

        return $result;
    }

    /**
     * Calculate final grade for a module using programme's grading scheme
     */
    public function calculateModuleFinalGrade(StudentModuleEnrolment $enrolment): array
    {
        try {
            $programme = $enrolment->moduleInstance->cohort->programme ?? null;
            
            if (!$programme) {
                Log::debug('No programme found for module final grade calculation', ['enrolment_id' => $enrolment->id]);
                return $this->calculateDefaultModuleFinalGrade($enrolment);
            }

            $gradingScheme = $programme->getEffectiveGradingScheme();
            
            if (!$gradingScheme) {
                Log::debug('No grading scheme found for programme', ['programme_id' => $programme->id]);
                return $this->calculateDefaultModuleFinalGrade($enrolment, $programme);
            }
        } catch (\Exception $e) {
            Log::warning('Error accessing programme/grading scheme in module calculation', [
                'enrolment_id' => $enrolment->id,
                'error' => $e->getMessage()
            ]);
            return $this->calculateDefaultModuleFinalGrade($enrolment);
        }

        // Get all graded assessments for this module enrolment
        $assessments = $enrolment->studentAssessments()
            ->whereNotNull('grade')
            ->with('assessmentComponent')
            ->get();

        if ($assessments->isEmpty()) {
            return [
                'final_grade' => null,
                'passed' => false,
                'status' => 'active', // Use valid enum value
                'message' => 'No graded assessments found',
            ];
        }

        // Extract grades and weights
        $componentGrades = $assessments->pluck('grade')->toArray();
        $componentWeights = $assessments->pluck('assessmentComponent.weight')->toArray();

        // Use the grading scheme to calculate final grade
        $calculation = $gradingScheme->calculateFinalGrade($componentGrades, $componentWeights);

        // Add component completion status to calculation before determining status
        $allComponentsGraded = $assessments->count() === $enrolment->moduleInstance->module->assessmentComponents()->where('is_active', true)->count();
        $calculation['all_components_graded'] = $allComponentsGraded;

        // Determine overall module status
        $status = $this->determineModuleStatus($calculation, $gradingScheme, $programme);

        return array_merge($calculation, [
            'status' => $status,
            'grading_scheme' => $gradingScheme->name,
            'calculation_method' => $gradingScheme->calculation_method,
            'component_count' => count($componentGrades),
        ]);
    }

    /**
     * Validate that a programme has valid grading configuration
     */
    public function validateGradingConfiguration(Programme $programme): array
    {
        $errors = [];
        $warnings = [];

        // Check for grading scheme
        $gradingScheme = $programme->getEffectiveGradingScheme();
        if (!$gradingScheme) {
            $warnings[] = 'No grading scheme configured - using system defaults';
        } else {
            // Validate grading scheme configuration
            if (!$gradingScheme->is_active) {
                $errors[] = 'Assigned grading scheme is not active';
            }
            
            if ($gradingScheme->overall_pass_threshold === null) {
                $warnings[] = 'Grading scheme has no pass threshold - using programme minimum';
            }
        }

        // Check for assessment strategy
        $assessmentStrategy = $programme->getEffectiveAssessmentStrategy();
        if (!$assessmentStrategy) {
            $warnings[] = 'No assessment strategy configured - using system defaults';
        } else if (!$assessmentStrategy->is_active) {
            $errors[] = 'Assigned assessment strategy is not active';
        }

        // Check for progression rule
        $progressionRule = $programme->getEffectiveProgressionRule();
        if (!$progressionRule) {
            $warnings[] = 'No progression rule configured - using system defaults';
        } else if (!$progressionRule->is_active) {
            $errors[] = 'Assigned progression rule is not active';
        }

        // Check programme-specific settings
        if ($programme->minimum_pass_grade === null) {
            $warnings[] = 'Programme has no minimum pass grade - using system default (40%)';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'has_grading_scheme' => $gradingScheme !== null,
            'has_assessment_strategy' => $assessmentStrategy !== null,
            'has_progression_rule' => $progressionRule !== null,
        ];
    }

    /**
     * Get the effective pass threshold for a component
     */
    public function getEffectiveComponentPassThreshold(GradingScheme $gradingScheme = null, Programme $programme = null): float
    {
        // Priority order:
        // 1. Grading scheme component pass threshold
        // 2. Grading scheme overall pass threshold  
        // 3. Programme minimum pass grade
        // 4. System default (40%)

        if ($gradingScheme && $gradingScheme->component_pass_threshold !== null) {
            return $gradingScheme->component_pass_threshold;
        }

        if ($gradingScheme && $gradingScheme->overall_pass_threshold !== null) {
            return $gradingScheme->overall_pass_threshold;
        }

        if ($programme && $programme->minimum_pass_grade !== null) {
            return $programme->minimum_pass_grade;
        }

        return 40.0; // System default
    }

    /**
     * Get the effective pass threshold for a module
     */
    public function getEffectiveModulePassThreshold(GradingScheme $gradingScheme = null, Programme $programme = null): float
    {
        // Priority order:
        // 1. Grading scheme overall pass threshold
        // 2. Programme minimum pass grade
        // 3. System default (40%)

        if ($gradingScheme && $gradingScheme->overall_pass_threshold !== null) {
            return $gradingScheme->overall_pass_threshold;
        }

        if ($programme && $programme->minimum_pass_grade !== null) {
            return $programme->minimum_pass_grade;
        }

        return 40.0; // System default
    }

    /**
     * Check if programme requires all components to pass
     */
    public function requiresAllComponentsPassed(GradingScheme $gradingScheme = null, Programme $programme = null): bool
    {
        if ($gradingScheme && $gradingScheme->all_components_required !== null) {
            return $gradingScheme->all_components_required;
        }

        // Check if programme has override in grading_overrides
        if ($programme && isset($programme->grading_overrides['all_components_required'])) {
            return $programme->grading_overrides['all_components_required'];
        }

        return false; // System default - compensation allowed
    }

    /**
     * Fallback calculation for assessments without programme configuration
     */
    private function calculateDefaultComponentGrade(float $grade, array $options = [], Programme $programme = null): array
    {
        $passThreshold = $programme->minimum_pass_grade ?? 40.0;
        $passed = $grade >= $passThreshold;

        return [
            'grade' => $grade,
            'passed' => $passed,
            'status' => $passed ? 'passed' : 'failed',
            'pass_threshold' => $passThreshold,
            'grading_scheme' => 'System Default',
            'calculation_method' => 'simple_threshold',
            'grade_display' => round($grade, 1) . '%',
        ];
    }

    /**
     * Fallback calculation for modules without programme configuration
     */
    private function calculateDefaultModuleFinalGrade(StudentModuleEnrolment $enrolment, Programme $programme = null): array
    {
        // Use the existing logic as fallback
        $assessments = $enrolment->studentAssessments()
            ->whereNotNull('grade')
            ->with('assessmentComponent')
            ->get();

        if ($assessments->isEmpty()) {
            return [
                'final_grade' => null,
                'passed' => false,
                'status' => 'active', // Use valid enum value
                'message' => 'No graded assessments found',
            ];
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($assessments as $assessment) {
            $weight = $assessment->assessmentComponent->weight;
            $totalWeightedScore += ($assessment->grade * $weight);
            $totalWeight += $weight;
        }

        $finalGrade = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
        $passThreshold = $programme->minimum_pass_grade ?? 40.0;
        $passed = $finalGrade >= $passThreshold;

        return [
            'final_grade' => round($finalGrade, 2),
            'passed' => $passed,
            'status' => $passed ? 'completed' : 'failed',
            'pass_threshold' => $passThreshold,
            'grading_scheme' => 'System Default',
            'calculation_method' => 'weighted_average',
            'grade_display' => round($finalGrade, 1) . '%',
            'component_count' => $assessments->count(),
            'all_components_graded' => $assessments->count() === $enrolment->moduleInstance->module->assessmentComponents()->where('is_active', true)->count(),
        ];
    }

    /**
     * Determine module status based on calculation results
     */
    private function determineModuleStatus(array $calculation, GradingScheme $gradingScheme, Programme $programme): string
    {
        if (!isset($calculation['passed']) || !$calculation['passed']) {
            return 'failed';
        }

        // Check if all components are graded
        if (!($calculation['all_components_graded'] ?? false)) {
            return 'active'; // Map to valid enum value
        }

        // For competency-based grading
        if ($gradingScheme->type === 'competency') {
            $competenciesAchieved = $calculation['competencies_achieved'] ?? 0;
            $totalCompetencies = $calculation['total_competencies'] ?? 0;
            return $competenciesAchieved === $totalCompetencies ? 'completed' : 'failed';
        }

        return 'completed';
    }

    /**
     * Log grading calculation for audit trail
     */
    private function logGradingCalculation(string $type, array $data): void
    {
        Log::info("Grading calculation performed", [
            'type' => $type,
            'data' => $data,
            'timestamp' => now(),
            'user_id' => auth()->id(),
        ]);
    }
}