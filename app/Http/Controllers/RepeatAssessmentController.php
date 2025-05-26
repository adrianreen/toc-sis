<?php
// app/Http/Controllers/RepeatAssessmentController.php

namespace App\Http\Controllers;

use App\Models\RepeatAssessment;
use App\Models\StudentAssessment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepeatAssessmentController extends Controller
{
    public function index()
    {
        $repeats = RepeatAssessment::with([
            'student',
            'studentAssessment.assessmentComponent',
            'moduleInstance.module'
        ])->latest()->paginate(20);

        return view('repeat-assessments.index', compact('repeats'));
    }

    public function create(Student $student)
    {
        // Get failed assessments
        $failedAssessments = StudentAssessment::whereHas('studentModuleEnrolment', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->where('status', 'failed')
            ->with(['assessmentComponent.module', 'studentModuleEnrolment.moduleInstance'])
            ->get();

        return view('repeat-assessments.create', compact('student', 'failedAssessments'));
    }

    public function store(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_assessment_id' => 'required|exists:student_assessments,id',
            'reason' => 'required|string|max:1000',
            'repeat_due_date' => 'required|date|after:today',
            'cap_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        $assessment = StudentAssessment::findOrFail($validated['student_assessment_id']);

        DB::transaction(function () use ($validated, $student, $assessment) {
            // Create repeat assessment
            $repeat = RepeatAssessment::create([
                'student_assessment_id' => $assessment->id,
                'student_id' => $student->id,
                'module_instance_id' => $assessment->studentModuleEnrolment->module_instance_id,
                'reason' => $validated['reason'],
                'repeat_due_date' => $validated['repeat_due_date'],
                'cap_grade' => $validated['cap_grade'] ?? 40, // Default cap at 40%
                'status' => 'pending',
            ]);

            // Create new assessment attempt
            StudentAssessment::create([
                'student_module_enrolment_id' => $assessment->student_module_enrolment_id,
                'assessment_component_id' => $assessment->assessment_component_id,
                'attempt_number' => $assessment->attempt_number + 1,
                'due_date' => $validated['repeat_due_date'],
                'status' => 'pending',
            ]);

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties(['repeat_id' => $repeat->id])
                ->log('Repeat assessment created for ' . $assessment->assessmentComponent->name);
        });

        return redirect()->route('repeat-assessments.index')
            ->with('success', 'Repeat assessment created successfully.');
    }

    public function approve(RepeatAssessment $repeat)
    {
        $repeat->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Repeat assessment approved.');
    }
}