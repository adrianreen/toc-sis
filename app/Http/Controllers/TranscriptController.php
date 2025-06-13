<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptController extends Controller
{
    /**
     * Generate and download student transcript PDF
     */
    public function download(Student $student)
    {
        // Check permissions - students can only download their own transcript
        if (Auth::user()->role === 'student') {
            if (!Auth::user()->student || Auth::user()->student->id !== $student->id) {
                abort(403, 'You can only download your own transcript.');
            }
        } elseif (!in_array(Auth::user()->role, ['manager', 'student_services', 'teacher'])) {
            abort(403, 'You do not have permission to download transcripts.');
        }

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

        // Prepare transcript data
        $transcriptData = $this->prepareTranscriptData($student);

        // Generate PDF
        $pdf = PDF::loadView('transcripts.official', $transcriptData);
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'isRemoteEnabled' => false,
        ]);

        // Log the activity
        activity()
            ->causedBy(Auth::user())
            ->performedOn($student)
            ->log('Official transcript downloaded');

        // Generate filename
        $filename = 'Transcript_' . $student->student_number . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Preview transcript in browser (for staff only)
     */
    public function preview(Student $student)
    {
        // Only staff can preview transcripts
        if (!in_array(Auth::user()->role, ['manager', 'student_services', 'teacher'])) {
            abort(403, 'You do not have permission to preview transcripts.');
        }

        // Load student data
        $student->load([
            'enrolments.programme',
            'enrolments.cohort',
            'studentModuleEnrolments.moduleInstance.module',
            'studentModuleEnrolments.moduleInstance.cohort.programme',
            'studentModuleEnrolments.studentAssessments' => function($query) {
                $query->visibleToStudents()->with('assessmentComponent');
            }
        ]);

        // Prepare transcript data
        $transcriptData = $this->prepareTranscriptData($student);

        return view('transcripts.preview', $transcriptData);
    }

    /**
     * Prepare all data needed for transcript generation
     */
    private function prepareTranscriptData(Student $student)
    {
        // Group modules by programme
        $programmeModules = [];
        $overallGPA = 0;
        $totalCredits = 0;
        $totalGradePoints = 0;

        foreach ($student->studentModuleEnrolments as $moduleEnrolment) {
            $module = $moduleEnrolment->moduleInstance->module;
            $programme = $moduleEnrolment->moduleInstance->cohort->programme;
            
            if (!isset($programmeModules[$programme->id])) {
                $programmeModules[$programme->id] = [
                    'programme' => $programme,
                    'modules' => [],
                    'total_credits' => 0,
                    'gpa' => 0
                ];
            }

            // Calculate module grade and status
            $moduleGrade = $this->calculateModuleGrade($moduleEnrolment);
            
            $programmeModules[$programme->id]['modules'][] = [
                'module' => $module,
                'enrolment' => $moduleEnrolment,
                'grade' => $moduleGrade['grade'],
                'status' => $moduleGrade['status'],
                'completion_date' => $moduleGrade['completion_date'],
                'credits' => $module->credits ?? 5, // Default 5 credits if not set
            ];

            // Add to totals for GPA calculation
            if ($moduleGrade['grade'] && $moduleGrade['status'] === 'Completed') {
                $credits = $module->credits ?? 5;
                $gradePoint = $this->gradeToPoints($moduleGrade['grade']);
                
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
                    $gradePoint = $this->gradeToPoints($moduleData['grade']);
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
                'name' => 'The Open College',
                'address' => 'Dublin, Ireland',
                'website' => 'www.theopencollege.com',
                'phone' => '+353 1 234 5678'
            ]
        ];
    }

    /**
     * Calculate the final grade for a module based on assessments
     */
    private function calculateModuleGrade($moduleEnrolment)
    {
        $assessments = $moduleEnrolment->studentAssessments->filter(function($assessment) {
            return $assessment->isVisibleToStudent();
        });
        
        if ($assessments->isEmpty()) {
            return [
                'grade' => null,
                'status' => 'In Progress',
                'completion_date' => null
            ];
        }

        $totalMark = 0;
        $totalWeight = 0;
        $completionDate = null;
        $allPassed = true;

        foreach ($assessments as $assessment) {
            if ($assessment->grade !== null) {
                $weight = $assessment->assessmentComponent->weight ?? 100;
                $totalMark += ($assessment->grade * $weight / 100);
                $totalWeight += $weight;
                
                // Check if this assessment passed (QQI pass is 50%)
                if ($assessment->grade < 50) {
                    $allPassed = false;
                }
                
                // Get latest completion date
                if ($assessment->updated_at && (!$completionDate || $assessment->updated_at > $completionDate)) {
                    $completionDate = $assessment->updated_at;
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
        $finalMark = round($totalMark, 1);
        
        // Determine grade and status
        $grade = $this->markToGrade($finalMark);
        $status = $allPassed && $finalMark >= 50 ? 'Completed' : ($finalMark > 0 ? 'Failed' : 'In Progress');

        return [
            'grade' => $grade,
            'status' => $status,
            'completion_date' => $completionDate
        ];
    }

    /**
     * Convert numerical mark to QQI grade
     */
    private function markToGrade($mark)
    {
        if ($mark >= 80) return 'D'; // Distinction
        if ($mark >= 65) return 'M'; // Merit
        if ($mark >= 50) return 'P'; // Pass
        return 'U'; // Unsuccessful
    }

    /**
     * Convert QQI grade to points for weighted average
     */
    private function gradeToPoints($grade)
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