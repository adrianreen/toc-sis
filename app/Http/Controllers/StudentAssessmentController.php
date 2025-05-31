<?php
// app/Http/Controllers/StudentAssessmentController.php

namespace App\Http\Controllers;

use App\Models\StudentAssessment;
use App\Models\StudentModuleEnrolment;
use App\Models\ModuleInstance;
use App\Models\AssessmentComponent;
use App\Models\Student;
use Illuminate\Support\Collection;
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
        
        // Visibility controls
        'visibility_control' => 'required|in:show_now,hide,schedule',
        'release_date' => 'required_if:visibility_control,schedule|nullable|date|after:now',
        'release_notes' => 'nullable|string|max:500',
    ]);

    DB::transaction(function () use ($validated, $studentAssessment) {
        // Update the assessment grade and feedback
        $updateData = [
            'grade' => $validated['grade'],
            'status' => $validated['grade'] >= 40 ? 'passed' : 'failed',
            'feedback' => $validated['feedback'],
            'submission_date' => $validated['submission_date'] ?? now(),
            'graded_date' => now(),
            'graded_by' => auth()->id(),
        ];

        // Handle visibility settings
        switch ($validated['visibility_control']) {
            case 'show_now':
                $updateData['is_visible_to_student'] = true;
                $updateData['release_date'] = null;
                $updateData['visibility_changed_by'] = auth()->id();
                $updateData['visibility_changed_at'] = now();
                $updateData['release_notes'] = $validated['release_notes'];
                break;
                
            case 'hide':
                $updateData['is_visible_to_student'] = false;
                $updateData['release_date'] = null;
                $updateData['visibility_changed_by'] = auth()->id();
                $updateData['visibility_changed_at'] = now();
                $updateData['release_notes'] = $validated['release_notes'];
                break;
                
            case 'schedule':
                $updateData['is_visible_to_student'] = false;
                $updateData['release_date'] = $validated['release_date'];
                $updateData['visibility_changed_by'] = auth()->id();
                $updateData['visibility_changed_at'] = now();
                $updateData['release_notes'] = $validated['release_notes'];
                break;
        }

        $studentAssessment->update($updateData);

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
                'visibility_control' => $validated['visibility_control'],
                'release_date' => $validated['release_date'] ?? null,
            ])
            ->log('Assessment graded: ' . $studentAssessment->assessmentComponent->name . ' - ' . $validated['grade'] . '% (' . $validated['visibility_control'] . ')');
    });

    return redirect()
        ->route('assessments.module-instance', $studentAssessment->studentModuleEnrolment->moduleInstance)
        ->with('success', 'Grade and visibility settings saved successfully.');
}
/**
 * Quick visibility action without changing grade
 */
public function quickVisibility(Request $request, StudentAssessment $studentAssessment)
{
    // Check permission
    $moduleInstance = $studentAssessment->studentModuleEnrolment->moduleInstance;
    if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
        abort(403);
    }

    $validated = $request->validate([
        'action' => 'required|in:show,hide',
        'notes' => 'nullable|string|max:500',
    ]);

    DB::transaction(function () use ($validated, $studentAssessment) {
        if ($validated['action'] === 'show') {
            $studentAssessment->showToStudent($validated['notes'] ?? 'Quick action: Made visible');
        } else {
            $studentAssessment->hideFromStudent($validated['notes'] ?? 'Quick action: Hidden');
        }
    });

    $message = $validated['action'] === 'show' ? 'Assessment made visible to student' : 'Assessment hidden from student';
    
    return redirect()->back()->with('success', $message);
}

/**
 * Bulk visibility management for module assessments
 */
