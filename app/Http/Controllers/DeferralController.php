<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrolment;
use App\Models\Deferral;
use App\Models\ProgrammeInstance;
use App\Services\EnrolmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeferralController extends Controller
{
    protected EnrolmentService $enrolmentService;

    public function __construct(EnrolmentService $enrolmentService)
    {
        $this->middleware(['auth', 'role:manager,student_services']);
        $this->enrolmentService = $enrolmentService;
    }

    public function index()
    {
        $deferrals = Deferral::with([
            'student',
            'enrolment.programmeInstance.programme',
            'enrolment.moduleInstance.module',
            'fromProgrammeInstance.programme',
            'toProgrammeInstance.programme',
            'approvedBy'
        ])
        ->latest()
        ->paginate(20);

        return view('deferrals.index', compact('deferrals'));
    }

    public function create(Student $student, Enrolment $enrolment)
    {
        // Check if enrolment belongs to student
        if ($enrolment->student_id !== $student->id) {
            abort(404, 'Enrolment does not belong to this student.');
        }

        // Only programme enrolments can be deferred
        if ($enrolment->enrolment_type !== 'programme') {
            abort(400, 'Only programme enrolments can be deferred.');
        }

        // Check if there's already a pending deferral
        $existingDeferral = Deferral::where('enrolment_id', $enrolment->id)
            ->where('status', 'pending')
            ->first();

        if ($existingDeferral) {
            return back()->withErrors(['error' => 'A deferral request already exists for this enrolment.']);
        }

        // Get future programme instances for the same programme
        $currentProgrammeInstance = $enrolment->programmeInstance;
        $futureProgrammeInstances = ProgrammeInstance::where('programme_id', $currentProgrammeInstance->programme_id)
            ->where('id', '!=', $currentProgrammeInstance->id)
            ->where('intake_start_date', '>', now())
            ->orderBy('intake_start_date')
            ->get();

        return view('deferrals.create', compact('student', 'enrolment', 'futureProgrammeInstances'));
    }

    public function store(Request $request, Student $student, Enrolment $enrolment)
    {
        $validated = $request->validate([
            'to_programme_instance_id' => 'required|exists:programme_instances,id',
            'reason' => 'required|string|max:1000',
            'expected_return_date' => 'nullable|date|after:today',
        ]);

        // Verify the target programme instance is for the same programme
        $targetProgrammeInstance = ProgrammeInstance::findOrFail($validated['to_programme_instance_id']);
        if ($targetProgrammeInstance->programme_id !== $enrolment->programmeInstance->programme_id) {
            return back()->withErrors(['to_programme_instance_id' => 'Target programme instance must be for the same programme.']);
        }

        DB::transaction(function () use ($validated, $student, $enrolment) {
            // Create deferral record
            $deferral = Deferral::create([
                'student_id' => $student->id,
                'enrolment_id' => $enrolment->id,
                'from_programme_instance_id' => $enrolment->programme_instance_id,
                'to_programme_instance_id' => $validated['to_programme_instance_id'],
                'deferral_date' => now(),
                'expected_return_date' => $validated['expected_return_date'],
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            // Update enrolment status to deferred
            $enrolment->update(['status' => 'deferred']);

            // Update student status to deferred if they have no other active enrolments
            $activeEnrolments = Enrolment::where('student_id', $student->id)
                ->where('status', 'active')
                ->count();

            if ($activeEnrolments === 0) {
                $student->update(['status' => 'deferred']);
            }

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'deferral_id' => $deferral->id,
                    'from_programme_instance' => $enrolment->programmeInstance->programme->title,
                    'to_programme_instance' => $targetProgrammeInstance->programme->title,
                    'intake_start' => $targetProgrammeInstance->intake_start_date->format('Y-m-d')
                ])
                ->log("Deferral requested from {$enrolment->programmeInstance->programme->title} to {$targetProgrammeInstance->programme->title}");
        });

        return redirect()->route('deferrals.index')
            ->with('success', 'Deferral request created successfully.');
    }

    public function approve(Deferral $deferral)
    {
        if ($deferral->status !== 'pending') {
            return back()->withErrors(['error' => 'Deferral is not pending approval.']);
        }

        DB::transaction(function () use ($deferral) {
            // Update deferral status
            $deferral->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Process the deferral return using EnrolmentService
            $this->enrolmentService->processDeferralReturn(
                $deferral->student,
                $deferral->enrolment,
                $deferral->toProgrammeInstance
            );

            activity()
                ->performedOn($deferral->student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'deferral_id' => $deferral->id,
                    'new_programme_instance' => $deferral->toProgrammeInstance->programme->title,
                    'intake_start' => $deferral->toProgrammeInstance->intake_start_date->format('Y-m-d')
                ])
                ->log("Deferral approved - moved to {$deferral->toProgrammeInstance->programme->title}");
        });

        return back()->with('success', 'Deferral approved and student moved to new programme instance.');
    }

    public function reject(Deferral $deferral, Request $request)
    {
        if ($deferral->status !== 'pending') {
            return back()->withErrors(['error' => 'Deferral is not pending approval.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($deferral, $validated) {
            // Update deferral status
            $deferral->update([
                'status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Restore enrolment status to active
            $deferral->enrolment->update(['status' => 'active']);

            // Restore student status to active if appropriate
            $deferral->student->update(['status' => 'active']);

            activity()
                ->performedOn($deferral->student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'deferral_id' => $deferral->id,
                    'rejection_reason' => $validated['admin_notes']
                ])
                ->log("Deferral rejected - {$validated['admin_notes']}");
        });

        return back()->with('success', 'Deferral rejected and enrolment restored.');
    }

    public function show(Deferral $deferral)
    {
        $deferral->load([
            'student',
            'enrolment.programmeInstance.programme',
            'fromProgrammeInstance.programme',
            'toProgrammeInstance.programme',
            'approvedBy'
        ]);

        return view('deferrals.show', compact('deferral'));
    }

    public function destroy(Deferral $deferral)
    {
        if ($deferral->status === 'approved') {
            return back()->withErrors(['error' => 'Cannot delete an approved deferral.']);
        }

        DB::transaction(function () use ($deferral) {
            // If deferral was pending, restore enrolment status
            if ($deferral->status === 'pending') {
                $deferral->enrolment->update(['status' => 'active']);
                
                // Check if student should be made active again
                $activeEnrolments = Enrolment::where('student_id', $deferral->student_id)
                    ->where('status', 'active')
                    ->count();
                    
                if ($activeEnrolments > 0) {
                    $deferral->student->update(['status' => 'active']);
                }
            }

            activity()
                ->performedOn($deferral->student)
                ->causedBy(auth()->user())
                ->withProperties(['deferral_id' => $deferral->id])
                ->log("Deferral request deleted");

            $deferral->delete();
        });

        return back()->with('success', 'Deferral request deleted.');
    }
}