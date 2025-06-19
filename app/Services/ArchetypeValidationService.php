<?php

namespace App\Services;

use App\Models\Programme;
use App\Models\Student;
use App\Models\ModuleInstance;
use App\Models\StudentModuleEnrolment;
use App\Models\Enrolment;
use Illuminate\Support\Collection;

/**
 * Service for validating archetype-specific business rules and constraints
 */
class ArchetypeValidationService
{
    /**
     * Validate if a student can enroll in a programme based on archetype rules
     */
    public function canStudentEnrollInProgramme(Student $student, Programme $programme): array
    {
        $errors = [];
        $warnings = [];
        
        $programmeType = $programme->programmeType;
        $progressionRule = $programme->getEffectiveModuleProgressionRule();
        
        if (!$programmeType) {
            $warnings[] = 'Programme has no archetype defined - using default rules';
        }

        // Check programme-specific requirements
        if ($programme->requires_placement && !$this->hasValidPlacementDocuments($student)) {
            $errors[] = 'Student must have valid placement documentation before enrollment';
        }

        if ($programme->requires_external_verification && !$this->hasExternalVerification($student)) {
            $errors[] = 'External verification required for this programme type';
        }

        // Check existing enrollments based on archetype
        if ($progressionRule) {
            $existingEnrollments = $this->getStudentActiveEnrollments($student);
            
            switch ($progressionRule->progression_type) {
                case 'sequential':
                    $sequentialErrors = $this->validateSequentialEnrollment($student, $programme, $existingEnrollments);
                    $errors = array_merge($errors, $sequentialErrors);
                    break;
                    
                case 'credit_based':
                    $creditErrors = $this->validateCreditBasedEnrollment($student, $programme, $existingEnrollments);
                    $errors = array_merge($errors, $creditErrors);
                    break;
                    
                case 'flexible':
                    // Flexible programmes have minimal restrictions
                    break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate if a student can enroll in a module instance based on archetype rules
     */
    public function canStudentEnrollInModuleInstance(Student $student, ModuleInstance $moduleInstance): array
    {
        $errors = [];
        $warnings = [];

        // Check instance-specific enrollment rules
        if ($moduleInstance->instance_type === 'rolling') {
            if ($moduleInstance->enrollment_status !== 'open') {
                $errors[] = 'Rolling enrollment is currently closed for this module';
            }
            
            if ($moduleInstance->max_enrollments && $moduleInstance->current_enrollments >= $moduleInstance->max_enrollments) {
                $errors[] = 'Module instance has reached maximum enrollment capacity';
            }
            
            if ($moduleInstance->enrollment_close_date && now() > $moduleInstance->enrollment_close_date) {
                $errors[] = 'Enrollment period has closed for this module instance';
            }
        }

        // Check if student is enrolled in a compatible programme
        $compatibleProgrammes = $this->getCompatibleProgrammesForModule($moduleInstance);
        $studentProgrammes = $this->getStudentActiveProgrammes($student);
        
        $hasCompatibleProgramme = $compatibleProgrammes->intersect($studentProgrammes->pluck('programme_id'))->isNotEmpty();
        
        if (!$hasCompatibleProgramme && $moduleInstance->instance_type !== 'standalone') {
            $warnings[] = 'Student is not enrolled in a compatible programme for this module';
        }

        // Check prerequisites based on progression rules
        if ($studentProgrammes->isNotEmpty()) {
            $programme = $studentProgrammes->first()->programme;
            $progressionRule = $programme->getEffectiveModuleProgressionRule();
            
            if ($progressionRule && $progressionRule->requires_previous_completion) {
                $prerequisiteErrors = $this->validatePrerequisites($student, $moduleInstance, $programme);
                $errors = array_merge($errors, $prerequisiteErrors);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate progression to next module based on archetype rules
     */
    public function canStudentProgressToNextModule(Student $student, ModuleInstance $currentModule, ModuleInstance $nextModule): array
    {
        $errors = [];
        
        $studentEnrollment = StudentModuleEnrolment::where('student_id', $student->id)
            ->where('module_instance_id', $currentModule->id)
            ->first();
            
        if (!$studentEnrollment) {
            $errors[] = 'Student is not enrolled in the current module';
            return ['valid' => false, 'errors' => $errors, 'warnings' => []];
        }

        $programme = $studentEnrollment->enrolment?->programme;
        if (!$programme) {
            $errors[] = 'Cannot determine programme for progression validation';
            return ['valid' => false, 'errors' => $errors, 'warnings' => []];
        }

        $progressionRule = $programme->getEffectiveModuleProgressionRule();
        
        if ($progressionRule) {
            switch ($progressionRule->progression_type) {
                case 'sequential':
                    if (!$this->hasCompletedModule($studentEnrollment)) {
                        $errors[] = 'Must complete current module before progressing (sequential progression)';
                    }
                    break;
                    
                case 'credit_based':
                    $creditErrors = $this->validateCreditProgressionRules($student, $programme, $nextModule);
                    $errors = array_merge($errors, $creditErrors);
                    break;
                    
                case 'flexible':
                    // Flexible progression allows moving to any module
                    break;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => [],
        ];
    }

    /**
     * Get grading constraints based on programme archetype
     */
    public function getGradingConstraints(Programme $programme): array
    {
        $gradingScheme = $programme->getEffectiveGradingScheme();
        $assessmentStrategy = $programme->getEffectiveAssessmentStrategy();
        
        $constraints = [
            'pass_threshold' => $programme->minimum_pass_grade ?? 40.0,
            'allows_compensation' => false,
            'max_resubmissions' => 1,
            'requires_external_verification' => $programme->requires_external_verification,
        ];

        if ($gradingScheme) {
            $constraints['allows_compensation'] = $gradingScheme->compensatory_grading_allowed ?? false;
            $constraints['compensation_threshold'] = $gradingScheme->compensation_threshold ?? 35.0;
            $constraints['pass_threshold'] = $gradingScheme->overall_pass_threshold ?? $constraints['pass_threshold'];
        }

        if ($assessmentStrategy) {
            $constraints['max_resubmissions'] = $assessmentStrategy->max_resubmissions ?? 1;
            $constraints['requires_moderation'] = $assessmentStrategy->requires_moderation ?? false;
            $constraints['allows_draft_submissions'] = $assessmentStrategy->supports_draft_submissions ?? false;
        }

        return $constraints;
    }

    // Private helper methods

    private function hasValidPlacementDocuments(Student $student): bool
    {
        // Check if student has required placement documentation
        // This would integrate with the student profile extensions system
        return true; // Placeholder - implement based on requirements
    }

    private function hasExternalVerification(Student $student): bool
    {
        // Check if student has external verification documents
        return true; // Placeholder - implement based on requirements
    }

    private function getStudentActiveEnrollments(Student $student): Collection
    {
        return Enrolment::where('student_id', $student->id)
            ->where('status', 'active')
            ->with(['programme', 'cohort'])
            ->get();
    }

    private function validateSequentialEnrollment(Student $student, Programme $programme, Collection $existingEnrollments): array
    {
        $errors = [];
        
        // Check if student is already enrolled in this programme
        $existingProgrammeEnrollment = $existingEnrollments->where('programme_id', $programme->id)->first();
        if ($existingProgrammeEnrollment) {
            $errors[] = 'Student is already enrolled in this programme (sequential programmes allow single enrollment)';
        }
        
        return $errors;
    }

    private function validateCreditBasedEnrollment(Student $student, Programme $programme, Collection $existingEnrollments): array
    {
        $errors = [];
        
        $progressionRule = $programme->getEffectiveModuleProgressionRule();
        $maxCreditsPerPeriod = $progressionRule->maximum_credits_per_period ?? 180;
        
        // Calculate current credit load
        $currentCredits = $this->calculateCurrentCreditLoad($student);
        $programmeCredits = $programme->credit_value ?? 120;
        
        if ($currentCredits + $programmeCredits > $maxCreditsPerPeriod) {
            $errors[] = "Enrollment would exceed maximum credits per period ({$maxCreditsPerPeriod})";
        }
        
        return $errors;
    }

    private function getCompatibleProgrammesForModule(ModuleInstance $moduleInstance): Collection
    {
        // Get programmes that include this module
        return $moduleInstance->module->programmes ?? collect();
    }

    private function getStudentActiveProgrammes(Student $student): Collection
    {
        return Enrolment::where('student_id', $student->id)
            ->where('status', 'active')
            ->with('programme')
            ->get();
    }

    private function validatePrerequisites(Student $student, ModuleInstance $moduleInstance, Programme $programme): array
    {
        $errors = [];
        
        // This would check module prerequisites based on programme structure
        // For now, just a placeholder implementation
        
        return $errors;
    }

    private function hasCompletedModule(StudentModuleEnrolment $enrollment): bool
    {
        return in_array($enrollment->status, ['completed', 'passed']);
    }

    private function validateCreditProgressionRules(Student $student, Programme $programme, ModuleInstance $nextModule): array
    {
        $errors = [];
        
        $progressionRule = $programme->getEffectiveModuleProgressionRule();
        $currentCredits = $this->calculateCurrentCreditLoad($student);
        $moduleCredits = $nextModule->module->credits ?? 15;
        
        if ($progressionRule->maximum_credits_per_period && 
            $currentCredits + $moduleCredits > $progressionRule->maximum_credits_per_period) {
            $errors[] = 'Would exceed maximum credits per period';
        }
        
        return $errors;
    }

    private function calculateCurrentCreditLoad(Student $student): int
    {
        return StudentModuleEnrolment::where('student_id', $student->id)
            ->whereIn('status', ['active', 'enrolled'])
            ->with('moduleInstance.module')
            ->get()
            ->sum(function ($enrollment) {
                return $enrollment->moduleInstance->module->credits ?? 15;
            });
    }
}