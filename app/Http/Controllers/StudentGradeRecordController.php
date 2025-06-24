<?php

namespace App\Http\Controllers;

use App\Http\Traits\HasStudentSearch;
use App\Models\ModuleInstance;
use App\Models\Student;
use App\Models\StudentGradeRecord;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentGradeRecordController extends Controller
{
    use HasStudentSearch;
    /**
     * Show assessment management interface for staff
     */
    public function index()
    {
        $user = auth()->user();

        // Get module instances based on user role
        if ($user->role === 'manager') {
            $moduleInstances = ModuleInstance::with(['module', 'tutor', 'studentGradeRecords'])
                ->orderBy('start_date', 'desc')
                ->paginate($this->getPaginationSize('grade_records'));
        } elseif ($user->role === 'teacher') {
            $moduleInstances = ModuleInstance::with(['module', 'tutor', 'studentGradeRecords'])
                ->where('tutor_id', $user->id)
                ->orderBy('start_date', 'desc')
                ->paginate($this->getPaginationSize('grade_records'));
        } else {
            $moduleInstances = collect();
        }

        // Calculate stats
        $stats = [
            'total_assessments' => $moduleInstances->sum(function ($instance) {
                return $instance->studentGradeRecords->count();
            }),
            'pending_grading' => $moduleInstances->sum(function ($instance) {
                return $instance->studentGradeRecords->whereNull('percentage')->count();
            }),
            'graded_today' => $moduleInstances->sum(function ($instance) {
                return $instance->studentGradeRecords->where('grading_date', '>=', today())->count();
            }),
            'overdue_release' => $moduleInstances->sum(function ($instance) {
                return $instance->studentGradeRecords
                    ->whereNotNull('percentage')
                    ->where('is_visible_to_student', false)
                    ->where('release_date', '<', now())
                    ->count();
            }),
        ];

        return view('assessments.index', compact('moduleInstances', 'stats'));
    }

    /**
     * Show grading interface for a module instance
     */
    public function moduleGrading(ModuleInstance $moduleInstance)
    {
        $moduleInstance->load([
            'module',
            'tutor',
            'studentGradeRecords' => function ($query) {
                $query->with('student')
                    ->orderBy('student_id')
                    ->orderBy('assessment_component_name');
            },
        ]);

        // Group grade records by student
        $studentGrades = $moduleInstance->studentGradeRecords
            ->groupBy('student_id')
            ->map(function ($grades) {
                return $grades->keyBy('assessment_component_name');
            });

        return view('grade-records.module-grading', compact('moduleInstance', 'studentGrades'));
    }

    /**
     * Update a single grade record
     */
    public function update(Request $request, StudentGradeRecord $gradeRecord)
    {
        $validated = $request->validate([
            'grade' => 'nullable|numeric|min:0|max:'.$gradeRecord->max_grade,
            'max_grade' => 'required|numeric|min:1',
            'feedback' => 'nullable|string',
            'submission_date' => 'nullable|date',
            'is_visible_to_student' => 'required|boolean',
            'release_date' => 'nullable|date',
        ]);

        $gradeRecord->update([
            'grade' => $validated['grade'],
            'max_grade' => $validated['max_grade'],
            'feedback' => $validated['feedback'],
            'submission_date' => $validated['submission_date'],
            'graded_date' => $validated['grade'] ? now() : null,
            'graded_by_staff_id' => $validated['grade'] ? auth()->id() : null,
            'is_visible_to_student' => $validated['is_visible_to_student'],
            'release_date' => $validated['release_date'],
        ]);

        return $this->successResponse('Grade updated successfully.', [
            'percentage' => $gradeRecord->percentage,
        ]);
    }

    /**
     * Bulk update grades for multiple students
     */
    public function bulkUpdate(Request $request, ModuleInstance $moduleInstance)
    {
        try {
            $validated = $request->validate([
                'grades' => 'required|array',
                'grades.*.id' => 'required|exists:student_grade_records,id',
                'grades.*.grade' => 'nullable|numeric|min:0|max:100',
                'grades.*.feedback' => 'nullable|string',
                'grades.*.is_visible_to_student' => 'nullable', // Fix: handle checkbox properly
            ]);

            $updatedCount = 0;

            DB::transaction(function () use ($validated, &$updatedCount) {
                foreach ($validated['grades'] as $gradeData) {
                    $gradeRecord = StudentGradeRecord::findOrFail($gradeData['id']);

                    // Fix: properly handle checkbox - if present = true, if absent = false
                    $isVisible = isset($gradeData['is_visible_to_student']) && $gradeData['is_visible_to_student'];

                    $gradeRecord->update([
                        'grade' => $gradeData['grade'] ?? null,
                        'feedback' => $gradeData['feedback'] ?? null,
                        'graded_date' => isset($gradeData['grade']) && $gradeData['grade'] !== null ? now() : $gradeRecord->graded_date,
                        'graded_by_staff_id' => isset($gradeData['grade']) && $gradeData['grade'] !== null ? auth()->id() : $gradeRecord->graded_by_staff_id,
                        'is_visible_to_student' => $isVisible,
                    ]);

                    $updatedCount++;
                }
            });

            return redirect()->route('grade-records.module-grading', $moduleInstance)
                ->with('success', "Successfully updated grades for {$updatedCount} records.");

        } catch (Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update grades: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Toggle visibility for a single grade record
     */
    public function toggleSingleVisibility(StudentGradeRecord $gradeRecord)
    {
        $newVisibility = ! $gradeRecord->is_visible_to_student;

        $gradeRecord->update([
            'is_visible_to_student' => $newVisibility,
            'release_date' => $newVisibility ? now() : null,
        ]);

        return $this->successResponse('Grade visibility updated successfully.', [
            'is_visible' => $newVisibility,
        ]);
    }

    /**
     * Toggle visibility for all grades in a module instance
     */
    public function toggleVisibility(Request $request, ModuleInstance $moduleInstance)
    {
        $validated = $request->validate([
            'visible' => 'required|boolean',
            'component_name' => 'nullable|string', // Optional: specific component
        ]);

        $query = $moduleInstance->studentGradeRecords();

        if ($validated['component_name']) {
            $query->where('assessment_component_name', $validated['component_name']);
        }

        $updateCount = $query->update([
            'is_visible_to_student' => $validated['visible'],
            'release_date' => $validated['visible'] ? now() : null,
        ]);

        $component = $validated['component_name'] ?? 'all assessments';
        $action = $validated['visible'] ? 'visible' : 'hidden';

        return $this->successResponse("Made {$component} {$action} to students.", [
            'updated_count' => $updateCount,
        ]);
    }

    /**
     * Bulk update visibility for a specific assessment component
     */
    public function bulkComponentVisibility(Request $request, ModuleInstance $moduleInstance)
    {
        $validated = $request->validate([
            'component_name' => 'required|string',
            'visible' => 'required|boolean',
        ]);

        $updateCount = $moduleInstance->studentGradeRecords()
            ->where('assessment_component_name', $validated['component_name'])
            ->whereNotNull('grade') // Only update graded assessments
            ->update([
                'is_visible_to_student' => $validated['visible'],
                'release_date' => $validated['visible'] ? now() : null,
            ]);

        $action = $validated['visible'] ? 'visible' : 'hidden';

        return $this->successResponse("Made all {$validated['component_name']} grades {$action} to students.", [
            'updated_count' => $updateCount,
        ]);
    }

    /**
     * Schedule release date for grades
     */
    public function scheduleRelease(Request $request, ModuleInstance $moduleInstance)
    {
        $validated = $request->validate([
            'release_date' => 'required|date|after:now',
            'component_name' => 'nullable|string',
        ]);

        $query = $moduleInstance->studentGradeRecords()
            ->whereNotNull('grade'); // Only schedule graded assessments

        if ($validated['component_name']) {
            $query->where('assessment_component_name', $validated['component_name']);
        }

        $query->update([
            'release_date' => $validated['release_date'],
            'is_visible_to_student' => false, // Will become visible on release date
        ]);

        $component = $validated['component_name'] ?? 'all graded assessments';

        return redirect()->route('module-instances.grading', $moduleInstance)
            ->with('success', "Scheduled release of {$component} for ".$validated['release_date']);
    }

    /**
     * Schedule component release (AJAX version)
     */
    public function scheduleComponentRelease(Request $request, ModuleInstance $moduleInstance)
    {
        $validated = $request->validate([
            'component_name' => 'required|string',
            'release_date' => 'required|date|after:now',
        ]);

        $updateCount = $moduleInstance->studentGradeRecords()
            ->where('assessment_component_name', $validated['component_name'])
            ->whereNotNull('grade') // Only schedule graded assessments
            ->update([
                'release_date' => $validated['release_date'],
                'is_visible_to_student' => false, // Will become visible on release date
            ]);

        return $this->successResponse("Scheduled release of {$validated['component_name']} for {$validated['release_date']}", [
            'updated_count' => $updateCount,
        ]);
    }

    /**
     * Export grades for a specific component
     */
    public function exportComponent(Request $request, ModuleInstance $moduleInstance)
    {
        $componentName = $request->query('component');

        if (! $componentName) {
            return $this->errorResponse('Component name is required');
        }

        $gradeRecords = $moduleInstance->studentGradeRecords()
            ->where('assessment_component_name', $componentName)
            ->with('student')
            ->orderBy('student_id')
            ->get();

        $exportData = $gradeRecords->map(function ($gradeRecord) {
            return [
                'student_number' => $gradeRecord->student->student_number,
                'student_name' => $gradeRecord->student->full_name,
                'grade' => $gradeRecord->grade,
                'max_grade' => $gradeRecord->max_grade,
                'percentage' => $gradeRecord->percentage,
                'is_visible' => $gradeRecord->is_visible_to_student,
                'graded_date' => $gradeRecord->graded_date?->format('Y-m-d'),
                'submission_date' => $gradeRecord->submission_date?->format('Y-m-d'),
                'feedback' => $gradeRecord->feedback,
            ];
        });

        return response()->json([
            'component_name' => $componentName,
            'module' => $moduleInstance->module->title,
            'data' => $exportData,
            'total_records' => $exportData->count(),
        ]);
    }

    /**
     * Export grades for a module instance
     */
    public function export(ModuleInstance $moduleInstance)
    {
        $moduleInstance->load([
            'module',
            'studentGradeRecords' => function ($query) {
                $query->with('student')
                    ->orderBy('student_id')
                    ->orderBy('assessment_component_name');
            },
        ]);

        // Group by student for export
        $exportData = $moduleInstance->studentGradeRecords
            ->groupBy('student_id')
            ->map(function ($grades, $studentId) {
                $student = $grades->first()->student;
                $gradeData = ['student_number' => $student->student_number, 'student_name' => $student->full_name];

                foreach ($grades as $grade) {
                    $gradeData[$grade->assessment_component_name] = $grade->grade;
                    $gradeData[$grade->assessment_component_name.'_percentage'] = $grade->percentage;
                }

                return $gradeData;
            });

        return response()->json($exportData);
    }

    /**
     * Show individual student's grade record
     */
    public function show(StudentGradeRecord $gradeRecord)
    {
        $gradeRecord->load(['student', 'moduleInstance.module', 'gradedByStaff']);

        return view('grade-records.show', compact('gradeRecord'));
    }

    /**
     * Student view: Show their own grades
     */
    public function myGrades()
    {
        $user = auth()->user();
        $student = $user->student;

        if (! $student) {
            abort(404, 'Student record not found.');
        }

        $gradeRecords = $student->getCurrentGradeRecords()
            ->with(['moduleInstance.module'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('module_instance_id');

        return view('grade-records.my-grades', compact('student', 'gradeRecords'));
    }

    /**
     * Ensure grade records exist for all students and assessment components
     */
    private function ensureGradeRecordsExist(ModuleInstance $moduleInstance, $enrolledStudents, $assessmentComponents)
    {
        foreach ($enrolledStudents as $student) {
            foreach ($assessmentComponents as $component) {
                $existingRecord = StudentGradeRecord::where([
                    'student_id' => $student->id,
                    'module_instance_id' => $moduleInstance->id,
                    'assessment_component_name' => $component['component_name'],
                ])->first();

                if (! $existingRecord) {
                    StudentGradeRecord::create([
                        'student_id' => $student->id,
                        'module_instance_id' => $moduleInstance->id,
                        'assessment_component_name' => $component['component_name'],
                        'max_grade' => 100, // Default max grade
                        'grade' => null,
                        'feedback' => null,
                        'submission_date' => null,
                        'graded_date' => null,
                        'graded_by_staff_id' => null,
                        'is_visible_to_student' => false,
                        'release_date' => null,
                    ]);
                }
            }
        }
    }

    /**
     * Calculate and show module completion status
     */
    public function moduleCompletion(Student $student, ModuleInstance $moduleInstance)
    {
        $gradeRecords = $student->studentGradeRecords()
            ->where('module_instance_id', $moduleInstance->id)
            ->get();

        $moduleInstance->load('module');
        $assessmentComponents = $moduleInstance->module->assessment_components;

        $completion = [
            'total_components' => count($assessmentComponents),
            'graded_components' => $gradeRecords->whereNotNull('grade')->count(),
            'overall_percentage' => 0,
            'component_results' => [],
        ];

        $totalWeightedScore = 0;
        $totalWeighting = 0;

        foreach ($assessmentComponents as $component) {
            $gradeRecord = $gradeRecords->where('assessment_component_name', $component['component_name'])->first();

            $componentResult = [
                'name' => $component['component_name'],
                'weighting' => $component['weighting'],
                'is_must_pass' => $component['is_must_pass'],
                'component_pass_mark' => $component['component_pass_mark'] ?? 40,
                'grade' => $gradeRecord?->grade,
                'percentage' => $gradeRecord?->percentage,
                'status' => 'pending',
            ];

            if ($gradeRecord && $gradeRecord->grade !== null) {
                $passMark = $component['component_pass_mark'] ?? 40;
                $percentage = $gradeRecord->percentage;

                if ($percentage >= $passMark) {
                    $componentResult['status'] = 'pass';
                } else {
                    $componentResult['status'] = $component['is_must_pass'] ? 'must_pass_fail' : 'fail';
                }

                $totalWeightedScore += $percentage * ($component['weighting'] / 100);
                $totalWeighting += $component['weighting'];
            }

            $completion['component_results'][] = $componentResult;
        }

        if ($totalWeighting > 0) {
            $completion['overall_percentage'] = round($totalWeightedScore, 2);
        }

        return view('grade-records.module-completion', compact('student', 'moduleInstance', 'completion'));
    }

    /**
     * Modern spreadsheet-like grading interface
     */
    public function modernGrading(ModuleInstance $moduleInstance)
    {
        $user = auth()->user();

        // Check permissions
        if (! in_array($user->role, ['manager', 'student_services', 'teacher'])) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'teacher' && $moduleInstance->tutor_id !== $user->id) {
            abort(403, 'You can only grade modules you are assigned to teach');
        }

        // Load module with assessment strategy
        $moduleInstance->load(['module', 'tutor']);
        $assessmentComponents = $moduleInstance->module->assessment_strategy ?? [];

        // Get all enrolled students for this module instance
        $enrolledStudents = Student::whereHas('enrolments', function ($query) use ($moduleInstance) {
            $query->where('status', 'active')
                ->where(function ($q) use ($moduleInstance) {
                    // Students enrolled in programme that includes this module
                    $q->whereHas('programmeInstance.moduleInstances', function ($pq) use ($moduleInstance) {
                        $pq->where('module_instances.id', $moduleInstance->id);
                    })
                    // OR students enrolled directly in this module
                        ->orWhere('module_instance_id', $moduleInstance->id);
                });
        })->with(['enrolments'])->orderBy('last_name')->orderBy('first_name')->get();

        // Ensure grade records exist for all enrolled students and all assessment components
        $this->ensureGradeRecordsExist($moduleInstance, $enrolledStudents, $assessmentComponents);

        // Get all grade records for this module instance, grouped by student
        $gradeRecords = StudentGradeRecord::where('module_instance_id', $moduleInstance->id)
            ->with(['student', 'gradedByStaff'])
            ->get();

        $groupedGradeRecords = $gradeRecords->groupBy('student_id');

        // Calculate statistics
        $stats = [
            'total' => $gradeRecords->count(),
            'graded' => $gradeRecords->whereNotNull('grade')->count(),
            'pending' => $gradeRecords->whereNull('grade')->count(),
            'visible' => $gradeRecords->where('is_visible_to_student', true)->count(),
            'students' => $enrolledStudents->count(),
        ];

        return view('grade-records.modern-grading', compact(
            'moduleInstance',
            'assessmentComponents',
            'enrolledStudents',
            'groupedGradeRecords',
            'stats'
        ));
    }
}
