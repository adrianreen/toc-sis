<?php
// app/Http/Controllers/EnrolmentController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Programme;
use App\Models\Cohort;
use App\Models\Enrolment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EnrolmentController extends Controller
{
    public function create(Student $student)
    {
        $programmes = Programme::with(['cohorts' => function ($query) {
            $query->where('status', '!=', 'completed')
                  ->orderBy('start_date', 'desc');
        }])->where('is_active', true)->get();

        $existingEnrolments = $student->enrolments()
            ->with(['programme', 'cohort'])
            ->get();

        return view('enrolments.create', compact('student', 'programmes', 'existingEnrolments'));
    }

    public function store(Request $request, Student $student)
    {
        $validated = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'cohort_id' => 'nullable|exists:cohorts,id',
            'enrolment_date' => 'required|date',
        ]);

        // Check if already enrolled in this programme
        $existing = $student->enrolments()
            ->where('programme_id', $validated['programme_id'])
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existing) {
            return back()->withErrors(['programme_id' => 'Student is already enrolled in this programme.']);
        }

        // Get programme to check enrolment type
        $programme = Programme::find($validated['programme_id']);

        // Validate cohort requirement
        if ($programme->isCohortBased() && empty($validated['cohort_id'])) {
            return back()->withErrors(['cohort_id' => 'Cohort is required for this programme.']);
        }

        // Calculate expected completion date
        $enrolmentDate = Carbon::parse($validated['enrolment_date']);
        $expectedCompletion = null;

        if ($programme->isCohortBased() && $validated['cohort_id']) {
            $cohort = Cohort::find($validated['cohort_id']);
            $expectedCompletion = $cohort->end_date;
        }

        // Create enrolment
        $enrolment = Enrolment::create([
            'student_id' => $student->id,
            'programme_id' => $validated['programme_id'],
            'cohort_id' => $validated['cohort_id'] ?? null,
            'enrolment_date' => $validated['enrolment_date'],
            'expected_completion_date' => $expectedCompletion,
            'status' => 'active',
        ]);

        // Update student status
        $student->update(['status' => 'active']);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student enrolled successfully.');
    }

    public function updateStatus(Request $request, Student $student, Enrolment $enrolment)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,deferred,completed,withdrawn,cancelled',
            'notes' => 'nullable|string',
        ]);

        $enrolment->update($validated);

        // Update student overall status if needed
        $this->updateStudentStatus($student);

        return back()->with('success', 'Enrolment status updated successfully.');
    }

    private function updateStudentStatus(Student $student)
    {
        $activeEnrolments = $student->enrolments()
            ->where('status', 'active')
            ->count();

        if ($activeEnrolments > 0) {
            $student->update(['status' => 'active']);
        } else {
            $allCompleted = $student->enrolments()
                ->whereIn('status', ['completed', 'cancelled'])
                ->count() === $student->enrolments()->count();

            if ($allCompleted) {
                $student->update(['status' => 'completed']);
            } else {
                $student->update(['status' => 'deferred']);
            }
        }
    }
}