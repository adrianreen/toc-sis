<?php

namespace App\Http\Controllers;

use App\Models\StudentGradeRecord;
use App\Models\ModuleInstance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentGradeRecordController extends Controller
{
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
                ->paginate(20);
        } elseif ($user->role === 'teacher') {
            $moduleInstances = ModuleInstance::with(['module', 'tutor', 'studentGradeRecords'])
                ->where('tutor_id', $user->id)
                ->orderBy('start_date', 'desc')
                ->paginate(20);
        } else {
            $moduleInstances = collect();
        }

        // Calculate stats
        $stats = [
            'total_assessments' => $moduleInstances->sum(function($instance) {
                return $instance->studentGradeRecords->count();
            }),
            'pending_grading' => $moduleInstances->sum(function($instance) {
                return $instance->studentGradeRecords->whereNull('percentage')->count();
            }),
            'graded_today' => $moduleInstances->sum(function($instance) {
                return $instance->studentGradeRecords->where('grading_date', '>=', today())->count();
            }),
            'overdue_release' => $moduleInstances->sum(function($instance) {
                return $instance->studentGradeRecords
                    ->whereNotNull('percentage')
                    ->where('is_visible_to_student', false)
                    ->where('release_date', '<', now())
                    ->count();
            })
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
            }
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
            'grade' => 'nullable|numeric|min:0|max:' . $gradeRecord->max_grade,
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

        return response()->json([
            'success' => true,
            'message' => 'Grade updated successfully.',
            'percentage' => $gradeRecord->percentage
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
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update grades: ' . $e->getMessage()])
                ->withInput();
        }
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

        return response()->json([
            'success' => true,
            'message' => "Made {$component} {$action} to students.",
            'updated_count' => $updateCount
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
            ->with('success', "Scheduled release of {$component} for " . $validated['release_date']);
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
            }
        ]);

        // Group by student for export
        $exportData = $moduleInstance->studentGradeRecords
            ->groupBy('student_id')
            ->map(function ($grades, $studentId) {
                $student = $grades->first()->student;
                $gradeData = ['student_number' => $student->student_number, 'student_name' => $student->full_name];
                
                foreach ($grades as $grade) {
                    $gradeData[$grade->assessment_component_name] = $grade->grade;
                    $gradeData[$grade->assessment_component_name . '_percentage'] = $grade->percentage;
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

        if (!$student) {
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
            'component_results' => []
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
                'status' => 'pending'
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
}