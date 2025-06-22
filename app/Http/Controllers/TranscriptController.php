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
            'enrolments.programmeInstance.programme',
            'enrolments.moduleInstance.module',
            'studentGradeRecords' => function($query) {
                // Only show visible results
                $query->where(function ($q) {
                    $q->where('is_visible_to_student', true)
                      ->orWhere(function ($subQ) {
                          $subQ->whereNotNull('release_date')
                               ->where('release_date', '<=', now());
                      });
                })->with('moduleInstance.module');
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
            'enrolments.programmeInstance.programme',
            'enrolments.moduleInstance.module',
            'studentGradeRecords' => function($query) {
                $query->where(function ($q) {
                    $q->where('is_visible_to_student', true)
                      ->orWhere(function ($subQ) {
                          $subQ->whereNotNull('release_date')
                               ->where('release_date', '<=', now());
                      });
                })->with('moduleInstance.module');
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
        // Group modules by programme using new architecture
        $programmeModules = [];
        $standaloneModules = [];
        $overallGPA = 0;
        $totalCredits = 0;
        $totalGradePoints = 0;

        // Get grade records grouped by module instance
        $gradesByModule = $student->studentGradeRecords->groupBy('module_instance_id');

        foreach ($gradesByModule as $moduleInstanceId => $gradeRecords) {
            $moduleInstance = $gradeRecords->first()->moduleInstance;
            $module = $moduleInstance->module;
            
            // Calculate module grade and status from grade records
            $moduleGrade = $this->calculateModuleGradeFromRecords($gradeRecords, $module);
            
            // Find which programme this module belongs to (if any)
            $programmeEnrolment = $student->enrolments()
                ->where('enrolment_type', 'programme')
                ->whereHas('programmeInstance.moduleInstances', function($query) use ($moduleInstanceId) {
                    $query->where('module_instances.id', $moduleInstanceId);
                })->first();

            if ($programmeEnrolment) {
                // This is a programme module
                $programme = $programmeEnrolment->programmeInstance->programme;
                
                if (!isset($programmeModules[$programme->id])) {
                    $programmeModules[$programme->id] = [
                        'programme' => $programme,
                        'programmeInstance' => $programmeEnrolment->programmeInstance,
                        'modules' => [],
                        'total_credits' => 0,
                        'gpa' => 0
                    ];
                }

                $programmeModules[$programme->id]['modules'][] = [
                    'module' => $module,
                    'moduleInstance' => $moduleInstance,
                    'grade' => $moduleGrade['grade'],
                    'status' => $moduleGrade['status'],
                    'completion_date' => $moduleGrade['completion_date'],
                    'credits' => $module->credit_value ?? 5,
                ];

                // Add to programme totals
                if ($moduleGrade['grade'] && $moduleGrade['status'] === 'Completed') {
                    $programmeModules[$programme->id]['total_credits'] += ($module->credit_value ?? 5);
                }
            } else {
                // This is a standalone module
                $standaloneModules[] = [
                    'module' => $module,
                    'moduleInstance' => $moduleInstance,
                    'grade' => $moduleGrade['grade'],
                    'status' => $moduleGrade['status'],
                    'completion_date' => $moduleGrade['completion_date'],
                    'credits' => $module->credit_value ?? 5,
                ];
            }

            // Add to overall totals for GPA calculation
            if ($moduleGrade['grade'] && $moduleGrade['status'] === 'Completed') {
                $credits = $module->credit_value ?? 5;
                $gradePoint = $this->gradeToPoints($moduleGrade['grade']);
                
                $totalCredits += $credits;
                $totalGradePoints += ($gradePoint * $credits);
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
            'standaloneModules' => $standaloneModules,
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
     * Calculate the final grade for a module based on grade records
     */
    private function calculateModuleGradeFromRecords($gradeRecords, $module)
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
        $grade = $this->markToGrade($finalMark);
        $status = $allComponentsPassed && $finalMark >= 40 ? 'Completed' : ($finalMark > 0 ? 'Failed' : 'In Progress');

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