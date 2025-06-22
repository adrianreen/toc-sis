<?php

namespace App\Services;

use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\Enrolment;
use App\Models\StudentGradeRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Comprehensive validation service for the 4-level Programme-Module architecture
 * Ensures data integrity and business rule compliance across the entire system
 */
class ArchitectureValidationService
{
    /**
     * Comprehensive system validation
     */
    public function validateEntireArchitecture(): array
    {
        $errors = [];
        $warnings = [];
        $stats = [];

        // Core architecture validation
        $errors = array_merge($errors, $this->validateProgrammes());
        $errors = array_merge($errors, $this->validateProgrammeInstances());
        $errors = array_merge($errors, $this->validateModules());
        $errors = array_merge($errors, $this->validateModuleInstances());
        
        // Relationship integrity
        $errors = array_merge($errors, $this->validateCurriculumLinks());
        $errors = array_merge($errors, $this->validateEnrolmentIntegrity());
        $errors = array_merge($errors, $this->validateGradeRecordIntegrity());
        
        // Business rule compliance
        $warnings = array_merge($warnings, $this->validateBusinessRules());
        
        // System statistics
        $stats = $this->generateSystemStats();

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => $stats,
            'valid' => empty($errors)
        ];
    }

    /**
     * Validate Programme blueprints
     */
    private function validateProgrammes(): array
    {
        $errors = [];
        
        $programmes = Programme::all();
        
        foreach ($programmes as $programme) {
            // Basic field validation
            if (empty($programme->title)) {
                $errors[] = "Programme ID {$programme->id}: Missing title";
            }
            
            if (empty($programme->awarding_body)) {
                $errors[] = "Programme ID {$programme->id}: Missing awarding body";
            }
            
            if ($programme->total_credits <= 0) {
                $errors[] = "Programme ID {$programme->id}: Invalid total credits ({$programme->total_credits})";
            }
            
            if (!in_array($programme->nfq_level, [1,2,3,4,5,6,7,8,9,10])) {
                $errors[] = "Programme ID {$programme->id}: Invalid NFQ level ({$programme->nfq_level})";
            }
        }
        
        return $errors;
    }

    /**
     * Validate Programme Instances (live containers)
     */
    private function validateProgrammeInstances(): array
    {
        $errors = [];
        
        $instances = ProgrammeInstance::with(['programme', 'moduleInstances'])->get();
        
        foreach ($instances as $instance) {
            // Must have valid programme link
            if (!$instance->programme) {
                $errors[] = "Programme Instance ID {$instance->id}: Missing programme blueprint link";
            }
            
            // Must have label
            if (empty($instance->label)) {
                $errors[] = "Programme Instance ID {$instance->id}: Missing label";
            }
            
            // Must have intake dates
            if (!$instance->intake_start_date) {
                $errors[] = "Programme Instance ID {$instance->id}: Missing intake start date";
            }
            
            if (!$instance->intake_end_date) {
                $errors[] = "Programme Instance ID {$instance->id}: Missing intake end date";
            }
            
            // Intake dates must be logical
            if ($instance->intake_start_date && $instance->intake_end_date) {
                if ($instance->intake_start_date >= $instance->intake_end_date) {
                    $errors[] = "Programme Instance ID {$instance->id}: Intake start date must be before end date";
                }
            }
            
            // Must have valid delivery style
            if (!in_array($instance->default_delivery_style, ['sync', 'async'])) {
                $errors[] = "Programme Instance ID {$instance->id}: Invalid delivery style ({$instance->default_delivery_style})";
            }
        }
        
        return $errors;
    }

    /**
     * Validate Module blueprints
     */
    private function validateModules(): array
    {
        $errors = [];
        
        $modules = Module::all();
        
        foreach ($modules as $module) {
            // Basic field validation
            if (empty($module->title)) {
                $errors[] = "Module ID {$module->id}: Missing title";
            }
            
            if (empty($module->module_code)) {
                $errors[] = "Module ID {$module->id}: Missing module code";
            }
            
            if ($module->credit_value <= 0) {
                $errors[] = "Module ID {$module->id}: Invalid credit value ({$module->credit_value})";
            }
            
            // Assessment strategy validation
            if (empty($module->assessment_strategy)) {
                $errors[] = "Module ID {$module->id}: Missing assessment strategy";
            } else {
                $totalWeight = collect($module->assessment_strategy)->sum('weighting');
                if (abs($totalWeight - 100) > 0.01) { // Allow for floating point precision
                    $errors[] = "Module ID {$module->id}: Assessment weights total {$totalWeight}%, must be 100%";
                }
                
                foreach ($module->assessment_strategy as $index => $component) {
                    if (empty($component['component_name'])) {
                        $errors[] = "Module ID {$module->id}: Assessment component {$index} missing name";
                    }
                    
                    if (!isset($component['weighting']) || $component['weighting'] < 0 || $component['weighting'] > 100) {
                        $errors[] = "Module ID {$module->id}: Assessment component {$index} invalid weighting";
                    }
                }
            }
            
            // Async cadence validation
            if (!in_array($module->async_instance_cadence, ['monthly', 'quarterly', 'bi_annually', 'annually'])) {
                $errors[] = "Module ID {$module->id}: Invalid async instance cadence ({$module->async_instance_cadence})";
            }
        }
        
        return $errors;
    }

    /**
     * Validate Module Instances (live classes)
     */
    private function validateModuleInstances(): array
    {
        $errors = [];
        
        $instances = ModuleInstance::with(['module', 'tutor'])->get();
        
        foreach ($instances as $instance) {
            // Must have valid module link
            if (!$instance->module) {
                $errors[] = "Module Instance ID {$instance->id}: Missing module blueprint link";
            }
            
            // Must have start date
            if (!$instance->start_date) {
                $errors[] = "Module Instance ID {$instance->id}: Missing start date";
            }
            
            // End date must be after start date if set
            if ($instance->start_date && $instance->target_end_date) {
                if ($instance->start_date >= $instance->target_end_date) {
                    $errors[] = "Module Instance ID {$instance->id}: Start date must be before target end date";
                }
            }
            
            // Must have valid delivery style
            if (!in_array($instance->delivery_style, ['sync', 'async'])) {
                $errors[] = "Module Instance ID {$instance->id}: Invalid delivery style ({$instance->delivery_style})";
            }
            
            // Tutor must be a teacher if assigned
            if ($instance->tutor && $instance->tutor->role !== 'teacher') {
                $errors[] = "Module Instance ID {$instance->id}: Assigned tutor is not a teacher (role: {$instance->tutor->role})";
            }
        }
        
        return $errors;
    }

    /**
     * Validate curriculum linker integrity
     */
    private function validateCurriculumLinks(): array
    {
        $errors = [];
        
        // Check for orphaned curriculum links
        $orphanedLinks = DB::table('programme_instance_curriculum')
            ->leftJoin('programme_instances', 'programme_instance_curriculum.programme_instance_id', '=', 'programme_instances.id')
            ->leftJoin('module_instances', 'programme_instance_curriculum.module_instance_id', '=', 'module_instances.id')
            ->whereNull('programme_instances.id')
            ->orWhereNull('module_instances.id')
            ->get();
        
        foreach ($orphanedLinks as $link) {
            $errors[] = "Orphaned curriculum link: Programme Instance {$link->programme_instance_id} -> Module Instance {$link->module_instance_id}";
        }
        
        return $errors;
    }

    /**
     * Validate enrolment system integrity
     */
    private function validateEnrolmentIntegrity(): array
    {
        $errors = [];
        
        $enrolments = Enrolment::with(['student', 'programmeInstance', 'moduleInstance'])->get();
        
        foreach ($enrolments as $enrolment) {
            // Must have valid student
            if (!$enrolment->student) {
                $errors[] = "Enrolment ID {$enrolment->id}: Missing student record";
            }
            
            // Two-path validation
            if ($enrolment->enrolment_type === 'programme') {
                if (!$enrolment->programme_instance_id || !$enrolment->programmeInstance) {
                    $errors[] = "Enrolment ID {$enrolment->id}: Programme enrolment missing programme instance";
                }
                if ($enrolment->module_instance_id) {
                    $errors[] = "Enrolment ID {$enrolment->id}: Programme enrolment should not have module instance ID";
                }
            } elseif ($enrolment->enrolment_type === 'module') {
                if (!$enrolment->module_instance_id || !$enrolment->moduleInstance) {
                    $errors[] = "Enrolment ID {$enrolment->id}: Module enrolment missing module instance";
                }
                if ($enrolment->programme_instance_id) {
                    $errors[] = "Enrolment ID {$enrolment->id}: Module enrolment should not have programme instance ID";
                }
                // Standalone module must allow standalone enrolment
                if ($enrolment->moduleInstance && !$enrolment->moduleInstance->module->allows_standalone_enrolment) {
                    $errors[] = "Enrolment ID {$enrolment->id}: Module does not allow standalone enrolment";
                }
            } else {
                $errors[] = "Enrolment ID {$enrolment->id}: Invalid enrolment type ({$enrolment->enrolment_type})";
            }
            
            // Valid status
            if (!in_array($enrolment->status, ['active', 'completed', 'withdrawn', 'deferred', 'failed'])) {
                $errors[] = "Enrolment ID {$enrolment->id}: Invalid status ({$enrolment->status})";
            }
        }
        
        return $errors;
    }

    /**
     * Validate grade record integrity
     */
    private function validateGradeRecordIntegrity(): array
    {
        $errors = [];
        
        $gradeRecords = StudentGradeRecord::with(['student', 'moduleInstance'])->get();
        
        foreach ($gradeRecords as $record) {
            // Must have valid student and module instance
            if (!$record->student) {
                $errors[] = "Grade Record ID {$record->id}: Missing student record";
            }
            
            if (!$record->moduleInstance) {
                $errors[] = "Grade Record ID {$record->id}: Missing module instance record";
            }
            
            // Assessment component must exist in module's strategy
            if ($record->moduleInstance && $record->moduleInstance->module) {
                $componentExists = collect($record->moduleInstance->module->assessment_strategy)
                    ->pluck('component_name')
                    ->contains($record->assessment_component_name);
                
                if (!$componentExists) {
                    $errors[] = "Grade Record ID {$record->id}: Assessment component '{$record->assessment_component_name}' not found in module strategy";
                }
            }
            
            // Grade validation
            if ($record->grade !== null) {
                if ($record->grade < 0 || $record->grade > 100) {
                    $errors[] = "Grade Record ID {$record->id}: Invalid grade ({$record->grade})";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Business rule validation (warnings, not errors)
     */
    private function validateBusinessRules(): array
    {
        $warnings = [];
        
        // Check for programmes without instances
        $programmesWithoutInstances = Programme::doesntHave('programmeInstances')->count();
        if ($programmesWithoutInstances > 0) {
            $warnings[] = "{$programmesWithoutInstances} programmes have no instances created";
        }
        
        // Check for modules without instances
        $modulesWithoutInstances = Module::doesntHave('moduleInstances')->count();
        if ($modulesWithoutInstances > 0) {
            $warnings[] = "{$modulesWithoutInstances} modules have no instances created";
        }
        
        // Check for instances without enrolments
        $instancesWithoutEnrolments = ModuleInstance::doesntHave('enrolments')->count();
        if ($instancesWithoutEnrolments > 0) {
            $warnings[] = "{$instancesWithoutEnrolments} module instances have no student enrolments";
        }
        
        // Check for unassigned tutors
        $instancesWithoutTutors = ModuleInstance::whereNull('tutor_id')->count();
        if ($instancesWithoutTutors > 0) {
            $warnings[] = "{$instancesWithoutTutors} module instances have no assigned tutor";
        }
        
        // Check for orphaned grade records (student has grades but no active enrolment)
        $orphanedGrades = $this->detectOrphanedGradeRecords();
        if ($orphanedGrades['count'] > 0) {
            $warnings[] = "{$orphanedGrades['count']} students have grade records without active enrolments (possible unenrollment aftermath)";
        }
        
        return $warnings;
    }

    /**
     * Generate comprehensive system statistics
     */
    private function generateSystemStats(): array
    {
        return [
            'programmes' => Programme::count(),
            'programme_instances' => ProgrammeInstance::count(),
            'modules' => Module::count(),
            'module_instances' => ModuleInstance::count(),
            'total_enrolments' => Enrolment::count(),
            'active_enrolments' => Enrolment::where('status', 'active')->count(),
            'programme_enrolments' => Enrolment::where('enrolment_type', 'programme')->count(),
            'module_enrolments' => Enrolment::where('enrolment_type', 'module')->count(),
            'grade_records' => StudentGradeRecord::count(),
            'graded_records' => StudentGradeRecord::whereNotNull('grade')->count(),
            'curriculum_links' => DB::table('programme_instance_curriculum')->count(),
        ];
    }

    /**
     * Validate specific programme instance curriculum
     */
    public function validateProgrammeCurriculum(ProgrammeInstance $programmeInstance): array
    {
        $errors = [];
        $warnings = [];
        
        $moduleInstances = $programmeInstance->moduleInstances;
        
        if ($moduleInstances->isEmpty()) {
            $warnings[] = "Programme instance has no modules in curriculum";
            return ['errors' => $errors, 'warnings' => $warnings];
        }
        
        // Check total credits
        $totalCredits = $moduleInstances->sum(function ($instance) {
            return $instance->module->credit_value;
        });
        
        $expectedCredits = $programmeInstance->programme->total_credits;
        
        if ($totalCredits < $expectedCredits) {
            $warnings[] = "Curriculum credits ({$totalCredits}) less than programme requirement ({$expectedCredits})";
        } elseif ($totalCredits > $expectedCredits) {
            $warnings[] = "Curriculum credits ({$totalCredits}) exceed programme requirement ({$expectedCredits})";
        }
        
        // Check for delivery style mismatches
        $syncInstances = $moduleInstances->where('delivery_style', 'sync')->count();
        $asyncInstances = $moduleInstances->where('delivery_style', 'async')->count();
        
        if ($programmeInstance->default_delivery_style === 'sync' && $asyncInstances > 0) {
            $warnings[] = "Sync programme contains {$asyncInstances} async module instances";
        }
        
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Auto-fix common issues where possible
     */
    public function autoFixIssues(): array
    {
        $fixed = [];
        $failed = [];
        
        try {
            DB::beginTransaction();
            
            // Remove orphaned curriculum links
            $orphanedCount = DB::table('programme_instance_curriculum')
                ->leftJoin('programme_instances', 'programme_instance_curriculum.programme_instance_id', '=', 'programme_instances.id')
                ->leftJoin('module_instances', 'programme_instance_curriculum.module_instance_id', '=', 'module_instances.id')
                ->where(function ($query) {
                    $query->whereNull('programme_instances.id')
                          ->orWhereNull('module_instances.id');
                })
                ->delete();
            
            if ($orphanedCount > 0) {
                $fixed[] = "Removed {$orphanedCount} orphaned curriculum links";
            }
            
            // Remove orphaned grade records
            $orphanedGrades = StudentGradeRecord::whereDoesntHave('student')->orWhereDoesntHave('moduleInstance')->delete();
            if ($orphanedGrades > 0) {
                $fixed[] = "Removed {$orphanedGrades} orphaned grade records";
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $failed[] = "Auto-fix failed: " . $e->getMessage();
            Log::error('Architecture validation auto-fix failed', ['error' => $e->getMessage()]);
        }
        
        return ['fixed' => $fixed, 'failed' => $failed];
    }

    /**
     * Detect orphaned grade records (students with grades but no active enrolments)
     * This typically happens after accidental unenrollments
     */
    private function detectOrphanedGradeRecords(): array
    {
        // Find students who have grade records but no active enrolments for those module instances
        $orphanedGrades = DB::select("
            SELECT DISTINCT 
                sgr.student_id,
                s.student_number,
                s.first_name,
                s.last_name,
                sgr.module_instance_id,
                mi.start_date as module_start_date,
                m.title as module_title,
                m.module_code,
                COUNT(sgr.id) as grade_count
            FROM student_grade_records sgr
            JOIN students s ON sgr.student_id = s.id
            JOIN module_instances mi ON sgr.module_instance_id = mi.id
            JOIN modules m ON mi.module_id = m.id
            LEFT JOIN enrolments e ON (
                sgr.student_id = e.student_id 
                AND (
                    (e.enrolment_type = 'module' AND e.module_instance_id = sgr.module_instance_id)
                    OR 
                    (e.enrolment_type = 'programme' AND EXISTS (
                        SELECT 1 FROM programme_instance_curriculum pic 
                        WHERE pic.programme_instance_id = e.programme_instance_id 
                        AND pic.module_instance_id = sgr.module_instance_id
                    ))
                )
                AND e.status = 'active'
                AND e.deleted_at IS NULL
            )
            WHERE e.id IS NULL
            AND sgr.deleted_at IS NULL
            GROUP BY sgr.student_id, sgr.module_instance_id, s.student_number, s.first_name, s.last_name, 
                     mi.start_date, m.title, m.module_code
            ORDER BY s.student_number, m.module_code
        ");

        return [
            'count' => count($orphanedGrades),
            'details' => $orphanedGrades
        ];
    }
}