public function bulkVisibility(Request $request, ModuleInstance $moduleInstance, AssessmentComponent $assessmentComponent)
{
    // Check permission
    if (auth()->user()->role === 'teacher' && auth()->id() !== $moduleInstance->teacher_id) {
        abort(403);
    }

    $validated = $request->validate([
        'action' => 'required|in:show_all,hide_all,schedule_all',
        'release_date' => 'required_if:action,schedule_all|nullable|date|after:now',
        'notes' => 'nullable|string|max:500',
    ]);

    $assessments = StudentAssessment::whereHas('studentModuleEnrolment', function($query) use ($moduleInstance) {
            $query->where('module_instance_id', $moduleInstance->id);
        })
        ->where('assessment_component_id', $assessmentComponent->id)
        ->whereNotNull('grade')
        ->get();

    $updatedCount = 0;

    DB::transaction(function () use ($validated, $assessments, &$updatedCount) {
        foreach ($assessments as $assessment) {
            switch ($validated['action']) {
                case 'show_all':
                    $assessment->showToStudent($validated['notes'] ?? 'Bulk action: Show all');
                    $updatedCount++;
                    break;
                    
                case 'hide_all':
                    $assessment->hideFromStudent($validated['notes'] ?? 'Bulk action: Hide all');
                    $updatedCount++;
                    break;
                    
                case 'schedule_all':
                    $assessment->scheduleRelease(
                        Carbon::parse($validated['release_date']), 
                        $validated['notes'] ?? 'Bulk action: Scheduled release'
                    );
                    $updatedCount++;
                    break;
            }
        }

        // Log bulk action
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => $validated['action'],
                'assessment_component' => $assessmentComponent->name,
                'module_instance' => $moduleInstance->instance_code,
                'count' => $updatedCount,
            ])
            ->log("Bulk visibility action: {$validated['action']} applied to {$updatedCount} assessments");
    });

    return redirect()
        ->route('assessments.module-instance', $moduleInstance)
        ->with('success', "Visibility updated for {$updatedCount} assessments.");
}

/**
 * Auto-release scheduled assessments (for cron job)
 */
