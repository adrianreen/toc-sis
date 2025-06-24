<?php

namespace App\Http\Controllers;

use App\Models\Enrolment;
use App\Models\ExtensionRequest;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExtensionRequestController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Display student's extension requests
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            $extensionRequests = ExtensionRequest::where('student_id', $user->student_id)
                ->with(['enrolment.programme', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('extension-requests.index', compact('extensionRequests'));
        } else {
            // Staff view - all extension requests
            $extensionRequests = ExtensionRequest::with(['student', 'enrolment.programme', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('extension-requests.staff-index', compact('extensionRequests'));
        }
    }

    /**
     * Show the form for creating a new extension request
     */
    public function create()
    {
        if (! Auth::user()->isStudent()) {
            abort(403, 'Only students can create extension requests.');
        }

        $student = Student::find(Auth::user()->student_id);
        if (! $student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found.');
        }

        // Get student's active enrolments
        $enrolments = $student->enrolments()
            ->with('programme')
            ->where('status', 'active')
            ->get();

        if ($enrolments->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No active enrolments found.');
        }

        return view('extension-requests.create', compact('student', 'enrolments'));
    }

    /**
     * Store a newly created extension request
     */
    public function store(Request $request)
    {
        if (! Auth::user()->isStudent()) {
            abort(403, 'Only students can create extension requests.');
        }

        $student = Student::find(Auth::user()->student_id);
        if (! $student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found.');
        }

        $validated = $request->validate([
            'enrolment_id' => [
                'required',
                'exists:enrolments,id',
                Rule::exists('enrolments')->where(function ($query) use ($student) {
                    return $query->where('student_id', $student->id);
                }),
            ],
            'contact_number' => 'required|string|max:20',
            'extension_type' => 'required|in:two_weeks_free,eight_weeks_minor,twenty_four_weeks_major,medical',
            'course_name' => 'required|string|max:255',
            'assignments_submitted' => 'required|integer|min:0',
            'course_commencement_date' => 'required|date',
            'original_completion_date' => 'required|date|after:course_commencement_date',
            'additional_information' => 'required|string|max:2000',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'declaration_accepted' => 'required|accepted',
        ]);

        try {
            DB::beginTransaction();

            // Handle medical certificate upload
            $medicalCertPath = null;
            if ($request->hasFile('medical_certificate')) {
                $medicalCertPath = $request->file('medical_certificate')->store('medical-certificates', 'private');
            }

            // Validate medical certificate requirement
            if ($validated['extension_type'] === 'medical' && ! $medicalCertPath) {
                throw new \Exception('Medical certificate is required for medical extensions.');
            }

            $extensionRequest = new ExtensionRequest($validated);
            $extensionRequest->student_id = $student->id;
            $extensionRequest->student_number = $student->student_number;
            $extensionRequest->medical_certificate_path = $medicalCertPath;

            // Calculate extension fee
            $extensionRequest->extension_fee = $extensionRequest->calculateExtensionFee();

            // Calculate requested completion date (except for medical which is manual)
            if ($validated['extension_type'] !== 'medical') {
                $extensionRequest->requested_completion_date = $extensionRequest->calculateRequestedCompletionDate();
            }

            $extensionRequest->save();

            // Notify staff about new extension request
            $this->notifyStaffOfNewRequest($extensionRequest);

            DB::commit();

            return redirect()->route('extension-requests.show', $extensionRequest)
                ->with('success', 'Extension request submitted successfully. You will be notified once it has been reviewed.');

        } catch (\Exception $e) {
            DB::rollback();

            // Clean up uploaded file if something went wrong
            if ($medicalCertPath) {
                Storage::disk('private')->delete($medicalCertPath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit extension request: '.$e->getMessage());
        }
    }

    /**
     * Display the specified extension request
     */
    public function show(ExtensionRequest $extensionRequest)
    {
        // Authorization check
        if (Auth::user()->isStudent()) {
            if ($extensionRequest->student_id !== Auth::user()->student_id) {
                abort(403);
            }
        } else {
            // Staff can view all extension requests
            if (! in_array(Auth::user()->role, ['manager', 'student_services', 'teacher'])) {
                abort(403);
            }
        }

        $extensionRequest->load(['student', 'enrolment.programme', 'reviewer']);

        return view('extension-requests.show', compact('extensionRequest'));
    }

    /**
     * Show the form for editing the specified extension request (staff only)
     */
    public function edit(ExtensionRequest $extensionRequest)
    {
        if (! in_array(Auth::user()->role, ['manager', 'student_services', 'teacher'])) {
            abort(403, 'Only staff can review extension requests.');
        }

        if (! $extensionRequest->isPending()) {
            return redirect()->route('extension-requests.show', $extensionRequest)
                ->with('error', 'This extension request has already been reviewed.');
        }

        $extensionRequest->load(['student', 'enrolment.programme']);

        return view('extension-requests.review', compact('extensionRequest'));
    }

    /**
     * Update the specified extension request (staff review)
     */
    public function update(Request $request, ExtensionRequest $extensionRequest)
    {
        if (! in_array(Auth::user()->role, ['manager', 'student_services', 'teacher'])) {
            abort(403, 'Only staff can review extension requests.');
        }

        if (! $extensionRequest->isPending()) {
            return redirect()->route('extension-requests.show', $extensionRequest)
                ->with('error', 'This extension request has already been reviewed.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
            'requested_completion_date' => 'nullable|date|after:original_completion_date',
        ]);

        try {
            DB::beginTransaction();

            $extensionRequest->update([
                'status' => $validated['status'],
                'review_notes' => $validated['review_notes'],
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'requested_completion_date' => $validated['requested_completion_date'] ?? $extensionRequest->requested_completion_date,
            ]);

            // If approved, update the enrolment completion date
            if ($validated['status'] === 'approved' && $extensionRequest->requested_completion_date) {
                $extensionRequest->enrolment->update([
                    'completion_date' => $extensionRequest->requested_completion_date,
                ]);
            }

            // Send notification to student
            $this->notifyStudentOfDecision($extensionRequest);

            DB::commit();

            $statusText = $validated['status'] === 'approved' ? 'approved' : 'rejected';

            return redirect()->route('extension-requests.show', $extensionRequest)
                ->with('success', "Extension request has been {$statusText}.");

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update extension request: '.$e->getMessage());
        }
    }

    /**
     * Download medical certificate
     */
    public function downloadMedicalCertificate(ExtensionRequest $extensionRequest)
    {
        // Authorization check
        if (Auth::user()->isStudent()) {
            if ($extensionRequest->student_id !== Auth::user()->student_id) {
                abort(403);
            }
        } else {
            if (! in_array(Auth::user()->role, ['manager', 'student_services', 'teacher'])) {
                abort(403);
            }
        }

        if (! $extensionRequest->medical_certificate_path) {
            abort(404, 'Medical certificate not found.');
        }

        if (! Storage::disk('private')->exists($extensionRequest->medical_certificate_path)) {
            abort(404, 'Medical certificate file not found.');
        }

        return Storage::disk('private')->download(
            $extensionRequest->medical_certificate_path,
            'medical-certificate-'.$extensionRequest->id.'.pdf'
        );
    }

    /**
     * Notify staff of new extension request
     */
    private function notifyStaffOfNewRequest(ExtensionRequest $extensionRequest): void
    {
        $staffUsers = \App\Models\User::whereIn('role', ['manager', 'student_services', 'teacher'])->get();

        foreach ($staffUsers as $user) {
            $this->notificationService->notifyApprovalRequired(
                $user,
                'Extension Request',
                $extensionRequest->student->first_name.' '.$extensionRequest->student->last_name,
                route('extension-requests.show', $extensionRequest)
            );
        }
    }

    /**
     * Notify student of extension decision
     */
    private function notifyStudentOfDecision(ExtensionRequest $extensionRequest): void
    {
        $studentUser = \App\Models\User::where('student_id', $extensionRequest->student_id)->first();

        if ($studentUser) {
            if ($extensionRequest->isApproved()) {
                $this->notificationService->notifyCourseExtensionApproved(
                    $studentUser,
                    $extensionRequest->course_name,
                    $extensionRequest->requested_completion_date
                );
            } else {
                // For rejected requests, send a generic notification
                $this->notificationService->sendNotification(
                    $studentUser->id,
                    'Extension Request Update',
                    "Your extension request for {$extensionRequest->course_name} has been reviewed.",
                    'extension_update',
                    route('extension-requests.show', $extensionRequest)
                );
            }
        }
    }
}
