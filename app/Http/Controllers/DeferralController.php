<?php
// app/Http/Controllers/DeferralController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrolment;
use App\Models\Deferral;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeferralController extends Controller
{
    public function index()
    {
        $deferrals = Deferral::with(['student', 'enrolment.programme', 'fromCohort', 'toCohort'])
            ->latest()
            ->paginate(20);

        return view('deferrals.index', compact('deferrals'));
    }

    public function create(Student $student, Enrolment $enrolment)
    {
        // Check if enrolment belongs to student
        if ($enrolment->student_id !== $student->id) {
            abort(404);
        }

        // Get future cohorts for the programme
        $futureCohorts = Cohort::where('programme_id', $enrolment->programme_id)
            ->where('status', '!=', 'completed')
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->get();

        return view('deferrals.create', compact('student', 'enrolment', 'futureCohorts'));
    }

    public function store(Request $request, Student $student, Enrolment $enrolment)
    {
        $validated = $request->validate([
            'to_cohort_id' => 'required|exists:cohorts,id',
            'reason' => 'required|string|max:1000',
            'expected_return_date' => 'nullable|date|after:today',
        ]);

        DB::transaction(function () use ($validated, $student, $enrolment) {
            // Create deferral record
            $deferral = Deferral::create([
                'student_id' => $student->id,
                'enrolment_id' => $enrolment->id,
                'from_cohort_id' => $enrolment->cohort_id,
                'to_cohort_id' => $validated['to_cohort_id'],
                'deferral_date' => now(),
                'expected_return_date' => $validated['expected_return_date'],
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            // Update enrolment status
            $enrolment->update(['status' => 'deferred']);

            // Update student status
            $student->update(['status' => 'deferred']);

            // Log activity
            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties(['deferral_id' => $deferral->id])
                ->log('Student deferred from cohort ' . $enrolment->cohort->code);
        });

        return redirect()->route('students.show', $student)
            ->with('success', 'Deferral request created successfully.');
    }

    public function approve(Deferral $deferral)
    {
        DB::transaction(function () use ($deferral) {
            // Update deferral
            $deferral->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update enrolment to new cohort
            $deferral->enrolment->update([
                'cohort_id' => $deferral->to_cohort_id,
            ]);

            // Log activity
            activity()
                ->performedOn($deferral->student)
                ->causedBy(auth()->user())
                ->withProperties(['deferral_id' => $deferral->id])
                ->log('Deferral approved - moved to cohort ' . $deferral->toCohort->code);
        });

        return back()->with('success', 'Deferral approved successfully.');
    }

    public function processReturn(Deferral $deferral)
    {
        DB::transaction(function () use ($deferral) {
            // Update deferral
            $deferral->update([
                'status' => 'returned',
                'actual_return_date' => now(),
            ]);

            // Update enrolment status
            $deferral->enrolment->update(['status' => 'active']);

            // Update student status
            $deferral->student->update(['status' => 'active']);

            // Log activity
            activity()
                ->performedOn($deferral->student)
                ->causedBy(auth()->user())
                ->withProperties(['deferral_id' => $deferral->id])
                ->log('Student returned from deferral to cohort ' . $deferral->toCohort->code);
        });

        return back()->with('success', 'Student return processed successfully.');
    }
}