<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Enrolment;
use App\Models\ProgrammeInstance;
use App\Models\ModuleInstance;
use App\Models\StudentGradeRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrolmentService
{
    /**
     * Enrol a student in a programme instance (two-path system)
     */
    public function enrolStudentInProgramme(Student $student, ProgrammeInstance $programmeInstance, array $enrolmentData = []): Enrolment
    {
        try {
            return DB::transaction(function () use ($student, $programmeInstance, $enrolmentData) {
                // Validate no existing active enrolment in this programme instance
                $existingEnrolment = Enrolment::where([
                    'student_id' => $student->id,
                    'programme_instance_id' => $programmeInstance->id,
                    'enrolment_type' => 'programme'
                ])->whereIn('status', ['active', 'deferred'])->first();

                if ($existingEnrolment) {
                    throw new \Exception('Student is already enrolled in this programme instance');
                }

                // Create the programme enrolment
                $enrolment = Enrolment::create([
                    'student_id' => $student->id,
                    'enrolment_type' => 'programme',
                    'programme_instance_id' => $programmeInstance->id,
                    'module_instance_id' => null,
                    'enrolment_date' => $enrolmentData['enrolment_date'] ?? now(),
                    'status' => 'active',
                ]);

                // Create student grade records for all assessment components in curriculum
                $this->createGradeRecordsForProgrammeEnrolment($student, $programmeInstance);

                // Update student status to active if not already
                if (in_array($student->status, ['enquiry', 'enrolled'])) {
                    $student->update(['status' => 'active']);
                }

                Log::info('Student enrolled in programme successfully', [
                    'student_id' => $student->id,
                    'programme_instance_id' => $programmeInstance->id,
                    'programme_title' => $programmeInstance->programme->title,
                ]);

                return $enrolment;
            });
        } catch (\Exception $e) {
            Log::error('Failed to enroll student in programme', [
                'student_id' => $student->id,
                'programme_instance_id' => $programmeInstance->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enrol a student in a standalone module instance (two-path system)
     */
    public function enrolStudentInModule(Student $student, ModuleInstance $moduleInstance, array $enrolmentData = []): Enrolment
    {
        try {
            return DB::transaction(function () use ($student, $moduleInstance, $enrolmentData) {
                // Validate module allows standalone enrolment
                if (!$moduleInstance->module->allows_standalone_enrolment) {
                    throw new \Exception('This module does not allow standalone enrolment');
                }

                // Validate no existing active enrolment in this module instance
                $existingEnrolment = Enrolment::where([
                    'student_id' => $student->id,
                    'module_instance_id' => $moduleInstance->id,
                    'enrolment_type' => 'module'
                ])->whereIn('status', ['active', 'deferred'])->first();

                if ($existingEnrolment) {
                    throw new \Exception('Student is already enrolled in this module instance');
                }

                // Create the module enrolment
                $enrolment = Enrolment::create([
                    'student_id' => $student->id,
                    'enrolment_type' => 'module',
                    'programme_instance_id' => null,
                    'module_instance_id' => $moduleInstance->id,
                    'enrolment_date' => $enrolmentData['enrolment_date'] ?? now(),
                    'status' => 'active',
                ]);

                // Create student grade records for all assessment components in this module
                $this->createGradeRecordsForModuleEnrolment($student, $moduleInstance);

                Log::info('Student enrolled in standalone module successfully', [
                    'student_id' => $student->id,
                    'module_instance_id' => $moduleInstance->id,
                    'module_title' => $moduleInstance->module->title,
                ]);

                return $enrolment;
            });
        } catch (\Exception $e) {
            Log::error('Failed to enroll student in module', [
                'student_id' => $student->id,
                'module_instance_id' => $moduleInstance->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create grade records for all modules in a programme instance curriculum
     */
    protected function createGradeRecordsForProgrammeEnrolment(Student $student, ProgrammeInstance $programmeInstance): void
    {
        foreach ($programmeInstance->moduleInstances as $moduleInstance) {
            $this->createGradeRecordsForModuleInstance($student, $moduleInstance);
        }
    }

    /**
     * Create grade records for a standalone module enrolment
     */
    protected function createGradeRecordsForModuleEnrolment(Student $student, ModuleInstance $moduleInstance): void
    {
        $this->createGradeRecordsForModuleInstance($student, $moduleInstance);
    }

    /**
     * Create individual grade records for all assessment components in a module instance
     */
    protected function createGradeRecordsForModuleInstance(Student $student, ModuleInstance $moduleInstance): void
    {
        $assessmentComponents = $moduleInstance->module->assessment_strategy ?? [];
        
        foreach ($assessmentComponents as $component) {
            // Check if grade record already exists (avoid duplicates)
            $existingRecord = StudentGradeRecord::where([
                'student_id' => $student->id,
                'module_instance_id' => $moduleInstance->id,
                'assessment_component_name' => $component['component_name']
            ])->first();

            if ($existingRecord) {
                continue; // Skip if already exists
            }

            StudentGradeRecord::create([
                'student_id' => $student->id,
                'module_instance_id' => $moduleInstance->id,
                'assessment_component_name' => $component['component_name'],
                'grade' => null,
                'max_grade' => 100,
                'feedback' => null,
                'submission_date' => null,
                'graded_date' => null,
                'graded_by_staff_id' => null,
                'is_visible_to_student' => false,
                'release_date' => null,
            ]);
        }

        Log::info('Created grade records for student in module instance', [
            'student_id' => $student->id,
            'module_instance_id' => $moduleInstance->id,
            'assessment_count' => count($assessmentComponents),
        ]);
    }

    /**
     * Process a deferral and move student to new programme instance
     */
    public function processDeferralReturn(Student $student, Enrolment $originalEnrolment, ProgrammeInstance $newProgrammeInstance): void
    {
        if ($originalEnrolment->enrolment_type !== 'programme') {
            throw new \Exception('Deferrals only apply to programme enrolments');
        }

        DB::transaction(function () use ($student, $originalEnrolment, $newProgrammeInstance) {
            // Update the original enrolment to point to new programme instance
            $originalEnrolment->update([
                'programme_instance_id' => $newProgrammeInstance->id,
                'status' => 'active',
            ]);

            // Remove old grade records
            StudentGradeRecord::where('student_id', $student->id)
                ->whereIn('module_instance_id', $originalEnrolment->programmeInstance->moduleInstances->pluck('id'))
                ->delete();

            // Create new grade records for new programme instance curriculum
            $this->createGradeRecordsForProgrammeEnrolment($student, $newProgrammeInstance);

            // Update student status
            $student->update(['status' => 'active']);

            Log::info('Processed deferral return', [
                'student_id' => $student->id,
                'old_programme_instance_id' => $originalEnrolment->programme_instance_id,
                'new_programme_instance_id' => $newProgrammeInstance->id,
            ]);
        });
    }

    /**
     * Withdraw a student from an enrolment
     */
    public function withdrawStudent(Enrolment $enrolment, array $withdrawalData = []): void
    {
        DB::transaction(function () use ($enrolment, $withdrawalData) {
            $enrolment->update([
                'status' => 'withdrawn',
            ]);

            // Optionally update student status if they have no other active enrolments
            $activeEnrolments = Enrolment::where('student_id', $enrolment->student_id)
                ->where('status', 'active')
                ->count();

            if ($activeEnrolments === 0) {
                $enrolment->student->update(['status' => 'cancelled']);
            }

            Log::info('Student withdrawn from enrolment', [
                'student_id' => $enrolment->student_id,
                'enrolment_id' => $enrolment->id,
                'enrolment_type' => $enrolment->enrolment_type,
            ]);
        });
    }

    /**
     * Get available programme instances for enrolment
     */
    public function getAvailableProgrammeInstances(): \Illuminate\Database\Eloquent\Collection
    {
        return ProgrammeInstance::with('programme')
            ->where('intake_start_date', '<=', now())
            ->where(function ($query) {
                $query->where('intake_end_date', '>=', now())
                      ->orWhereNull('intake_end_date');
            })
            ->orderBy('intake_start_date', 'desc')
            ->get();
    }

    /**
     * Get available standalone module instances for enrolment
     */
    public function getAvailableModuleInstances(): \Illuminate\Database\Eloquent\Collection
    {
        return ModuleInstance::with('module')
            ->whereHas('module', function ($query) {
                $query->where('allows_standalone_enrolment', true);
            })
            ->where('start_date', '>=', now()) // Module must start today or in the future
            ->where('start_date', '<=', now()->addMonths(6)) // But not more than 6 months out
            ->orderBy('start_date', 'asc') // Order by soonest first
            ->get();
    }
}