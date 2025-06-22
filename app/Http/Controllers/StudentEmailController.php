<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\Student;
use App\Mail\TemplateMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:manager,student_services,teacher']);
    }

    public function index(Student $student)
    {
        $availableTemplates = EmailTemplate::active()
                                          ->orderBy('category')
                                          ->orderBy('name')
                                          ->get()
                                          ->groupBy('category');

        $recentEmails = $student->emailLogs()
                               ->with(['emailTemplate', 'sentBy'])
                               ->latest()
                               ->limit(5)
                               ->get();

        return view('admin.student-emails.index', compact('student', 'availableTemplates', 'recentEmails'));
    }

    public function compose(Student $student, Request $request)
    {
        $template = null;
        if ($request->filled('template_id')) {
            $template = EmailTemplate::active()->findOrFail($request->template_id);
        }

        $availableTemplates = EmailTemplate::active()
                                          ->orderBy('category')
                                          ->orderBy('name')
                                          ->get()
                                          ->groupBy('category');

        return view('admin.student-emails.compose', compact('student', 'template', 'availableTemplates'));
    }

    public function preview(Student $student, Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'include_transcript' => 'boolean',
        ]);

        $template = EmailTemplate::findOrFail($request->template_id);
        $processed = $template->replaceVariables($student, Auth::user());

        $attachmentInfo = null;
        if ($request->boolean('include_transcript')) {
            $attachmentInfo = [
                'name' => "transcript_{$student->student_number}.pdf",
                'size' => 'Estimated: 150-300 KB',
            ];
        }

        return response()->json([
            'subject' => $processed['subject'],
            'body_html' => $processed['body_html'],
            'attachment' => $attachmentInfo,
        ]);
    }

    public function send(Student $student, Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'include_transcript' => 'boolean',
            'custom_message' => 'nullable|string|max:500',
        ]);

        $template = EmailTemplate::active()->findOrFail($request->template_id);
        
        // Create email log entry
        $emailLog = EmailLog::create([
            'email_template_id' => $template->id,
            'student_id' => $student->id,
            'sent_by' => Auth::id(),
            'recipient_email' => $student->email,
            'subject' => $template->subject,
            'delivery_status' => 'pending',
            'has_attachment' => $request->boolean('include_transcript'),
        ]);

        try {
            $attachmentPath = null;
            $attachmentName = null;

            // Generate transcript PDF if requested
            if ($request->boolean('include_transcript')) {
                $attachmentPath = $this->generateTranscriptPDF($student);
                $attachmentName = "transcript_{$student->student_number}.pdf";
                
                $emailLog->update([
                    'attachment_info' => json_encode([
                        'type' => 'transcript',
                        'filename' => $attachmentName,
                        'generated_at' => now()->toISOString(),
                    ])
                ]);
            }

            // Add custom message to variables if provided
            $customVariables = [];
            if ($request->filled('custom_message')) {
                $customVariables['custom_message'] = $request->custom_message;
            }

            // Send email
            Mail::to($student->email)->send(new TemplateMail(
                $template,
                $student,
                Auth::user(),
                $customVariables,
                $attachmentPath,
                $attachmentName
            ));

            // Update log as sent
            $emailLog->markAsSent();
            
            // Store processed variables for audit
            $processed = $template->replaceVariables($student, Auth::user(), $customVariables);
            $emailLog->update(['variables_used' => $processed['variables_used']]);

            // Clean up temporary transcript file
            if ($attachmentPath && file_exists($attachmentPath)) {
                unlink($attachmentPath);
            }

            return redirect()
                ->route('students.show', $student)
                ->with('success', "Email sent successfully to {$student->full_name}.");

        } catch (\Exception $e) {
            // Mark as failed and log error
            $emailLog->markAsFailed($e->getMessage());

            // Clean up temporary files
            if (isset($attachmentPath) && $attachmentPath && file_exists($attachmentPath)) {
                unlink($attachmentPath);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function quickSend(Student $student, Request $request)
    {
        $request->validate([
            'action' => 'required|in:results_transcript,welcome,reminder',
        ]);

        // Map quick actions to template names
        $templateMap = [
            'results_transcript' => 'Student Results with Transcript',
            'welcome' => 'Welcome Email',
            'reminder' => 'Assessment Reminder',
        ];

        $templateName = $templateMap[$request->action];
        $template = EmailTemplate::active()
                                 ->where('name', $templateName)
                                 ->first();

        if (!$template) {
            return back()->with('error', "Template '{$templateName}' not found. Please create it first.");
        }

        // Auto-include transcript for results action
        $includeTranscript = $request->action === 'results_transcript';

        return $this->send($student, new Request([
            'template_id' => $template->id,
            'include_transcript' => $includeTranscript,
        ]));
    }

    private function generateTranscriptPDF(Student $student): string
    {
        // Load student data with all related information for transcript
        $student->load([
            'enrolments.programme',
            'enrolments.cohort',
            'studentModuleEnrolments.moduleInstance.module',
            'studentModuleEnrolments.moduleInstance.cohort.programme',
            'studentModuleEnrolments.studentAssessments' => function($query) {
                // Only show visible results
                $query->visibleToStudents()->with('assessmentComponent');
            }
        ]);

        // Use the same transcript data preparation as the main TranscriptController
        $transcriptData = $this->prepareTranscriptDataForEmail($student);

        // Generate transcript PDF using the existing transcript system
        $pdf = Pdf::loadView('transcripts.official', $transcriptData);
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'isRemoteEnabled' => false,
        ]);
        
        // Create temporary file
        $tempPath = storage_path('app/temp/transcript_' . $student->id . '_' . uniqid() . '.pdf');
        
        // Ensure temp directory exists
        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        $pdf->save($tempPath);
        
        return $tempPath;
    }

    /**
     * Prepare transcript data for email attachments (simplified version of TranscriptController method)
     */
    private function prepareTranscriptDataForEmail(Student $student): array
    {
        // Group modules by programme
        $programmeModules = [];
        $overallGPA = 0;
        $totalCredits = 0;
        $totalGradePoints = 0;

        // Get grade records grouped by module instance
        $gradesByModule = $student->studentGradeRecords->groupBy('module_instance_id');

        foreach ($gradesByModule as $moduleInstanceId => $gradeRecords) {
            $moduleInstance = $gradeRecords->first()->moduleInstance;
            $module = $moduleInstance->module;
            
            // Find which programme this module belongs to (if any)
            $programmeEnrolment = $student->enrolments()
                ->where('enrolment_type', 'programme')
                ->whereHas('programmeInstance.moduleInstances', function($query) use ($moduleInstanceId) {
                    $query->where('module_instances.id', $moduleInstanceId);
                })->first();

            if (!$programmeEnrolment) {
                continue; // Skip standalone modules for now
            }
            
            $programme = $programmeEnrolment->programmeInstance->programme;
            
            if (!isset($programmeModules[$programme->id])) {
                $programmeModules[$programme->id] = [
                    'programme' => $programme,
                    'modules' => [],
                    'total_credits' => 0,
                    'gpa' => 0
                ];
            }

            // Calculate module grade and status
            $moduleGrade = $this->calculateModuleGradeForEmail($gradeRecords, $module);
            
            $programmeModules[$programme->id]['modules'][] = [
                'module' => $module,
                'moduleInstance' => $moduleInstance,
                'grade' => $moduleGrade['grade'],
                'status' => $moduleGrade['status'],
                'completion_date' => $moduleGrade['completion_date'],
                'credits' => $module->credit_value ?? 5, // Default 5 credits if not set
            ];

            // Add to totals for GPA calculation
            if ($moduleGrade['grade'] && $moduleGrade['status'] === 'Completed') {
                $credits = $module->credit_value ?? 5;
                $gradePoint = $this->gradeToPointsForEmail($moduleGrade['grade']);
                
                $totalCredits += $credits;
                $totalGradePoints += ($gradePoint * $credits);
                
                $programmeModules[$programme->id]['total_credits'] += $credits;
            }
        }

        // Calculate overall GPA
        if ($totalCredits > 0) {
            $overallGPA = round($totalGradePoints / $totalCredits, 2);
        }

        // Calculate programme GPAs
        foreach ($programmeModules as &$programmeData) {
            $progCredits = 0;
            $progGradePoints = 0;
            
            foreach ($programmeData['modules'] as $moduleData) {
                if ($moduleData['grade'] && $moduleData['status'] === 'Completed') {
                    $gradePoint = $this->gradeToPointsForEmail($moduleData['grade']);
                    $progCredits += $moduleData['credits'];
                    $progGradePoints += ($gradePoint * $moduleData['credits']);
                }
            }
            
            if ($progCredits > 0) {
                $programmeData['gpa'] = round($progGradePoints / $progCredits, 2);
            }
        }

        return [
            'student' => $student,
            'programmeModules' => $programmeModules,
            'overallGPA' => $overallGPA,
            'totalCredits' => $totalCredits,
            'generatedDate' => now(),
            'institution' => [
                'name' => config('app.name', 'The Open College'),
                'address' => 'Dublin, Ireland',
                'website' => 'www.theopencollege.com',
                'phone' => '+353 1 234 5678'
            ]
        ];
    }

    /**
     * Calculate the final grade for a module based on grade records (for email)
     */
    private function calculateModuleGradeForEmail($gradeRecords, $module): array
    {
        $gradedRecords = $gradeRecords->whereNotNull('grade');
        
        if ($gradedRecords->isEmpty()) {
            return [
                'grade' => null,
                'status' => 'In Progress',
                'completion_date' => null
            ];
        }

        $totalWeightedMark = 0;
        $totalWeight = 0;
        $completionDate = null;
        $allComponentsPassed = true;

        // Get assessment strategy from module
        $assessmentStrategy = $module->assessment_strategy ?? [];
        
        foreach ($assessmentStrategy as $component) {
            $gradeRecord = $gradedRecords->where('assessment_component_name', $component['component_name'])->first();
            
            if ($gradeRecord && $gradeRecord->grade !== null) {
                $weight = $component['weighting'];
                $percentage = $gradeRecord->percentage;
                
                $totalWeightedMark += ($percentage * $weight / 100);
                $totalWeight += $weight;
                
                // Check component pass requirements
                $componentPassMark = $component['component_pass_mark'] ?? 40;
                if ($component['is_must_pass'] && $percentage < $componentPassMark) {
                    $allComponentsPassed = false;
                }
                
                // Get latest completion date
                if ($gradeRecord->graded_date && (!$completionDate || $gradeRecord->graded_date > $completionDate)) {
                    $completionDate = $gradeRecord->graded_date;
                }
            }
        }

        if ($totalWeight === 0) {
            return [
                'grade' => null,
                'status' => 'In Progress',
                'completion_date' => null
            ];
        }

        // Calculate final percentage
        $finalMark = round($totalWeightedMark, 1);
        
        // Determine grade and status
        $grade = $this->markToGradeForEmail($finalMark);
        $status = $allComponentsPassed && $finalMark >= 40 ? 'Completed' : ($finalMark > 0 ? 'Failed' : 'In Progress');

        return [
            'grade' => $grade,
            'status' => $status,
            'completion_date' => $completionDate
        ];
    }

    /**
     * Convert numerical mark to QQI grade (for email)
     */
    private function markToGradeForEmail($mark): string
    {
        if ($mark >= 80) return 'D'; // Distinction
        if ($mark >= 65) return 'M'; // Merit
        if ($mark >= 50) return 'P'; // Pass
        return 'U'; // Unsuccessful
    }

    /**
     * Convert QQI grade to points for weighted average (for email)
     */
    private function gradeToPointsForEmail($grade): float
    {
        switch ($grade) {
            case 'D': return 3.0; // Distinction
            case 'M': return 2.0; // Merit
            case 'P': return 1.0; // Pass
            case 'U': return 0.0; // Unsuccessful
            default: return 0.0;
        }
    }
}