<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Models\Cohort;
use App\Models\Student;
use App\Models\Enrolment;
use App\Models\Deferral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'deferred_students' => Student::where('status', 'deferred')->count(),
            'total_programmes' => Programme::where('is_active', true)->count(),
            'active_cohorts' => Cohort::where('status', 'active')->count(),
            'pending_deferrals' => Deferral::where('status', 'pending')->count(),
            'pending_extensions' => \App\Models\Extension::where('status', 'pending')->count(),
        ];

        // Get programme breakdown
        $programmeStats = Programme::withCount([
            'enrolments' => function ($query) {
                $query->where('status', 'active');
            }
        ])->where('is_active', true)->get();

        // Get recent activities
        $recentActivities = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->latest()
            ->limit(10)
            ->get();

        return view('reports.dashboard', compact('stats', 'programmeStats', 'recentActivities'));
    }

    public function cohortList(Cohort $cohort)
    {
        $students = Student::whereHas('enrolments', function ($query) use ($cohort) {
            $query->where('cohort_id', $cohort->id)
                  ->where('status', 'active');
        })->with(['enrolments' => function ($query) use ($cohort) {
            $query->where('cohort_id', $cohort->id);
        }])->get();

        return view('reports.cohort-list', compact('cohort', 'students'));
    }

    public function studentProgress(Student $student)
    {
        $student->load([
            'enrolments.programme',
            'enrolments.deferrals',
            'studentModuleEnrolments.moduleInstance.module',
            'studentModuleEnrolments.studentAssessments.assessmentComponent'
        ]);

        $moduleProgress = DB::table('student_module_enrolments as sme')
            ->join('module_instances as mi', 'sme.module_instance_id', '=', 'mi.id')
            ->join('modules as m', 'mi.module_id', '=', 'm.id')
            ->where('sme.student_id', $student->id)
            ->select(
                'm.code',
                'm.title',
                'sme.status',
                'sme.final_grade',
                'sme.attempt_number'
            )
            ->get();

        return view('reports.student-progress', compact('student', 'moduleProgress'));
    }
}