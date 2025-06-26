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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimpleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating essential data quickly...');

        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@theopencollege.ie',
            'role' => 'manager',
            'azure_id' => 'admin-'.Str::random(10),
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create a few teachers
        $teachers = [];
        for ($i = 1; $i <= 5; $i++) {
            $teachers[] = User::create([
                'name' => "Teacher {$i}",
                'email' => "teacher{$i}@theopencollege.ie",
                'role' => 'teacher',
                'azure_id' => "teacher{$i}-".Str::random(8),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
        }

        // Create programmes
        $programme1 = Programme::create([
            'title' => 'Bachelor of Arts in Business Management',
            'awarding_body' => 'The Open College',
            'nfq_level' => 7,
            'total_credits' => 180,
            'description' => 'A comprehensive business management programme.',
            'learning_outcomes' => [
                'Demonstrate comprehensive knowledge of business management principles',
                'Apply analytical and problem-solving skills to business scenarios',
                'Communicate effectively in professional environments',
            ],
        ]);

        $programme2 = Programme::create([
            'title' => 'Certificate in Digital Skills',
            'awarding_body' => 'The Open College',
            'nfq_level' => 5,
            'total_credits' => 30,
            'description' => 'Essential digital skills programme.',
            'learning_outcomes' => [
                'Use computers and mobile devices confidently',
                'Navigate internet resources safely',
                'Create documents using productivity software',
            ],
        ]);

        // Create programme instances
        $instance1 = ProgrammeInstance::create([
            'programme_id' => $programme1->id,
            'label' => 'September 2024 Intake',
            'intake_start_date' => Carbon::parse('2024-09-01'),
            'intake_end_date' => Carbon::parse('2024-09-30'),
            'default_delivery_style' => 'sync',
        ]);

        $instance2 = ProgrammeInstance::create([
            'programme_id' => $programme2->id,
            'label' => 'Monthly Intake 2024',
            'intake_start_date' => Carbon::parse('2024-01-01'),
            'intake_end_date' => Carbon::parse('2024-12-31'),
            'default_delivery_style' => 'async',
        ]);

        // Create modules
        $module1 = Module::create([
            'title' => 'Strategic Management',
            'module_code' => 'STRAT001',
            'credit_value' => 15,
            'assessment_strategy' => [
                [
                    'component_name' => 'Strategic Analysis Report',
                    'weighting' => 40,
                    'is_must_pass' => false,
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
            'default_pass_mark' => 40,
        ]);

        $module2 = Module::create([
            'title' => 'Digital Literacy Fundamentals',
            'module_code' => 'DIG001',
            'credit_value' => 10,
            'assessment_strategy' => [
                [
                    'component_name' => 'Practical Skills Assessment',
                    'weighting' => 70,
                    'is_must_pass' => true,
                    'component_pass_mark' => 40,
                ],
                [
                    'component_name' => 'Digital Portfolio',
                    'weighting' => 30,
                    'is_must_pass' => false,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'monthly',
            'default_pass_mark' => 40,
        ]);

        // Create module instances
        $moduleInstance1 = ModuleInstance::create([
            'module_id' => $module1->id,
            'tutor_id' => $teachers[0]->id,
            'start_date' => Carbon::parse('2024-09-15'),
            'target_end_date' => Carbon::parse('2024-12-15'),
            'delivery_style' => 'sync',
        ]);

        $moduleInstance2 = ModuleInstance::create([
            'module_id' => $module2->id,
            'tutor_id' => $teachers[1]->id,
            'start_date' => Carbon::parse('2024-02-01'),
            'target_end_date' => Carbon::parse('2024-04-30'),
            'delivery_style' => 'async',
        ]);

        // Link module instances to programme instances (curriculum)
        $instance1->moduleInstances()->attach($moduleInstance1->id);
        $instance2->moduleInstances()->attach($moduleInstance2->id);

        // Create students
        for ($i = 1; $i <= 50; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $email = strtolower($firstName.'.'.$lastName.$i.'@student.ie');

            $user = User::create([
                'name' => $firstName.' '.$lastName,
                'email' => $email,
                'role' => 'student',
                'azure_id' => 'student-'.$i.'-'.Str::random(8),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);

            $student = Student::create([
                'student_number' => Student::generateStudentNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'county' => fake()->randomElement(['Dublin', 'Cork', 'Galway', 'Limerick', 'Waterford']),
                'eircode' => fake()->postcode(),
                'status' => 'active',
            ]);

            $user->update(['student_id' => $student->id]);

            // Create enrolments
            if ($i <= 30) {
                // Programme enrolment
                Enrolment::create([
                    'student_id' => $student->id,
                    'enrolment_type' => 'programme',
                    'programme_instance_id' => $instance1->id,
                    'module_instance_id' => null,
                    'status' => 'active',
                    'enrolment_date' => Carbon::parse('2024-09-01'),
                ]);
            } else {
                // Standalone module enrolment
                Enrolment::create([
                    'student_id' => $student->id,
                    'enrolment_type' => 'module',
                    'programme_instance_id' => null,
                    'module_instance_id' => $moduleInstance2->id,
                    'status' => 'active',
                    'enrolment_date' => Carbon::parse('2024-02-01'),
                ]);
            }
        }

        $this->command->info('✅ ESSENTIAL DATA CREATED SUCCESSFULLY!');
        $this->command->info('📊 Created:');
        $this->command->info('  • 1 Admin user');
        $this->command->info('  • 5 Teacher users');
        $this->command->info('  • 2 Programmes with instances');
        $this->command->info('  • 2 Modules with instances');
        $this->command->info('  • 50 Students with enrolments');
        $this->command->info('💡 Login: admin@theopencollege.ie / password123');
    }
}
