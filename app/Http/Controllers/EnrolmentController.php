<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Programme;
use App\Models\Enrolment;
use App\Services\EnrolmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrolmentController extends Controller
{
    protected $enrolmentService;

    public function __construct(EnrolmentService $enrolmentService)
    {
        $this->enrolmentService = $enrolmentService;
    }

    /**
     * Show the form for creating a new enrolment.
     */
    public function create(Student $student)
    {
        // Get programmes with their cohorts
        $programmes = Programme::with(['cohorts' => function ($query) {
            $query->where('status', '!=', 'completed')
                  ->orderBy('start_date', 'desc');
        }])->where('is_active', true)->get();

        // Get existing enrolments to show in the form
        $existingEnrolments = $student->enrolments()->with('programme')->get();

        return view('enrolments.create', compact('student', 'programmes', 'existingEnrolments'));
    }

    /**
     * Store a newly created enrolment in storage.
     */
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'cohort_id' => 'nullable|exists:cohorts,id',
            'enrolment_date' => 'required|date',
        ]);

        // Check for duplicate enrolment
        $existingEnrolment = Enrolment::where([
            'student_id' => $student->id,
            'programme_id' => $request->programme_id,
        ])->whereIn('status', ['active', 'deferred'])->first();

        if ($existingEnrolment) {
            return redirect()->back()
                ->withErrors(['programme_id' => 'Student is already enrolled in this programme.']);
        }

        try {
            // Use the service to handle the complex enrolment process
            $enrolment = $this->enrolmentService->enrolStudent($student, [
                'programme_id' => $request->programme_id,
                'cohort_id' => $request->cohort_id,
                'enrolment_date' => $request->enrolment_date,
            ]);

            // Log the activity
            activity()
                ->performedOn($enrolment)
                ->causedBy(Auth::user())
                ->withProperties([
                    'student_name' => $student->full_name,
                    'programme_code' => $enrolment->programme->code,
                    'cohort_code' => $enrolment->cohort?->code,
                ])
                ->log('enrolled student in programme');

            return redirect()
                ->route('students.show', $student)
                ->with('success', 'Student successfully enrolled in programme and all module instances.');

        } catch (\Exception $e) {
            \Log::error('Enrolment failed', [
                'student_id' => $student->id,
                'programme_id' => $request->programme_id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['enrolment' => 'Failed to enrol student: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the status of an enrolment.
     */
    public function updateStatus(Request $request, Student $student, Enrolment $enrolment)
    {
        $request->validate([
            'status' => 'required|in:active,deferred,completed,withdrawn,cancelled',
        ]);

        $oldStatus = $enrolment->status;
        $enrolment->update([
            'status' => $request->status,
        ]);

        // Update student status if appropriate
        if ($request->status === 'completed') {
            // Check if all enrolments are completed
            $activeEnrolments = $student->enrolments()
                ->whereIn('status', ['active', 'deferred'])
                ->count();
            
            if ($activeEnrolments === 0) {
                $student->update(['status' => 'completed']);
            }
        }

        // Log the activity
        activity()
            ->performedOn($enrolment)
            ->causedBy(Auth::user())
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'student_name' => $student->full_name,
                'programme_code' => $enrolment->programme->code,
            ])
            ->log('updated enrolment status');

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Enrolment status updated successfully.');
    }
}