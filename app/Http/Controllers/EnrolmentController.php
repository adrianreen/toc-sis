<?php

namespace App\Http\Controllers;

use App\Models\Enrolment;
use App\Models\ModuleInstance;
use App\Models\ProgrammeInstance;
use App\Models\Student;
use App\Services\EnrolmentService;
use Illuminate\Http\Request;

class EnrolmentController extends Controller
{
    protected $enrolmentService;

    public function __construct(EnrolmentService $enrolmentService)
    {
        $this->enrolmentService = $enrolmentService;
    }

    /**
     * Show all enrolments
     */
    public function index()
    {
        $enrolments = Enrolment::with(['student', 'programmeInstance.programme', 'moduleInstance.module'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('enrolments.index', compact('enrolments'));
    }

    /**
     * Step 1: Admin selects a student and clicks "Enrol"
     * Show the two-path choice: "Enrol in a Programme" or "Enrol in a Standalone Module"
     */
    public function create(Student $student)
    {
        return view('enrolments.create', compact('student'));
    }

    /**
     * Step 2a: Admin chooses "Enrol in a Programme"
     * Display list of available Programme Instances of type Sync
     */
    public function createProgramme(Student $student)
    {
        // Get available programme instances
        $availableProgrammes = $this->enrolmentService->getAvailableProgrammeInstances();

        // Check for existing programme enrolments
        $existingProgrammeEnrolments = $student->programmeEnrolments()
            ->whereIn('status', ['active', 'deferred'])
            ->with('programmeInstance.programme')
            ->get();

        return view('enrolments.create-programme', compact('student', 'availableProgrammes', 'existingProgrammeEnrolments'));
    }

    /**
     * Step 2b: Admin chooses "Enrol in a Standalone Module"
     * Display list of available Module Instances where parent Module allows standalone enrolment
     */
    public function createModule(Student $student)
    {
        // Get available standalone module instances
        $availableModules = $this->enrolmentService->getAvailableModuleInstances();

        // Check for existing module enrolments
        $existingModuleEnrolments = $student->moduleEnrolments()
            ->whereIn('status', ['active', 'deferred'])
            ->with('moduleInstance.module')
            ->get();

        return view('enrolments.create-module', compact('student', 'availableModules', 'existingModuleEnrolments'));
    }

    /**
     * Step 3a: Process Programme Enrolment
     * Admin selects the programme instance, create enrolment record linking student to Programme Instance
     */
    public function storeProgramme(Request $request, Student $student)
    {
        $validated = $request->validate([
            'programme_instance_id' => 'required|exists:programme_instances,id',
            'enrolment_date' => 'required|date',
        ]);

        try {
            $programmeInstance = ProgrammeInstance::findOrFail($validated['programme_instance_id']);

            $enrolment = $this->enrolmentService->enrolStudentInProgramme(
                $student,
                $programmeInstance,
                $validated
            );

            return redirect()->route('students.show', $student)
                ->with('success', "Student successfully enrolled in {$programmeInstance->programme->title} ({$programmeInstance->label}). The system automatically enrolled the student in all module instances for this programme.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Step 3b: Process Standalone Module Enrolment
     * Admin selects specific module instance, create enrolment record linking student directly to Module Instance
     */
    public function storeModule(Request $request, Student $student)
    {
        $validated = $request->validate([
            'module_instance_id' => 'required|exists:module_instances,id',
            'enrolment_date' => 'required|date',
        ]);

        try {
            $moduleInstance = ModuleInstance::findOrFail($validated['module_instance_id']);

            $enrolment = $this->enrolmentService->enrolStudentInModule(
                $student,
                $moduleInstance,
                $validated
            );

            return redirect()->route('students.show', $student)
                ->with('success', "Student successfully enrolled in standalone module: {$moduleInstance->module->title} ({$moduleInstance->module->module_code}).");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show individual enrolment details
     */
    public function show(Enrolment $enrolment)
    {
        $enrolment->load([
            'student',
            'programmeInstance.programme',
            'programmeInstance.moduleInstances.module',
            'moduleInstance.module',
        ]);

        return view('enrolments.show', compact('enrolment'));
    }

    /**
     * Update enrolment status (e.g., defer, withdraw, complete)
     */
    public function update(Request $request, Enrolment $enrolment)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,completed,withdrawn,deferred',
            'notes' => 'nullable|string',
        ]);

        $enrolment->update($validated);

        return redirect()->route('enrolments.show', $enrolment)
            ->with('success', 'Enrolment status updated successfully.');
    }

    /**
     * Withdraw student from enrolment
     */
    public function withdraw(Enrolment $enrolment)
    {
        try {
            $this->enrolmentService->withdrawStudent($enrolment);

            return redirect()->route('students.show', $enrolment->student)
                ->with('success', 'Student withdrawn from enrolment successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show deferral form for programme enrolments
     */
    public function deferralForm(Enrolment $enrolment)
    {
        if ($enrolment->enrolment_type !== 'programme') {
            return redirect()->route('enrolments.show', $enrolment)
                ->with('error', 'Deferrals only apply to programme enrolments.');
        }

        // Get available programme instances for the same programme
        $availableProgrammeInstances = ProgrammeInstance::where('programme_id', $enrolment->programmeInstance->programme_id)
            ->where('id', '!=', $enrolment->programme_instance_id)
            ->where('intake_start_date', '>', now())
            ->orderBy('intake_start_date')
            ->get();

        return view('enrolments.deferral', compact('enrolment', 'availableProgrammeInstances'));
    }

    /**
     * Process deferral to new programme instance
     */
    public function processDeferral(Request $request, Enrolment $enrolment)
    {
        $validated = $request->validate([
            'new_programme_instance_id' => 'required|exists:programme_instances,id',
            'reason' => 'required|string',
        ]);

        try {
            $newProgrammeInstance = ProgrammeInstance::findOrFail($validated['new_programme_instance_id']);

            $this->enrolmentService->processDeferralReturn(
                $enrolment->student,
                $enrolment,
                $newProgrammeInstance
            );

            return redirect()->route('students.show', $enrolment->student)
                ->with('success', "Student deferral processed successfully. Moved to {$newProgrammeInstance->label}.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Student dashboard: Show their enrolments
     */
    public function myEnrolments()
    {
        $user = auth()->user();
        $student = $user->student;

        if (! $student) {
            abort(404, 'Student record not found.');
        }

        $programmeEnrolments = $student->getCurrentProgrammeEnrolments()->get();
        $moduleEnrolments = $student->getCurrentModuleEnrolments()->get();

        return view('enrolments.my-enrolments', compact('student', 'programmeEnrolments', 'moduleEnrolments'));
    }

    /**
     * Unenroll student from programme/module (admin correction)
     */
    public function unenroll(Request $request, Enrolment $enrolment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5',
            'confirm_unenroll' => 'required|accepted',
        ]);

        try {
            // Store details for success message before deletion
            $studentName = $enrolment->student->full_name;
            $enrolmentDetails = $enrolment->isProgrammeEnrolment()
                ? $enrolment->programmeInstance->programme->title.' ('.$enrolment->programmeInstance->label.')'
                : $enrolment->moduleInstance->module->title.' ('.$enrolment->moduleInstance->module->module_code.')';

            // Log the unenrollment action
            activity()
                ->performedOn($enrolment)
                ->causedBy(auth()->user())
                ->withProperties([
                    'reason' => $validated['reason'],
                    'student_id' => $enrolment->student_id,
                    'enrolment_type' => $enrolment->enrolment_type,
                    'programme_instance_id' => $enrolment->programme_instance_id,
                    'module_instance_id' => $enrolment->module_instance_id,
                ])
                ->log('Student unenrolled by admin');

            // Delete the enrolment
            $enrolment->delete();

            return redirect()->route('students.show', $enrolment->student)
                ->with('success', "Student {$studentName} has been successfully unenrolled from {$enrolmentDetails}. Reason: {$validated['reason']}");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to unenroll student: '.$e->getMessage()]);
        }
    }

    /**
     * Show unenroll confirmation form
     */
    public function showUnenrollForm(Enrolment $enrolment)
    {
        $enrolment->load([
            'student',
            'programmeInstance.programme',
            'moduleInstance.module',
        ]);

        return view('enrolments.unenroll', compact('enrolment'));
    }
}
