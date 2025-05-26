<?php
// app/Http/Controllers/ExtensionController.php

namespace App\Http\Controllers;

use App\Models\Extension;
use App\Models\Student;
use App\Models\StudentAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtensionController extends Controller
{
    public function index()
    {
        $query = Extension::with([
            'student',
            'studentAssessment.assessmentComponent',
            'studentAssessment.studentModuleEnrolment.moduleInstance.module'
        ]);

        if (auth()->user()->role === 'teacher') {
            // Teachers only see extensions for their modules
            $query->whereHas('studentAssessment.studentModuleEnrolment.moduleInstance', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        $extensions = $query->latest()->paginate(20);

        return view('extensions.index', compact('extensions'));
    }

    public function create(Student $student)
    {
        // Get student's active assessments
        $assessments = StudentAssessment::whereHas('studentModuleEnrolment', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                      ->where('status', 'active');
            })
            ->where('status', 'pending')
            ->where('due_date', '>', now())
            ->with(['assessmentComponent.module', 'studentModuleEnrolment.moduleInstance'])
            ->get();

        return view('extensions.create', compact('student', 'assessments'));
    }

    public function store(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_assessment_id' => 'required|exists:student_assessments,id',
            'new_due_date' => 'required|date|after:today',
            'reason' => 'required|string|max:1000',
        ]);

        $assessment = StudentAssessment::findOrFail($validated['student_assessment_id']);

        // Verify assessment belongs to student
        if ($assessment->studentModuleEnrolment->student_id !== $student->id) {
            abort(403);
        }

        // Check if extension already exists
        $existingExtension = Extension::where('student_assessment_id', $assessment->id)
            ->where('status', 'pending')
            ->first();

        if ($existingExtension) {
            return back()->withErrors(['student_assessment_id' => 'An extension request already exists for this assessment.']);
        }

        DB::transaction(function () use ($validated, $student, $assessment) {
            Extension::create([
                'student_assessment_id' => $assessment->id,
                'student_id' => $student->id,
                'original_due_date' => $assessment->due_date,
                'new_due_date' => $validated['new_due_date'],
                'reason' => $validated['reason'],
                'requested_by' => auth()->id(),
                'status' => 'pending',
            ]);

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties(['assessment_id' => $assessment->id])
                ->log('Extension requested for ' . $assessment->assessmentComponent->name);
        });

        return redirect()->route('extensions.index')
            ->with('success', 'Extension request created successfully.');
    }

    public function approve(Extension $extension)
    {
        DB::transaction(function () use ($extension) {
            $extension->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update the assessment due date
            $extension->studentAssessment->update([
                'due_date' => $extension->new_due_date,
            ]);

            // Notify the teacher if different from approver
            if ($extension->studentAssessment->studentModuleEnrolment->moduleInstance->teacher_id !== auth()->id()) {
                // TODO: Send notification to teacher
            }

            activity()
                ->performedOn($extension->student)
                ->causedBy(auth()->user())
                ->withProperties(['extension_id' => $extension->id])
                ->log('Extension approved - new due date: ' . $extension->new_due_date->format('d M Y'));
        });

        return back()->with('success', 'Extension approved successfully.');
    }

    public function reject(Extension $extension, Request $request)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $extension->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Extension rejected.');
    }
}