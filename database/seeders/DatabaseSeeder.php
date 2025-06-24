<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Student;
use App\Models\StudentGradeRecord;
use App\Models\User;
use App\Services\EnrolmentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * NEW 4-LEVEL ARCHITECTURE COMPATIBLE SEEDER
     * This replaces the old seeder that was incompatible with the new architecture.
     */
    public function run(): void
    {
        // Create basic users for testing
        $this->createUsers();

        // Create programmes and programme instances
        $this->createProgrammesAndInstances();

        // Create modules with assessment strategies
        $this->createModulesWithAssessmentStrategies();

        // Create module instances and link to programme instances
        $this->createModuleInstancesAndCurriculum();

        // Create students
        $this->createStudents();

        // Create enrollments using the new two-path system
        $this->createEnrollments();

        // Create grade records for testing
        $this->createGradeRecords();

        $this->command->info('✅ New 4-level architecture seeding completed successfully!');
    }

    private function createUsers(): void
    {
        // Admin user
        User::create([
            'name' => 'Adrian Reen',
            'email' => 'adrian.reen@theopencollege.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

        // Student Services user
        User::create([
            'name' => 'Student Services',
            'email' => 'studentservices@theopencollege.com',
            'password' => Hash::make('password'),
            'role' => 'student_services',
            'email_verified_at' => now(),
        ]);

        // Teacher user
        User::create([
            'name' => 'Jane Teacher',
            'email' => 'jane.teacher@theopencollege.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Created users');
    }

    private function createProgrammesAndInstances(): void
    {
        // Create BA Business Management Programme
        $programme = Programme::create([
            'programme_code' => 'BABM',
            'title' => 'Bachelor of Arts in Business Management',
            'description' => 'A comprehensive business management degree programme.',
            'awarding_body' => 'Quality and Qualifications Ireland (QQI)',
            'nfq_level' => 7,
            'total_credits' => 180,
            'created_by' => 1,
        ]);

        // Create programme instance
        ProgrammeInstance::create([
            'programme_id' => $programme->id,
            'label' => 'September 2024 Intake',
            'start_date' => Carbon::parse('2024-09-01'),
            'end_date' => Carbon::parse('2027-06-30'),
            'delivery_style' => 'sync',
            'created_by' => 1,
        ]);

        $this->command->info('Created programmes and instances');
    }

    private function createModulesWithAssessmentStrategies(): void
    {
        Module::create([
            'module_code' => 'STRAT001',
            'title' => 'Strategic Management',
            'description' => 'Introduction to strategic management principles.',
            'credit_value' => 10,
            'pass_mark' => 40,
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
            'assessment_strategy' => [
                [
                    'component_name' => 'Strategic Analysis Report',
                    'weighting' => 40,
                    'is_must_pass' => false,
                ],
                [
                    'component_name' => 'Case Study Presentation',
                    'weighting' => 30,
                    'is_must_pass' => false,
                ],
                [
                    'component_name' => 'Final Examination',
                    'weighting' => 30,
                    'is_must_pass' => true,
                    'component_pass_mark' => 40,
                ],
            ],
            'created_by' => 1,
        ]);

        Module::create([
            'module_code' => 'MARK001',
            'title' => 'Introduction to Marketing',
            'description' => 'Fundamentals of marketing theory and practice.',
            'credit_value' => 10,
            'pass_mark' => 40,
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'monthly',
            'assessment_strategy' => [
                [
                    'component_name' => 'Marketing Plan',
                    'weighting' => 50,
                    'is_must_pass' => false,
                ],
                [
                    'component_name' => 'Group Project',
                    'weighting' => 30,
                    'is_must_pass' => false,
                ],
                [
                    'component_name' => 'Final Exam',
                    'weighting' => 20,
                    'is_must_pass' => true,
                    'component_pass_mark' => 40,
                ],
            ],
            'created_by' => 1,
        ]);

        $this->command->info('Created modules with assessment strategies');
    }

    private function createModuleInstancesAndCurriculum(): void
    {
        $teacherUser = User::where('role', 'teacher')->first();
        $programmeInstance = ProgrammeInstance::first();
        $strategicModule = Module::where('module_code', 'STRAT001')->first();
        $marketingModule = Module::where('module_code', 'MARK001')->first();

        // Create module instances
        $strategicInstance = ModuleInstance::create([
            'module_id' => $strategicModule->id,
            'tutor_id' => $teacherUser->id,
            'start_date' => Carbon::parse('2024-09-01'),
            'end_date' => Carbon::parse('2024-12-15'),
            'delivery_style' => 'sync',
            'created_by' => 1,
        ]);

        $marketingInstance = ModuleInstance::create([
            'module_id' => $marketingModule->id,
            'tutor_id' => $teacherUser->id,
            'start_date' => Carbon::parse('2024-09-01'),
            'end_date' => Carbon::parse('2024-12-15'),
            'delivery_style' => 'sync',
            'created_by' => 1,
        ]);

        // Link module instances to programme instance (curriculum)
        $programmeInstance->moduleInstances()->attach([
            $strategicInstance->id,
            $marketingInstance->id,
        ]);

        $this->command->info('Created module instances and curriculum links');
    }

    private function createStudents(): void
    {
        $student = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Anna',
            'last_name' => 'Kowalski',
            'email' => 'anna.kowalski1004@student.ie',
            'phone' => '+353 1 234 5678',
            'date_of_birth' => Carbon::parse('1995-10-04'),
            'status' => 'active',
            'created_by' => 1,
        ]);

        // Create user account for student
        User::create([
            'name' => $student->full_name,
            'email' => $student->email,
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => $student->id,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Created students');
    }

    private function createEnrollments(): void
    {
        $student = Student::first();
        $programmeInstance = ProgrammeInstance::first();
        $enrolmentService = app(EnrolmentService::class);

        // Enroll student in programme using the EnrolmentService
        $enrolmentService->enrolInProgramme($student, $programmeInstance, 1);

        $this->command->info('Created enrollments using EnrolmentService');
    }

    private function createGradeRecords(): void
    {
        $student = Student::first();
        $moduleInstances = ModuleInstance::all();

        foreach ($moduleInstances as $moduleInstance) {
            $module = $moduleInstance->module;

            foreach ($module->assessment_strategy as $component) {
                StudentGradeRecord::create([
                    'student_id' => $student->id,
                    'module_instance_id' => $moduleInstance->id,
                    'assessment_component_name' => $component['component_name'],
                    'grade' => rand(50, 95),
                    'max_grade' => 100,
                    'submission_date' => Carbon::now()->subDays(rand(1, 30)),
                    'graded_date' => Carbon::now()->subDays(rand(1, 15)),
                    'graded_by_staff_id' => 3, // Teacher user
                    'is_visible_to_student' => true,
                    'created_by' => 1,
                ]);
            }
        }

        $this->command->info('Created grade records');
    }
}
