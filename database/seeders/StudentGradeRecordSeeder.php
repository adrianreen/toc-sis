<?php

namespace Database\Seeders;

use App\Models\Enrolment;
use App\Models\StudentGradeRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentGradeRecordSeeder extends Seeder
{
    public function run(): void
    {
        $enrolments = Enrolment::with(['student', 'moduleInstance.module', 'programmeInstance.curriculum.module'])
            ->where('status', '!=', 'withdrawn')
            ->get();

        $teachers = User::where('role', 'teacher')->get();

        $this->command->info("Creating grade records for {$enrolments->count()} enrolments...");

        foreach ($enrolments as $enrolment) {
            $this->createGradeRecordsForEnrolment($enrolment, $teachers);
        }

        $this->command->info('Grade records created successfully!');
    }

    private function createGradeRecordsForEnrolment(Enrolment $enrolment, $teachers)
    {
        $modules = [];

        if ($enrolment->enrolment_type === 'programme') {
            // Get all modules in the programme curriculum
            $modules = $enrolment->programmeInstance->curriculum ?? collect();
        } elseif ($enrolment->enrolment_type === 'module') {
            // Single module enrolment
            $modules = collect([$enrolment->moduleInstance]);
        }

        foreach ($modules as $moduleInstance) {
            $this->createGradeRecordsForModule($enrolment->student, $moduleInstance, $teachers);
        }
    }

    private function createGradeRecordsForModule($student, $moduleInstance, $teachers)
    {
        $module = $moduleInstance->module;
        $assessmentStrategy = $module->assessment_strategy ?? [];

        if (empty($assessmentStrategy)) {
            return; // Skip if no assessment components defined
        }

        foreach ($assessmentStrategy as $index => $assessmentComponent) {
            $this->createGradeRecord($student, $moduleInstance, $assessmentComponent, $index, $teachers);
        }
    }

    private function createGradeRecord($student, $moduleInstance, $assessmentComponent, $componentIndex, $teachers)
    {
        // Determine if this assessment should have a grade yet
        $shouldHaveGrade = $this->shouldAssessmentHaveGrade($moduleInstance);

        if (! $shouldHaveGrade) {
            return; // Skip creating grade record if assessment isn't due yet
        }

        // Generate realistic grade based on student performance profile
        $grade = $this->generateRealisticGrade($student, $assessmentComponent, $moduleInstance->module);

        // Select appropriate grading teacher
        $gradingTeacher = $moduleInstance->tutor_user_id ?
                         User::find($moduleInstance->tutor_user_id) :
                         $teachers->random();

        // Determine submission and grading dates
        $dates = $this->generateAssessmentDates($moduleInstance, $componentIndex);

        // Determine visibility based on release policies
        $isVisible = $this->shouldGradeBeVisible($dates['graded_date']);

        StudentGradeRecord::create([
            'student_id' => $student->id,
            'module_instance_id' => $moduleInstance->id,
            'assessment_component_name' => $assessmentComponent['component_name'],
            'assessment_component_weighting' => $assessmentComponent['weighting'],
            'grade' => $grade,
            'percentage' => $grade, // Assuming grade is already a percentage
            'is_pass' => $grade >= ($assessmentComponent['component_pass_mark'] ?? $moduleInstance->module->default_pass_mark ?? 40),
            'submission_date' => $dates['submission_date'],
            'graded_date' => $dates['graded_date'],
            'graded_by' => $gradingTeacher->id,
            'is_visible_to_student' => $isVisible,
            'release_date' => $isVisible ? $dates['graded_date'] : $dates['graded_date']->addWeeks(2),
            'feedback' => $this->generateFeedback($grade, $assessmentComponent['component_name']),
            'created_by' => $gradingTeacher->id,
        ]);
    }

    private function shouldAssessmentHaveGrade($moduleInstance): bool
    {
        $moduleStartDate = $moduleInstance->start_date;
        $moduleEndDate = $moduleInstance->target_end_date;
        $now = Carbon::now();

        if (! $moduleStartDate) {
            return false;
        }

        // Only create grades for modules that have started
        if ($moduleStartDate->isAfter($now)) {
            return false;
        }

        // 80% chance of having grades if module is more than 25% complete
        $moduleProgress = $moduleStartDate->diffInDays($now) / $moduleStartDate->diffInDays($moduleEndDate);

        return $moduleProgress > 0.25 && (rand(1, 100) <= 80);
    }

    private function generateRealisticGrade($student, $assessmentComponent, $module): int
    {
        // Create student performance profile based on student ID
        $studentPerformance = $this->getStudentPerformanceProfile($student->id);

        // Base grade around the performance level
        $baseGrade = $studentPerformance['average_grade'];

        // Add randomness and assessment-specific factors
        $randomFactor = rand(-15, 15);
        $assessmentDifficulty = $this->getAssessmentDifficulty($assessmentComponent['component_name']);

        $grade = $baseGrade + $randomFactor + $assessmentDifficulty;

        // Ensure grade is within bounds (0-100)
        $grade = max(0, min(100, $grade));

        // Apply pass/fail logic for must-pass components
        if ($assessmentComponent['is_must_pass'] && $grade < 40) {
            // 70% chance to bump up to just passing for must-pass components
            if (rand(1, 100) <= 70) {
                $grade = rand(40, 50);
            }
        }

        return $grade;
    }

    private function getStudentPerformanceProfile($studentId): array
    {
        // Create consistent performance profiles based on student ID
        $profileType = $studentId % 5;

        return match ($profileType) {
            0 => ['average_grade' => 75, 'consistency' => 'high'],    // High performer
            1 => ['average_grade' => 65, 'consistency' => 'medium'],  // Good performer
            2 => ['average_grade' => 55, 'consistency' => 'medium'],  // Average performer
            3 => ['average_grade' => 45, 'consistency' => 'low'],     // Struggling performer
            4 => ['average_grade' => 85, 'consistency' => 'high'],    // Excellent performer
            default => ['average_grade' => 60, 'consistency' => 'medium']
        };
    }

    private function getAssessmentDifficulty($componentName): int
    {
        $difficultyMap = [
            'Final Examination' => -5,        // Slightly harder
            'Practical Examination' => -3,    // Moderately harder
            'Individual Report' => 2,         // Slightly easier
            'Group Presentation' => 5,        // Easier (group work)
            'Portfolio' => 3,                 // Easier (ongoing work)
            'Essay' => 0,                     // Neutral
            'Project' => -2,                  // Slightly harder
            'Skills Test' => -3,              // Moderately harder
            'Assignment' => 2,                // Slightly easier
        ];

        foreach ($difficultyMap as $keyword => $adjustment) {
            if (str_contains($componentName, $keyword)) {
                return $adjustment;
            }
        }

        return 0; // Default neutral adjustment
    }

    private function generateAssessmentDates($moduleInstance, $componentIndex): array
    {
        $moduleStart = $moduleInstance->start_date;
        $moduleEnd = $moduleInstance->target_end_date;

        if (! $moduleStart || ! $moduleEnd) {
            $moduleStart = Carbon::now()->subMonths(2);
            $moduleEnd = Carbon::now()->addMonth();
        }

        // Spread assessments throughout the module
        $totalComponents = 3; // Assume average of 3 components
        $assessmentPoint = ($componentIndex + 1) / $totalComponents;

        $submissionDate = $moduleStart->copy()->addDays(
            $moduleStart->diffInDays($moduleEnd) * $assessmentPoint
        );

        // Grading happens 1-2 weeks after submission
        $gradedDate = $submissionDate->copy()->addDays(rand(7, 14));

        return [
            'submission_date' => $submissionDate,
            'graded_date' => $gradedDate,
        ];
    }

    private function shouldGradeBeVisible($gradedDate): bool
    {
        $now = Carbon::now();

        // Grades are visible if:
        // 1. They were graded more than a week ago (80% chance)
        // 2. They were graded recently but admin made them visible (20% chance)

        if ($gradedDate->addWeek()->isPast()) {
            return rand(1, 100) <= 80;
        }

        return rand(1, 100) <= 20;
    }

    private function generateFeedback($grade, $componentName): ?string
    {
        // 60% chance of having feedback
        if (rand(1, 100) > 60) {
            return null;
        }

        $feedbackTemplates = [
            'excellent' => [
                'Excellent work demonstrating comprehensive understanding of the subject matter.',
                'Outstanding performance with clear evidence of critical thinking and analysis.',
                'Exceptional quality work that exceeds expectations in all areas.',
                'Brilliant execution showing mastery of the learning outcomes.',
            ],
            'good' => [
                'Good work with solid understanding demonstrated throughout.',
                'Well structured with good analysis and clear presentation.',
                'Competent performance meeting most of the assessment criteria.',
                'Good grasp of the subject with room for minor improvements.',
            ],
            'satisfactory' => [
                'Satisfactory work meeting the basic requirements.',
                'Adequate understanding shown with some areas for development.',
                'Meets the learning outcomes with scope for improvement.',
                'Basic competency demonstrated with potential for growth.',
            ],
            'needs_improvement' => [
                'Work shows some understanding but requires significant improvement.',
                'Several key areas need development to meet the required standard.',
                'Basic attempt made but fails to meet several assessment criteria.',
                'Requires substantial improvement to demonstrate competency.',
            ],
        ];

        $category = match (true) {
            $grade >= 80 => 'excellent',
            $grade >= 65 => 'good',
            $grade >= 50 => 'satisfactory',
            default => 'needs_improvement'
        };

        $baseFeedback = $feedbackTemplates[$category][array_rand($feedbackTemplates[$category])];

        // Add component-specific feedback
        $specificFeedback = match (true) {
            str_contains($componentName, 'Report') => ' The report structure and referencing were particularly noteworthy.',
            str_contains($componentName, 'Presentation') => ' The delivery and visual aids enhanced the overall presentation.',
            str_contains($componentName, 'Examination') => ' Time management and question selection were appropriate.',
            str_contains($componentName, 'Project') => ' The project scope and implementation were well considered.',
            default => ''
        };

        return $baseFeedback.$specificFeedback;
    }
}
