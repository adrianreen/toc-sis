<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Enrolment;
use App\Models\ModuleInstance;
use App\Models\StudentModuleEnrolment;
use App\Models\StudentAssessment;
use App\Models\AssessmentComponent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrolmentService
{
    /**
     * Enrol a student in a programme and handle all related enrollments
     */
    public function enrolStudent(Student $student, array $enrolmentData): Enrolment
    {
        return DB::transaction(function () use ($student, $enrolmentData) {
            // Create the main programme enrolment
            $enrolment = Enrolment::create([
                'student_id' => $student->id,
                'programme_id' => $enrolmentData['programme_id'],
                'cohort_id' => $enrolmentData['cohort_id'] ?? null,
                'enrolment_date' => $enrolmentData['enrolment_date'],
                'status' => 'active',
            ]);

            // If this is a cohort-based programme, enrol in module instances
            if ($enrolment->cohort_id) {
                $this->enrolInModuleInstances($student, $enrolment);
            }

            // Update student status to active if not already
            if ($student->status === 'enquiry' || $student->status === 'enrolled') {
                $student->update(['status' => 'active']);
            }

            Log::info('Student enrolled successfully', [
                'student_id' => $student->id,
                'programme_id' => $enrolmentData['programme_id'],
                'cohort_id' => $enrolmentData['cohort_id'],
            ]);

            return $enrolment;
        });
    }

    /**
     * Enrol student in all module instances for their cohort
     */
    protected function enrolInModuleInstances(Student $student, Enrolment $enrolment): void
    {
        // Get all module instances for this cohort
        $moduleInstances = ModuleInstance::where('cohort_id', $enrolment->cohort_id)
            ->with(['module.assessmentComponents'])
            ->get();

        foreach ($moduleInstances as $moduleInstance) {
            $this->enrolInSingleModuleInstance($student, $enrolment, $moduleInstance);
        }
    }

    /**
     * Enrol student in a single module instance and create assessments
     */
    public function enrolInSingleModuleInstance(Student $student, Enrolment $enrolment, ModuleInstance $moduleInstance): StudentModuleEnrolment
    {
        // Check if already enrolled (avoid duplicates)
        $existingEnrolment = StudentModuleEnrolment::where([
            'student_id' => $student->id,
            'module_instance_id' => $moduleInstance->id,
        ])->first();

        if ($existingEnrolment) {
            return $existingEnrolment;
        }

        // Create the module enrolment
        $studentModuleEnrolment = StudentModuleEnrolment::create([
            'student_id' => $student->id,
            'enrolment_id' => $enrolment->id,
            'module_instance_id' => $moduleInstance->id,
            'status' => 'enrolled',
            'attempt_number' => 1,
        ]);

        // Create individual assessments for all assessment components
        $this->createAssessmentsForStudent($studentModuleEnrolment, $moduleInstance);

        Log::info('Student enrolled in module instance', [
            'student_id' => $student->id,
            'module_instance_id' => $moduleInstance->id,
            'instance_code' => $moduleInstance->instance_code,
        ]);

        return $studentModuleEnrolment;
    }

    /**
     * Create individual assessment records for a student
     */
    protected function createAssessmentsForStudent(StudentModuleEnrolment $studentModuleEnrolment, ModuleInstance $moduleInstance): void
    {
        $assessmentComponents = $moduleInstance->module->assessmentComponents()
            ->where('is_active', true)
            ->orderBy('sequence')
            ->get();

        foreach ($assessmentComponents as $component) {
            $dueDate = $this->calculateDueDate($moduleInstance, $component);

            StudentAssessment::create([
                'student_module_enrolment_id' => $studentModuleEnrolment->id,
                'assessment_component_id' => $component->id,
                'attempt_number' => 1,
                'status' => 'pending',
                'due_date' => $dueDate,
            ]);
        }

        Log::info('Created assessments for student', [
            'student_id' => $studentModuleEnrolment->student_id,
            'module_instance_id' => $moduleInstance->id,
            'assessment_count' => $assessmentComponents->count(),
        ]);
    }

    /**
     * Calculate due date for an assessment component
     * This is a simple implementation - you can make it more sophisticated
     */
    public function calculateDueDate(ModuleInstance $moduleInstance, AssessmentComponent $component): Carbon
    {
        // Simple logic: spread assessments evenly across the module duration
        $startDate = Carbon::parse($moduleInstance->start_date);
        $endDate = Carbon::parse($moduleInstance->end_date);
        $totalDuration = $startDate->diffInDays($endDate);
        
        // Calculate percentage through module based on sequence
        $maxSequence = $moduleInstance->module->assessmentComponents()->max('sequence') ?? 1;
        $percentageThrough = ($component->sequence / $maxSequence) * 0.8; // Use 80% of duration
        
        $daysToAdd = (int) ($totalDuration * $percentageThrough);
        
        return $startDate->copy()->addDays($daysToAdd);
    }

    /**
     * Process a deferral and move student to new cohort
     */
    public function processDeferralReturn(Student $student, Enrolment $originalEnrolment, $newCohortId): void
    {
        DB::transaction(function () use ($student, $originalEnrolment, $newCohortId) {
            // Update the original enrolment
            $originalEnrolment->update([
                'cohort_id' => $newCohortId,
                'status' => 'active',
            ]);

            // Remove old module enrolments
            StudentModuleEnrolment::where('enrolment_id', $originalEnrolment->id)
                ->delete();

            // Enrol in new cohort's module instances
            $this->enrolInModuleInstances($student, $originalEnrolment);

            // Update student status
            $student->update(['status' => 'active']);
        });
    }

    /**
     * Add a student to an existing module instance (for late enrollments)
     */
    public function addStudentToModuleInstance(Student $student, ModuleInstance $moduleInstance): StudentModuleEnrolment
    {
        // Find their programme enrolment
        $enrolment = $student->enrolments()
            ->where('programme_id', $moduleInstance->cohort->programme_id)
            ->where('status', 'active')
            ->first();

        if (!$enrolment) {
            throw new \Exception('Student must be enrolled in the programme first');
        }

        return $this->enrolInSingleModuleInstance($student, $enrolment, $moduleInstance);
    }
}