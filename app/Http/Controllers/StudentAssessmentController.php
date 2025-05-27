<?php
// app/Http/Controllers/StudentAssessmentController.php

namespace App\Http\Controllers;

use App\Models\StudentAssessment;
use App\Models\StudentModuleEnrolment;
use App\Models\ModuleInstance;
use App\Models\AssessmentComponent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentAssessmentController extends Controller
{
    /**
     * Show teacher's assigned modules and assessment overview
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'teacher') {
            // Get modules assigned to this teacher
            $moduleInstances = ModuleInstance::where('teacher_id', $user->id)
                ->with(['module', 'cohort.programme', 'studentEnrolments.student'])
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            // Managers can see all module instances
            $moduleInstances = ModuleInstance::with(['module', 'cohort.programme', 'teacher', 'studentEnrolments.student'])
                ->orderBy('start_date', 'desc')
                ->paginate(20);
        }

        // Get assessment statistics
        $stats = $this->getAssessmentStats($user);

        return view('assessments.index', compact('moduleInstances', 'stats'));
    }

    /**
     * Show detailed view of a specific module instance for grading
     */
    public function moduleInstance(ModuleInstance $moduleInstance)
    {
        // Check permission
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403, 'You can only grade students in your assigned modules.');
        }

        $moduleInstance->load([
            'module.assessmentComponents' => function($query) {
                $query->where('is_active', true)->orderBy('sequence');
            },
            'cohort.programme',
            'teacher',
            'studentEnrolments.student',
            'studentEnrolments.studentAssessments.assessmentComponent'
        ]);

        // Get or create student assessments for all enrolled students
        $this->ensureStudentAssessments($moduleInstance);

        // Reload with assessments
        $moduleInstance->load([
            'studentEnrolments.studentAssessments' => function($query) {
                $query->with('assessmentComponent')->orderBy('assessment_component_id');
            }
        ]);

        return view('assessments.module-instance', compact('moduleInstance'));
    }

    /**
     * Show grading form for a specific student's assessment
     */
    public function grade(StudentAssessment $studentAssessment)
    {
        // Check permission
        $moduleInstance = $studentAssessment->studentModuleEnrolment->moduleInstance;
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403);
        }

        $studentAssessment->load([
            'studentModuleEnrolment.student',
            'studentModuleEnrolment.moduleInstance.module',
            'assessmentComponent',
            'gradedBy'
        ]);

        return view('assessments.grade', compact('studentAssessment'));
    }

    /**
     * Store or update a grade for a student assessment
     */
    public function storeGrade(Request $request, StudentAssessment $studentAssessment)
    {
        // Check permission
        $moduleInstance = $studentAssessment->studentModuleEnrolment->moduleInstance;
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403);
        }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:2000',
            'submission_date' => 'nullable|date|before_or_equal:today',
        ]);

        DB::transaction(function () use ($validated, $studentAssessment) {
            // Update the assessment
            $studentAssessment->update([
                'grade' => $validated['grade'],
                'status' => $validated['grade'] >= 40 ? 'passed' : 'failed',
                'feedback' => $validated['feedback'],
                'submission_date' => $validated['submission_date'] ?? now(),
                'graded_date' => now(),
                'graded_by' => auth()->id(),
            ]);

            // Update the parent student module enrolment
            $studentAssessment->studentModuleEnrolment->updateStatus();

            // Log the activity
            activity()
                ->performedOn($studentAssessment->studentModuleEnrolment->student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'module' => $studentAssessment->studentModuleEnrolment->moduleInstance->module->code,
                    'assessment' => $studentAssessment->assessmentComponent->name,
                    'grade' => $validated['grade'],
                ])
                ->log('Assessment graded: ' . $studentAssessment->assessmentComponent->name . ' - ' . $validated['grade'] . '%');
        });

        return redirect()
            ->route('assessments.module-instance', $studentAssessment->studentModuleEnrolment->moduleInstance)
            ->with('success', 'Grade saved successfully.');
    }

    /**
     * Bulk grade entry for multiple students/assessments
     */
    public function bulkGradeForm(ModuleInstance $moduleInstance, AssessmentComponent $assessmentComponent)
    {
        // Check permission
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403);
        }

        $studentAssessments = StudentAssessment::whereHas('studentModuleEnrolment', function($query) use ($moduleInstance) {
                $query->where('module_instance_id', $moduleInstance->id);
            })
            ->where('assessment_component_id', $assessmentComponent->id)
            ->with(['studentModuleEnrolment.student'])
            ->orderBy('id')
            ->get();

        return view('assessments.bulk-grade', compact('moduleInstance', 'assessmentComponent', 'studentAssessments'));
    }

    /**
     * Store bulk grades
     */
    public function storeBulkGrades(Request $request, ModuleInstance $moduleInstance, AssessmentComponent $assessmentComponent)
    {
        // Check permission
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403);
        }

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.assessment_id' => 'required|exists:student_assessments,id',
            'grades.*.grade' => 'nullable|numeric|min:0|max:100',
            'grades.*.feedback' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated, $assessmentComponent) {
            foreach ($validated['grades'] as $gradeData) {
                if (!empty($gradeData['grade'])) {
                    $assessment = StudentAssessment::find($gradeData['assessment_id']);
                    
                    $assessment->update([
                        'grade' => $gradeData['grade'],
                        'status' => $gradeData['grade'] >= 40 ? 'passed' : 'failed',
                        'feedback' => $gradeData['feedback'],
                        'graded_date' => now(),
                        'graded_by' => auth()->id(),
                    ]);

                    // Update parent module enrolment
                    $assessment->studentModuleEnrolment->updateStatus();
                }
            }

            // Log bulk grading activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'assessment_component' => $assessmentComponent->name,
                    'grades_entered' => collect($validated['grades'])->whereNotNull('grade')->count(),
                ])
                ->log('Bulk grades entered for ' . $assessmentComponent->name);
        });

        return redirect()
            ->route('assessments.module-instance', $moduleInstance)
            ->with('success', 'Bulk grades saved successfully.');
    }

    /**
     * Show student progress for a specific student
     */
    public function studentProgress(Student $student)
    {
        $student->load([
            'studentModuleEnrolments.moduleInstance.module',
            'studentModuleEnrolments.studentAssessments.assessmentComponent',
            'enrolments.programme',
            'enrolments.cohort'
        ]);

        return view('assessments.student-progress', compact('student'));
    }

    /**
     * Export grades for a module instance
     */
    public function exportGrades(ModuleInstance $moduleInstance)
    {
        // Check permission
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403);
        }

        $moduleInstance->load([
            'module.assessmentComponents' => function($query) {
                $query->where('is_active', true)->orderBy('sequence');
            },
            'studentEnrolments.student',
            'studentEnrolments.studentAssessments'
        ]);

        return view('assessments.export', compact('moduleInstance'));
    }

    /**
     * Assessment statistics for dashboard
     */
    public function stats()
    {
        $user = auth()->user();
        $stats = $this->getAssessmentStats($user);

        return response()->json($stats);
    }

    /**
     * Get pending assessments that need grading
     */
    public function pending()
    {
        $user = auth()->user();
        
        $query = StudentAssessment::with([
                'studentModuleEnrolment.student',
                'studentModuleEnrolment.moduleInstance.module',
                'assessmentComponent'
            ])
            ->where('status', 'submitted')
            ->orWhere(function($q) {
                $q->where('status', 'pending')
                  ->where('due_date', '<', now());
            });

        if ($user->role === 'teacher') {
            $query->whereHas('studentModuleEnrolment.moduleInstance', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        $pendingAssessments = $query->orderBy('due_date')->paginate(20);

        return view('assessments.pending', compact('pendingAssessments'));
    }

    /**
     * Mark assessment as submitted (for teachers to track submissions)
     */
    public function markSubmitted(StudentAssessment $studentAssessment)
    {
        // Check permission
        $moduleInstance = $studentAssessment->studentModuleEnrolment->moduleInstance;
        if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
            abort(403);
        }

        $studentAssessment->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return back()->with('success', 'Assessment marked as submitted.');
    }

    /**
     * Private helper methods
     */
    private function getAssessmentStats($user)
    {
        $baseQuery = StudentAssessment::query();
        
        if ($user->role === 'teacher') {
            $baseQuery->whereHas('studentModuleEnrolment.moduleInstance', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        return [
            'total_assessments' => $baseQuery->count(),
            'pending_grading' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'overdue' => (clone $baseQuery)->where('status', 'pending')
                ->where('due_date', '<', now())->count(),
            'graded_today' => (clone $baseQuery)->whereDate('graded_date', today())->count(),
            'average_grade' => (clone $baseQuery)->whereNotNull('grade')->avg('grade'),
            'pass_rate' => $this->calculatePassRate($baseQuery),
        ];
    }

    private function calculatePassRate($query)
    {
        $graded = (clone $query)->whereNotNull('grade');
        $total = $graded->count();
        
        if ($total === 0) return 0;
        
        $passed = $graded->where('grade', '>=', 40)->count();
        return round(($passed / $total) * 100, 1);
    }

    private function ensureStudentAssessments(ModuleInstance $moduleInstance)
    {
        $assessmentComponents = $moduleInstance->module->assessmentComponents()
            ->where('is_active', true)
            ->get();

        foreach ($moduleInstance->studentEnrolments as $enrolment) {
            foreach ($assessmentComponents as $component) {
                // Check if assessment already exists
                $exists = StudentAssessment::where('student_module_enrolment_id', $enrolment->id)
                    ->where('assessment_component_id', $component->id)
                    ->where('attempt_number', $enrolment->attempt_number)
                    ->exists();

                if (!$exists) {
                    // Calculate due date based on module schedule
                    $dueDate = $this->calculateDueDate($moduleInstance, $component);

                    StudentAssessment::create([
                        'student_module_enrolment_id' => $enrolment->id,
                        'assessment_component_id' => $component->id,
                        'attempt_number' => $enrolment->attempt_number,
                        'status' => 'pending',
                        'due_date' => $dueDate,
                    ]);
                }
            }
        }
    }

    private function calculateDueDate(ModuleInstance $moduleInstance, AssessmentComponent $component)
    {
        // Calculate due date based on component sequence and module duration
        $moduleWeeks = $moduleInstance->start_date->diffInWeeks($moduleInstance->end_date);
        $componentWeeks = ($component->sequence / ($moduleInstance->module->assessmentComponents()->count() + 1)) * $moduleWeeks;
        
        return $moduleInstance->start_date->addWeeks($componentWeeks);
    }
}