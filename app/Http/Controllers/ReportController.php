<?php

// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Deferral;
use App\Models\Programme;
use App\Models\Student;

class ReportController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'deferred_students' => Student::where('status', 'deferred')->count(),
            'total_programmes' => Programme::count(),
            'active_programme_instances' => \App\Models\ProgrammeInstance::whereDate('intake_start_date', '<=', now())
                ->whereDate('intake_end_date', '>=', now())->count(),
            'pending_deferrals' => Deferral::where('status', 'pending')->count(),
            'total_module_instances' => \App\Models\ModuleInstance::count(),
        ];

        // Get programme breakdown with active enrolments
        $programmeStats = Programme::withCount([
            'programmeInstances as active_instances_count' => function ($query) {
                $query->whereDate('intake_start_date', '<=', now())
                    ->whereDate('intake_end_date', '>=', now());
            },
        ])->with(['programmeInstances' => function ($query) {
            $query->withCount(['enrolments' => function ($subQuery) {
                $subQuery->where('enrolment_type', 'programme')
                    ->whereHas('student', function ($studentQuery) {
                        $studentQuery->where('status', 'active');
                    });
            }]);
        }])->get();

        // Get recent activities
        $recentActivities = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->latest()
            ->limit(10)
            ->get();

        return view('reports.dashboard', compact('stats', 'programmeStats', 'recentActivities'));
    }

    public function programmeInstanceList(\App\Models\ProgrammeInstance $programmeInstance)
    {
        $students = Student::whereHas('enrolments', function ($query) use ($programmeInstance) {
            $query->where('programme_instance_id', $programmeInstance->id)
                ->where('enrolment_type', 'programme')
                ->whereHas('student', function ($studentQuery) {
                    $studentQuery->where('status', 'active');
                });
        })->with(['enrolments' => function ($query) use ($programmeInstance) {
            $query->where('programme_instance_id', $programmeInstance->id);
        }])->get();

        return view('reports.programme-instance-list', compact('programmeInstance', 'students'));
    }

    public function studentProgress(Student $student)
    {
        $student->load([
            'enrolments.programmeInstance.programme',
            'enrolments.moduleInstance.module',
            'studentGradeRecords' => function ($query) {
                $query->with('moduleInstance.module')
                    ->where(function ($q) {
                        $q->where('is_visible_to_student', true)
                            ->orWhere(function ($subQ) {
                                $subQ->whereNotNull('release_date')
                                    ->where('release_date', '<=', now());
                            });
                    });
            },
        ]);

        // Calculate module progress from grade records
        $moduleProgress = $student->studentGradeRecords
            ->groupBy('module_instance_id')
            ->map(function ($gradeRecords) {
                $moduleInstance = $gradeRecords->first()->moduleInstance;
                $module = $moduleInstance->module;

                $totalComponents = count($module->assessment_strategy ?? []);
                $gradedComponents = $gradeRecords->whereNotNull('grade')->count();

                // Calculate overall grade from assessment strategy
                $totalWeightedMark = 0;
                $totalWeight = 0;

                foreach ($module->assessment_strategy ?? [] as $component) {
                    $gradeRecord = $gradeRecords->where('assessment_component_name', $component['component_name'])->first();
                    if ($gradeRecord && $gradeRecord->grade !== null) {
                        $totalWeightedMark += ($gradeRecord->percentage * $component['weighting'] / 100);
                        $totalWeight += $component['weighting'];
                    }
                }

                $finalGrade = $totalWeight > 0 ? round($totalWeightedMark, 1) : null;
                $status = $gradedComponents === $totalComponents ? 'completed' : 'in_progress';

                return (object) [
                    'code' => $module->module_code,
                    'title' => $module->title,
                    'status' => $status,
                    'final_grade' => $finalGrade,
                    'progress' => $totalComponents > 0 ? round(($gradedComponents / $totalComponents) * 100) : 0,
                ];
            })
            ->values();

        return view('reports.student-progress', compact('student', 'moduleProgress'));
    }
}