public function processScheduledReleases()
{
    $assessments = StudentAssessment::where('is_visible_to_student', false)
        ->whereNotNull('release_date')
        ->where('release_date', '<=', now())
        ->whereNotNull('grade')
        ->get();

    $releasedCount = 0;

    foreach ($assessments as $assessment) {
        $assessment->update([
            'is_visible_to_student' => true,
            'visibility_changed_at' => now(),
        ]);

        activity()
            ->performedOn($assessment)
            ->log('Assessment auto-released on schedule');

        $releasedCount++;
    }

    \Log::info("Auto-released {$releasedCount} scheduled assessments");

    return $releasedCount;
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
 * Show comprehensive student progress
 */
public function studentProgress(Student $student)
{
    // Load student with all related data
    $student->load([
        'enrolments.programme',
        'enrolments.cohort',
        'studentModuleEnrolments.moduleInstance.module',
        'studentModuleEnrolments.moduleInstance.cohort',
        'studentModuleEnrolments.studentAssessments.assessmentComponent',
        'createdBy',
        'updatedBy'
    ]);

    // Calculate overall statistics
    $stats = $this->calculateStudentStats($student);
    
    // Get programme progress
    $programmeProgress = $this->getProgrammeProgress($student);
    
    // Get module progress details
    $moduleProgress = $this->getModuleProgress($student);
    
    // Get recent activity
    $recentActivity = $this->getStudentActivity($student);
    
    // Get upcoming deadlines
    $upcomingDeadlines = $this->getUpcomingDeadlines($student);
    
    return view('assessments.student-progress', compact(
        'student',
        'stats',
        'programmeProgress',
        'moduleProgress',
        'recentActivity',
        'upcomingDeadlines'
    ));
}

/**
 * Calculate overall student statistics
 */
protected function calculateStudentStats(Student $student): array
{
    $totalAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->count();
    
    $completedAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->whereIn('status', ['graded', 'passed', 'failed'])
        ->count();
    
    $passedAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->where('status', 'passed')
        ->count();
    
    $failedAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->where('status', 'failed')
        ->count();
    
    $pendingAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->where('status', 'pending')
        ->count();
    
    $submittedAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->where('status', 'submitted')
        ->count();
    
    // Calculate overall grade average
    $gradedAssessments = $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->whereNotNull('grade');
    
    $overallAverage = $gradedAssessments->count() > 0 
        ? $gradedAssessments->avg('grade') 
        : null;
    
    return [
        'total_assessments' => $totalAssessments,
        'completed_assessments' => $completedAssessments,
        'passed_assessments' => $passedAssessments,
        'failed_assessments' => $failedAssessments,
        'pending_assessments' => $pendingAssessments,
        'submitted_assessments' => $submittedAssessments,
        'completion_percentage' => $totalAssessments > 0 ? round(($completedAssessments / $totalAssessments) * 100, 1) : 0,
        'pass_rate' => $completedAssessments > 0 ? round(($passedAssessments / $completedAssessments) * 100, 1) : 0,
        'overall_average' => $overallAverage ? round($overallAverage, 1) : null,
    ];
}

/**
 * Get programme-level progress
 */
protected function getProgrammeProgress(Student $student): Collection
{
    return $student->enrolments->map(function ($enrolment) {
        $moduleEnrolments = $enrolment->student->studentModuleEnrolments
            ->where('enrolment_id', $enrolment->id);
        
        $totalModules = $moduleEnrolments->count();
        $completedModules = $moduleEnrolments->where('status', 'completed')->count();
        $activeModules = $moduleEnrolments->where('status', 'active')->count();
        $failedModules = $moduleEnrolments->where('status', 'failed')->count();
        
        // Calculate weighted average for the programme
        $completedWithGrades = $moduleEnrolments->where('status', 'completed')->whereNotNull('final_grade');
        $programmeAverage = $completedWithGrades->count() > 0 
            ? $completedWithGrades->avg('final_grade') 
            : null;
        
        return [
            'enrolment' => $enrolment,
            'total_modules' => $totalModules,
            'completed_modules' => $completedModules,
            'active_modules' => $activeModules,
            'failed_modules' => $failedModules,
            'completion_percentage' => $totalModules > 0 ? round(($completedModules / $totalModules) * 100, 1) : 0,
            'programme_average' => $programmeAverage ? round($programmeAverage, 1) : null,
        ];
    });
}

/**
 * Get detailed module progress
 */
protected function getModuleProgress(Student $student): Collection
{
    return $student->studentModuleEnrolments->map(function ($moduleEnrolment) {
        $assessments = $moduleEnrolment->studentAssessments;
        
        $totalAssessments = $assessments->count();
        $completedAssessments = $assessments->whereIn('status', ['graded', 'passed', 'failed'])->count();
        $passedAssessments = $assessments->where('status', 'passed')->count();
        $failedAssessments = $assessments->where('status', 'failed')->count();
        
        // Calculate weighted final grade if all assessments are complete
        $finalGrade = null;
        if ($completedAssessments === $totalAssessments && $totalAssessments > 0) {
            $weightedSum = 0;
            $totalWeight = 0;
            
            foreach ($assessments as $assessment) {
                if ($assessment->grade !== null) {
                    $weight = $assessment->assessmentComponent->weight;
                    $weightedSum += ($assessment->grade * $weight);
                    $totalWeight += $weight;
                }
            }
            
            if ($totalWeight > 0) {
                $finalGrade = round($weightedSum / $totalWeight, 1);
                
                // Update the module enrolment if needed
                if ($moduleEnrolment->final_grade !== $finalGrade) {
                    $moduleEnrolment->update([
                        'final_grade' => $finalGrade,
                        'status' => $finalGrade >= 40 ? 'completed' : 'failed',
                        'completion_date' => $finalGrade >= 40 ? now() : null,
                    ]);
                }
            }
        }
        
        return [
            'module_enrolment' => $moduleEnrolment,
            'assessments' => $assessments->sortBy('assessmentComponent.sequence'),
            'total_assessments' => $totalAssessments,
            'completed_assessments' => $completedAssessments,
            'passed_assessments' => $passedAssessments,
            'failed_assessments' => $failedAssessments,
            'completion_percentage' => $totalAssessments > 0 ? round(($completedAssessments / $totalAssessments) * 100, 1) : 0,
            'final_grade' => $finalGrade,
            'status' => $finalGrade !== null ? ($finalGrade >= 40 ? 'completed' : 'failed') : 'in_progress',
        ];
    })->sortBy('module_enrolment.created_at');
}

/**
 * Get recent student activity
 */
protected function getStudentActivity(Student $student): Collection
{
    return \Spatie\Activitylog\Models\Activity::where(function ($query) use ($student) {
        $query->where('subject_type', Student::class)
              ->where('subject_id', $student->id)
              ->orWhere('properties->student_id', $student->id)
              ->orWhere('description', 'like', "%{$student->student_number}%");
    })
    ->with('causer')
    ->latest()
    ->limit(10)
    ->get();
}

/**
 * Get upcoming deadlines
 */
protected function getUpcomingDeadlines(Student $student): Collection
{
    return $student->studentModuleEnrolments
        ->flatMap->studentAssessments
        ->where('status', 'pending')
        ->where('due_date', '>=', now())
        ->sortBy('due_date')
        ->take(5)
        ->values();
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