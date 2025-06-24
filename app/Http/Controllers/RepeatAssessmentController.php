<?php

// app/Http/Controllers/RepeatAssessmentController.php

namespace App\Http\Controllers;

use App\Models\RepeatAssessment;
use App\Models\Student;
use App\Models\StudentGradeRecord;
use App\Models\User;
use App\Services\MoodleService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RepeatAssessmentController extends Controller
{
    protected NotificationService $notificationService;

    protected MoodleService $moodleService;

    public function __construct(NotificationService $notificationService, MoodleService $moodleService)
    {
        $this->notificationService = $notificationService;
        $this->moodleService = $moodleService;

        // Apply role middleware
        $this->middleware(['auth', 'role:manager,student_services,teacher']);
    }

    /**
     * Display paginated list of repeat assessments with advanced filtering and statistics
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = RepeatAssessment::with([
            'student',
            'studentGradeRecord.moduleInstance.module',
            'approvedBy',
            'assignedTo',
        ]);

        // Apply filters
        if ($request->filled('workflow_stage')) {
            $query->byWorkflowStage($request->workflow_stage);
        }

        if ($request->filled('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        if ($request->filled('priority_level')) {
            $query->byPriority($request->priority_level);
        }

        if ($request->filled('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        if ($request->filled('overdue')) {
            $query->overdue();
        }

        if ($request->filled('due_soon')) {
            $query->dueSoon($request->due_soon ?: 7);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $repeats = $query->paginate(20)->appends($request->query());

        // Get filter options
        $workflowStages = [
            'identified' => 'Identified',
            'notified' => 'Student Notified',
            'payment_pending' => 'Payment Pending',
            'moodle_setup' => 'Moodle Setup',
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $paymentStatuses = [
            'pending' => 'Payment Pending',
            'paid' => 'Paid',
            'waived' => 'Waived',
            'overdue' => 'Overdue',
        ];

        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];

        $staff = User::whereIn('role', ['manager', 'student_services', 'teacher'])
            ->orderBy('name')
            ->get();

        // Get summary statistics
        $stats = [
            'total' => RepeatAssessment::count(),
            'pending_payment' => RepeatAssessment::pendingPayment()->count(),
            'overdue' => RepeatAssessment::overdue()->count(),
            'due_soon' => RepeatAssessment::dueSoon()->count(),
            'active' => RepeatAssessment::byWorkflowStage('active')->count(),
        ];

        return view('repeat-assessments.index', compact(
            'repeats', 'workflowStages', 'paymentStatuses', 'priorities', 'staff', 'stats'
        ));
    }

    public function show(RepeatAssessment $repeatAssessment)
    {
        $repeatAssessment->load([
            'student',
            'studentGradeRecord.moduleInstance.module',
            'approvedBy',
            'assignedTo',
        ]);

        return view('repeat-assessments.show', compact('repeatAssessment'));
    }

    public function create(?Student $student = null)
    {
        // If no student specified, show student selection
        if (! $student) {
            // Get students with failed grade records that don't already have repeat assessments
            $studentsWithFailures = Student::whereHas('studentGradeRecords', function ($query) {
                $query->where('percentage', '<', 40)
                    ->whereNotNull('percentage');
            })->with(['studentGradeRecords' => function ($query) {
                $query->where('percentage', '<', 40)
                    ->whereNotNull('percentage')
                    ->with('moduleInstance.module');
            }])->get();

            return view('repeat-assessments.select-student', compact('studentsWithFailures'));
        }

        // Get failed grade records for the specific student
        $failedGradeRecords = StudentGradeRecord::where('student_id', $student->id)
            ->where('percentage', '<', 40)
            ->whereNotNull('percentage')
            ->whereDoesntHave('repeatAssessments') // Don't show assessments that already have repeat assessments
            ->with(['moduleInstance.module'])
            ->get();

        $staff = User::whereIn('role', ['manager', 'student_services', 'teacher'])
            ->orderBy('name')
            ->get();

        return view('repeat-assessments.create', compact('student', 'failedGradeRecords', 'staff'));
    }

    public function store(Request $request, ?Student $student = null)
    {
        $validated = $request->validate([
            'student_id' => $student ? 'nullable' : 'required|exists:students,id',
            'student_grade_record_id' => 'required|exists:student_grade_records,id',
            'reason' => 'required|string|max:1000',
            'repeat_due_date' => 'required|date|after:today',
            'cap_grade' => 'nullable|numeric|min:0|max:100',
            'payment_amount' => 'nullable|numeric|min:0',
            'priority_level' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => 'nullable|exists:users,id',
            'deadline_date' => 'required|date|after:today',
            'staff_notes' => 'nullable|string|max:2000',
            'moodle_setup_required' => 'boolean',
        ]);

        // If student not provided in URL, get from form
        if (! $student) {
            $student = Student::findOrFail($validated['student_id']);
        }

        $gradeRecord = StudentGradeRecord::findOrFail($validated['student_grade_record_id']);

        // Check if repeat assessment already exists for this grade record
        $existingRepeat = RepeatAssessment::where('student_grade_record_id', $gradeRecord->id)->first();
        if ($existingRepeat) {
            return back()->withErrors(['student_grade_record_id' => 'A repeat assessment already exists for this grade record.']);
        }

        DB::transaction(function () use ($validated, $student, $gradeRecord) {
            // Create repeat assessment
            $repeat = RepeatAssessment::create([
                'student_grade_record_id' => $gradeRecord->id,
                'student_id' => $student->id,
                'module_instance_id' => $gradeRecord->module_instance_id,
                'reason' => $validated['reason'],
                'repeat_due_date' => $validated['repeat_due_date'],
                'cap_grade' => $validated['cap_grade'] ?? 40, // Default cap at 40%
                'status' => 'pending',
                'payment_amount' => $validated['payment_amount'] ?? 50.00, // Default amount
                'priority_level' => $validated['priority_level'],
                'assigned_to' => $validated['assigned_to'] ?? auth()->id(),
                'deadline_date' => $validated['deadline_date'],
                'staff_notes' => $validated['staff_notes'],
                'workflow_stage' => 'identified',
                'moodle_setup_status' => ($validated['moodle_setup_required'] ?? true) ? 'pending' : 'not_required',
            ]);

            // Create new grade record attempt
            StudentGradeRecord::create([
                'student_id' => $student->id,
                'module_instance_id' => $gradeRecord->module_instance_id,
                'assessment_component_name' => $gradeRecord->assessment_component_name,
                'attempts' => $gradeRecord->attempts + 1,
                'submission_date' => null,
                'grade' => null,
                'percentage' => null,
                'is_visible_to_student' => false, // Keep hidden until graded
                'release_date' => $validated['repeat_due_date'],
            ]);

            activity()
                ->performedOn($repeat)
                ->causedBy(auth()->user())
                ->withProperties([
                    'student_id' => $student->id,
                    'assessment_component' => $assessment->assessmentComponent->name,
                    'module' => $assessment->assessmentComponent->module->name,
                ])
                ->log('Repeat assessment created');
        });

        return redirect()->route('repeat-assessments.index')
            ->with('success', 'Repeat assessment created successfully.');
    }

    public function edit(RepeatAssessment $repeatAssessment)
    {
        $repeatAssessment->load([
            'student',
            'studentGradeRecord',
            'moduleInstance.module',
        ]);

        $staff = User::whereIn('role', ['manager', 'student_services', 'teacher'])
            ->orderBy('name')
            ->get();

        return view('repeat-assessments.edit', compact('repeatAssessment', 'staff'));
    }

    public function update(Request $request, RepeatAssessment $repeatAssessment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'repeat_due_date' => 'required|date',
            'cap_grade' => 'nullable|numeric|min:0|max:100',
            'payment_amount' => 'nullable|numeric|min:0',
            'priority_level' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => 'nullable|exists:users,id',
            'deadline_date' => 'required|date',
            'staff_notes' => 'nullable|string|max:2000',
        ]);

        $repeatAssessment->update($validated);

        activity()
            ->performedOn($repeatAssessment)
            ->causedBy(auth()->user())
            ->log('Repeat assessment updated');

        return redirect()->route('repeat-assessments.show', $repeatAssessment)
            ->with('success', 'Repeat assessment updated successfully.');
    }

    // Payment management
    public function recordPayment(Request $request, RepeatAssessment $repeatAssessment)
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['online', 'bank_transfer', 'cheque', 'cash'])],
            'payment_amount' => 'required|numeric|min:0',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        $repeatAssessment->markAsPaid(
            $validated['payment_method'],
            $validated['payment_amount'],
            $validated['payment_notes']
        );

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function waivePayment(Request $request, RepeatAssessment $repeatAssessment)
    {
        $validated = $request->validate([
            'waiver_reason' => 'required|string|max:500',
        ]);

        $repeatAssessment->waivePayment($validated['waiver_reason']);

        return back()->with('success', 'Payment waived successfully.');
    }

    // Notification management
    public function sendNotification(Request $request, RepeatAssessment $repeatAssessment)
    {
        $validated = $request->validate([
            'notification_method' => ['required', Rule::in(['email', 'post', 'phone', 'in_person'])],
            'notification_notes' => 'nullable|string|max:500',
        ]);

        // Send notification via NotificationService
        if ($validated['notification_method'] === 'email') {
            // Use existing notification service to send email
            $this->notificationService->notifyRepeatAssessmentRequired(
                $repeatAssessment->student->user,
                $repeatAssessment
            );
        }

        $repeatAssessment->markNotificationSent(
            $validated['notification_method'],
            $validated['notification_notes']
        );

        return back()->with('success', 'Student notification sent successfully.');
    }

    // Moodle integration
    public function setupMoodle(Request $request, RepeatAssessment $repeatAssessment)
    {
        $validated = $request->validate([
            'moodle_course_id' => 'nullable|string|max:50',
            'moodle_notes' => 'nullable|string|max:500',
        ]);

        try {
            // Use MoodleService to set up course
            $courseId = $this->moodleService->setupRepeatAssessmentCourse($repeatAssessment);

            $repeatAssessment->markMoodleSetupComplete(
                $courseId ?? $validated['moodle_course_id'],
                $validated['moodle_notes']
            );

            return back()->with('success', 'Moodle course setup completed successfully.');
        } catch (\Exception $e) {
            $repeatAssessment->markMoodleSetupFailed($e->getMessage());

            return back()->with('error', 'Moodle setup failed: '.$e->getMessage());
        }
    }

    // Workflow management
    public function approve(RepeatAssessment $repeatAssessment)
    {
        $repeatAssessment->approve();

        return back()->with('success', 'Repeat assessment approved.');
    }

    public function reject(Request $request, RepeatAssessment $repeatAssessment)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $repeatAssessment->reject($validated['rejection_reason']);

        return back()->with('success', 'Repeat assessment rejected.');
    }

    public function complete(RepeatAssessment $repeatAssessment)
    {
        $repeatAssessment->update([
            'workflow_stage' => 'completed',
            'status' => 'completed',
        ]);

        activity()
            ->performedOn($repeatAssessment)
            ->causedBy(auth()->user())
            ->log('Repeat assessment marked as completed');

        return back()->with('success', 'Repeat assessment marked as completed.');
    }

    // Bulk operations
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['assign', 'update_priority', 'send_reminders'])],
            'repeat_assessment_ids' => 'required|array',
            'repeat_assessment_ids.*' => 'exists:repeat_assessments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority_level' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $repeatAssessments = RepeatAssessment::whereIn('id', $validated['repeat_assessment_ids'])->get();

        switch ($validated['action']) {
            case 'assign':
                $repeatAssessments->each(function ($repeat) use ($validated) {
                    $repeat->update(['assigned_to' => $validated['assigned_to']]);
                });
                $message = 'Repeat assessments assigned successfully.';
                break;

            case 'update_priority':
                $repeatAssessments->each(function ($repeat) use ($validated) {
                    $repeat->update(['priority_level' => $validated['priority_level']]);
                });
                $message = 'Priority levels updated successfully.';
                break;

            case 'send_reminders':
                $repeatAssessments->each(function ($repeat) {
                    if (! $repeat->notification_sent) {
                        // Send reminder notification
                        $this->notificationService->notifyRepeatAssessmentRequired(
                            $repeat->student->user,
                            $repeat
                        );
                        $repeat->markNotificationSent('email', 'Bulk reminder sent');
                    }
                });
                $message = 'Reminder notifications sent successfully.';
                break;
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => $validated['action'],
                'count' => $repeatAssessments->count(),
            ])
            ->log('Bulk action performed on repeat assessments');

        return back()->with('success', $message);
    }

    // Auto-population of failed students
    public function autoPopulate(Request $request)
    {
        $validated = $request->validate([
            'module_instance_id' => 'nullable|exists:module_instances,id',
            'deadline_days' => 'required|integer|min:1|max:365',
            'payment_amount' => 'required|numeric|min:0',
            'dry_run' => 'boolean',
        ]);

        $query = StudentAssessment::where('status', 'failed')
            ->whereDoesntHave('repeatAssessments')
            ->with(['studentModuleEnrolment.student', 'assessmentComponent']);

        if ($validated['module_instance_id']) {
            $query->whereHas('studentModuleEnrolment', function ($q) use ($validated) {
                $q->where('module_instance_id', $validated['module_instance_id']);
            });
        }

        $failedAssessments = $query->get();

        if ($validated['dry_run'] ?? false) {
            return response()->json([
                'count' => $failedAssessments->count(),
                'assessments' => $failedAssessments->map(function ($assessment) {
                    return [
                        'student_name' => $assessment->studentModuleEnrolment->student->full_name,
                        'assessment_name' => $assessment->assessmentComponent->name,
                        'module_name' => $assessment->assessmentComponent->module->name,
                    ];
                }),
            ]);
        }

        $created = 0;
        $deadline = now()->addDays($validated['deadline_days']);

        DB::transaction(function () use ($failedAssessments, $validated, $deadline, &$created) {
            foreach ($failedAssessments as $assessment) {
                RepeatAssessment::create([
                    'student_assessment_id' => $assessment->id,
                    'student_id' => $assessment->studentModuleEnrolment->student_id,
                    'module_instance_id' => $assessment->studentModuleEnrolment->module_instance_id,
                    'reason' => 'Auto-populated from failed assessment',
                    'repeat_due_date' => $deadline,
                    'cap_grade' => 40,
                    'status' => 'pending',
                    'payment_amount' => $validated['payment_amount'],
                    'priority_level' => 'medium',
                    'assigned_to' => auth()->id(),
                    'deadline_date' => $deadline,
                    'workflow_stage' => 'identified',
                    'moodle_setup_status' => 'pending',
                ]);
                $created++;
            }
        });

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'created_count' => $created,
                'deadline_days' => $validated['deadline_days'],
                'payment_amount' => $validated['payment_amount'],
            ])
            ->log('Auto-populated repeat assessments from failed assessments');

        return back()->with('success', "Successfully created {$created} repeat assessments.");
    }

    public function destroy(RepeatAssessment $repeatAssessment)
    {
        // Only allow deletion if not yet approved and no payments made
        if ($repeatAssessment->isApproved() || $repeatAssessment->isPaid()) {
            return back()->with('error', 'Cannot delete an approved or paid repeat assessment.');
        }

        $studentName = $repeatAssessment->student->full_name;
        $assessmentName = $repeatAssessment->studentGradeRecord->assessment_component_name;

        $repeatAssessment->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'student_name' => $studentName,
                'assessment_name' => $assessmentName,
            ])
            ->log('Repeat assessment deleted');

        return redirect()->route('repeat-assessments.index')
            ->with('success', 'Repeat assessment deleted successfully.');
    }

    /**
     * Get failed assessments for a specific student (API endpoint)
     */
    public function getFailedAssessments(Student $student)
    {
        try {
            $failedAssessments = StudentGradeRecord::with([
                'moduleInstance.module',
                'student',
            ])
                ->where('student_id', $student->id)
                ->where(function ($query) {
                    $query->where('percentage', '<', 40)
                        ->whereNotNull('percentage');
                })
                ->whereDoesntHave('repeatAssessments') // Don't include assessments that already have repeat assessments
                ->get()
                ->groupBy('module_instance_id')
                ->map(function ($gradeRecords) {
                    $moduleInstance = $gradeRecords->first()->moduleInstance;
                    $failedComponents = $gradeRecords->where('percentage', '<', 40)->count();
                    $totalComponents = count($moduleInstance->module->assessment_strategy ?? []);

                    return [
                        'module_instance_id' => $moduleInstance->id,
                        'module_title' => $moduleInstance->module->title,
                        'module_code' => $moduleInstance->module->module_code,
                        'failed_components' => $failedComponents,
                        'total_components' => $totalComponents,
                        'lowest_grade' => $gradeRecords->min('percentage'),
                        'last_graded' => $gradeRecords->whereNotNull('graded_at')->max('graded_at')?->format('Y-m-d'),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'assessments' => $failedAssessments,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load student assessments', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load assessments',
            ], 500);
        }
    }
}
