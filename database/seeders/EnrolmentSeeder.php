<?php

namespace Database\Seeders;

use App\Models\Enrolment;
use App\Models\ModuleInstance;
use App\Models\ProgrammeInstance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EnrolmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::where('status', 'active')->get();
        $programmeInstances = ProgrammeInstance::all();
        $standaloneModuleInstances = ModuleInstance::whereNull('programme_instance_id')->get();

        $this->command->info("Creating enrolments for {$students->count()} active students...");

        foreach ($students as $student) {
            $this->createStudentEnrolments($student, $programmeInstances, $standaloneModuleInstances);
        }

        $this->command->info('Enrolments created successfully!');
    }

    private function createStudentEnrolments(Student $student, $programmeInstances, $standaloneModuleInstances)
    {
        $enrollmentType = $this->determineEnrollmentType($student);

        switch ($enrollmentType) {
            case 'programme':
                $this->createProgrammeEnrolment($student, $programmeInstances);
                break;
            case 'standalone_modules':
                $this->createStandaloneModuleEnrolments($student, $standaloneModuleInstances);
                break;
            case 'mixed':
                // Some students have both programme and standalone module enrolments
                $this->createProgrammeEnrolment($student, $programmeInstances);
                $this->createStandaloneModuleEnrolments($student, $standaloneModuleInstances, 2); // Just 1-2 additional modules
                break;
        }
    }

    private function determineEnrollmentType(Student $student): string
    {
        $studentId = $student->id;

        // Distribute students across different enrollment patterns
        if ($studentId % 10 <= 6) {
            return 'programme'; // 70% programme enrolments
        } elseif ($studentId % 10 <= 8) {
            return 'standalone_modules'; // 20% standalone module enrolments
        } else {
            return 'mixed'; // 10% mixed enrolments
        }
    }

    private function createProgrammeEnrolment(Student $student, $programmeInstances)
    {
        // Select appropriate programme based on student characteristics
        $suitableProgrammes = $this->getSuitableProgrammesForStudent($student, $programmeInstances);

        if ($suitableProgrammes->isEmpty()) {
            return;
        }

        $selectedProgramme = $suitableProgrammes->random();

        // Create programme enrolment
        Enrolment::create([
            'student_id' => $student->id,
            'enrolment_type' => 'programme',
            'programme_instance_id' => $selectedProgramme->id,
            'module_instance_id' => null,
            'status' => $this->getEnrolmentStatus(),
            'enrolment_date' => $this->getEnrolmentDate($selectedProgramme),
            'created_by' => 1,
        ]);
    }

    private function createStandaloneModuleEnrolments(Student $student, $standaloneModuleInstances, $maxModules = 5)
    {
        // Create 1-5 standalone module enrolments
        $moduleCount = rand(1, $maxModules);
        $selectedModules = $standaloneModuleInstances->random(min($moduleCount, $standaloneModuleInstances->count()));

        foreach ($selectedModules as $moduleInstance) {
            Enrolment::create([
                'student_id' => $student->id,
                'enrolment_type' => 'module',
                'programme_instance_id' => null,
                'module_instance_id' => $moduleInstance->id,
                'status' => $this->getEnrolmentStatus(),
                'enrolment_date' => $this->getEnrolmentDate($moduleInstance),
                'created_by' => 1,
            ]);
        }
    }

    private function getSuitableProgrammesForStudent(Student $student, $programmeInstances)
    {
        $studentId = $student->id;

        // Distribute students across different programme types based on their ID
        $programmePreference = match ($studentId % 6) {
            0, 1 => 'Business Management', // 33% business
            2 => 'Information Technology',  // 17% IT
            3 => 'Digital Marketing',       // 17% marketing
            4 => 'Data Analytics',          // 17% analytics
            5 => 'Certificate',             // 17% certificates
            default => 'Business Management'
        };

        // Filter programme instances based on preference
        return $programmeInstances->filter(function ($programmeInstance) use ($programmePreference) {
            $programmeTitle = $programmeInstance->programme->title;

            if ($programmePreference === 'Certificate') {
                return str_contains($programmeTitle, 'Certificate');
            }

            return str_contains($programmeTitle, $programmePreference);
        });
    }

    private function getEnrolmentStatus(): string
    {
        // Most students are enrolled, some are completed, few are withdrawn
        $rand = rand(1, 100);

        if ($rand <= 75) {
            return 'enrolled';
        } elseif ($rand <= 90) {
            return 'completed';
        } elseif ($rand <= 95) {
            return 'deferred';
        } else {
            return 'withdrawn';
        }
    }

    private function getEnrolmentDate($instance): Carbon
    {
        // Enrolment date should be around the intake start date
        $baseDate = $instance->start_date ?? $instance->intake_start_date ?? Carbon::now();

        // Add some randomness (±30 days from start date)
        $daysOffset = rand(-30, 30);

        return $baseDate->copy()->addDays($daysOffset);
    }
}
