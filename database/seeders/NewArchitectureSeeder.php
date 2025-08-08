<?php

namespace Database\Seeders;

use App\Models\Enrolment;
use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewArchitectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample programmes
        $baBusiness = Programme::firstOrCreate(
            ['title' => 'BA in Business Management'],
            [
                'awarding_body' => 'The Open College',
                'nfq_level' => 8,
                'total_credits' => 180,
                'description' => 'A comprehensive business management degree programme',
                'learning_outcomes' => 'Students will develop skills in leadership, strategy, and operations management.',
            ]
        );

        $diplomaMarketing = Programme::firstOrCreate(
            ['title' => 'Diploma in Digital Marketing'],
            [
                'awarding_body' => 'The Open College',
                'nfq_level' => 6,
                'total_credits' => 60,
                'description' => 'Professional diploma in digital marketing strategies',
                'learning_outcomes' => 'Students will master digital marketing tools and strategies.',
            ]
        );

        // Create programme instances
        $baBusinessSept2024 = ProgrammeInstance::firstOrCreate(
            [
                'programme_id' => $baBusiness->id,
                'label' => 'September 2024 Intake'
            ],
            [
                'intake_start_date' => Carbon::create(2024, 9, 1),
                'intake_end_date' => Carbon::create(2027, 6, 30),
                'default_delivery_style' => 'sync',
            ]
        );

        $diplomaMarketingJan2025 = ProgrammeInstance::firstOrCreate(
            [
                'programme_id' => $diplomaMarketing->id,
                'label' => 'January 2025 Rolling'
            ],
            [
                'intake_start_date' => Carbon::create(2025, 1, 1),
                'intake_end_date' => Carbon::create(2025, 12, 31),
                'default_delivery_style' => 'async',
            ]
        );

        // Create sample modules
        $businessStrategy = Module::firstOrCreate(
            ['module_code' => 'BUS401'],
            [
                'title' => 'Business Strategy',
                'credit_value' => 10,
                'assessment_strategy' => [
                    [
                        'component_name' => 'Strategic Analysis Essay',
                        'weighting' => 40,
                        'is_must_pass' => false,
                        'component_pass_mark' => null,
                    ],
                    [
                        'component_name' => 'Final Examination',
                        'weighting' => 60,
                        'is_must_pass' => true,
                        'component_pass_mark' => 40,
                    ],
                ],
                'allows_standalone_enrolment' => true,
                'async_instance_cadence' => 'quarterly',
            ]
        );

        $digitalMarketing = Module::firstOrCreate(
            ['module_code' => 'MKT101'],
            [
                'title' => 'Introduction to Digital Marketing',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Campaign Project',
                    'weighting' => 70,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Reflection Portfolio',
                    'weighting' => 30,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'monthly',
        ]);

        $employmentLaw = Module::firstOrCreate(
            ['module_code' => 'LAW201'],
            [
                'title' => 'Employment Law',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Case Study Analysis',
                    'weighting' => 100,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'bi_annually',
        ]);

        // Find or create tutor users
        $tutor1 = User::firstOrCreate([
            'email' => 'john.smith@theopencollege.com',
        ], [
            'name' => 'John Smith',
            'role' => 'teacher',
        ]);

        $tutor2 = User::firstOrCreate([
            'email' => 'sarah.jones@theopencollege.com',
        ], [
            'name' => 'Sarah Jones',
            'role' => 'teacher',
        ]);

        // Create module instances
        $businessStrategySept2024 = ModuleInstance::firstOrCreate([
            'module_id' => $businessStrategy->id,
            'start_date' => Carbon::create(2024, 9, 15),
        ], [
            'tutor_id' => $tutor1->id,
            'target_end_date' => Carbon::create(2024, 12, 15),
            'delivery_style' => 'sync',
        ]);

        $digitalMarketingJan2025 = ModuleInstance::firstOrCreate([
            'module_id' => $digitalMarketing->id,
            'start_date' => Carbon::create(2025, 1, 15),
        ], [
            'tutor_id' => $tutor2->id,
            'target_end_date' => Carbon::create(2025, 3, 15),
            'delivery_style' => 'async',
        ]);

        $employmentLawStandalone = ModuleInstance::firstOrCreate([
            'module_id' => $employmentLaw->id,
            'start_date' => Carbon::create(2024, 10, 1),
        ], [
            'tutor_id' => $tutor1->id,
            'target_end_date' => Carbon::create(2024, 11, 30),
            'delivery_style' => 'async',
        ]);

        // Link modules to programme instances (curriculum)
        $baBusinessSept2024->moduleInstances()->syncWithoutDetaching([$businessStrategySept2024->id]);
        $diplomaMarketingJan2025->moduleInstances()->syncWithoutDetaching([$digitalMarketingJan2025->id]);

        // Create sample students
        $student1 = Student::firstOrCreate(
            ['email' => 'emma.wilson@student.ie'],
            [
                'student_number' => Student::generateStudentNumber(),
                'first_name' => 'Emma',
                'last_name' => 'Wilson',
                'phone' => '0851234567',
                'address' => '123 Main Street',
                'city' => 'Dublin',
                'county' => 'Dublin',
                'eircode' => 'D01 X123',
                'date_of_birth' => Carbon::create(1995, 5, 15),
                'status' => 'active',
            ]
        );

        $student2 = Student::firstOrCreate(
            ['email' => 'michael.oconnor@student.ie'],
            [
                'student_number' => Student::generateStudentNumber(),
                'first_name' => 'Michael',
                'last_name' => 'O\'Connor',
                'phone' => '0867654321',
                'address' => '456 Oak Avenue',
                'city' => 'Cork',
                'county' => 'Cork',
                'eircode' => 'T12 Y456',
                'date_of_birth' => Carbon::create(1992, 8, 22),
                'status' => 'active',
            ]
        );

        // Create sample enrolments
        // Programme enrolment
        Enrolment::firstOrCreate([
            'student_id' => $student1->id,
            'enrolment_type' => 'programme',
            'programme_instance_id' => $baBusinessSept2024->id,
        ], [
            'module_instance_id' => null,
            'enrolment_date' => Carbon::create(2024, 8, 15),
            'status' => 'active',
        ]);

        // Standalone module enrolments
        Enrolment::firstOrCreate([
            'student_id' => $student2->id,
            'enrolment_type' => 'module',
            'module_instance_id' => $employmentLawStandalone->id,
        ], [
            'programme_instance_id' => null,
            'enrolment_date' => Carbon::create(2024, 9, 20),
            'status' => 'active',
        ]);

        Enrolment::firstOrCreate([
            'student_id' => $student1->id,
            'enrolment_type' => 'module',
            'module_instance_id' => $employmentLawStandalone->id,
        ], [
            'programme_instance_id' => null,
            'enrolment_date' => Carbon::create(2024, 9, 25),
            'status' => 'active',
        ]);

        $this->command->info('New architecture seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 2 Programmes (BA Business, Diploma Marketing)');
        $this->command->info('- 2 Programme Instances');
        $this->command->info('- 3 Modules (Business Strategy, Digital Marketing, Employment Law)');
        $this->command->info('- 3 Module Instances');
        $this->command->info('- 2 Students');
        $this->command->info('- 3 Enrolments (1 programme, 2 standalone modules)');
    }
}
