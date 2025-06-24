<?php

namespace Database\Seeders;

use App\Models\Enrolment;
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

class WorkflowTestingSeeder extends Seeder
{
    /**
     * Comprehensive test data for workflow testing scenarios
     * Creates realistic academic structure with various student progression patterns
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating comprehensive workflow testing data...');

        // Create test users for different roles
        $this->createTestUsers();

        // Create programme blueprints
        $programmes = $this->createProgrammes();

        // Create programme instances for multiple academic years
        $programmeInstances = $this->createProgrammeInstances($programmes);

        // Create module blueprints with varied assessment strategies
        $modules = $this->createModules();

        // Create module instances for curriculum delivery
        $moduleInstances = $this->createModuleInstances($modules);

        // Link modules to programme instances (curriculum)
        $this->createCurriculum($programmeInstances, $moduleInstances);

        // Create diverse student population
        $students = $this->createStudents();

        // Create enrolments with various patterns
        $this->createEnrolments($students, $programmeInstances, $moduleInstances);

        // Create grade records with realistic progression patterns
        $this->createGradeRecords($students, $moduleInstances);

        $this->command->info('✅ Workflow testing data created successfully!');
        $this->printSummary();
    }

    /**
     * Create test users for different roles
     */
    private function createTestUsers(): array
    {
        $this->command->info('👥 Creating test users...');

        $users = [];

        // Manager users
        $users['manager1'] = User::firstOrCreate([
            'email' => 'sarah.jones@theopencollege.com',
        ], [
            'name' => 'Sarah Jones',
            'role' => 'manager',
            'password' => Hash::make('password123'),
        ]);

        $users['manager2'] = User::firstOrCreate([
            'email' => 'david.brown@theopencollege.com',
        ], [
            'name' => 'David Brown',
            'role' => 'manager',
            'password' => Hash::make('password123'),
        ]);

        // Student Services staff
        $users['student_services1'] = User::firstOrCreate([
            'email' => 'mary.kelly@theopencollege.com',
        ], [
            'name' => 'Mary Kelly',
            'role' => 'student_services',
            'password' => Hash::make('password123'),
        ]);

        $users['student_services2'] = User::firstOrCreate([
            'email' => 'james.murphy@theopencollege.com',
        ], [
            'name' => 'James Murphy',
            'role' => 'student_services',
            'password' => Hash::make('password123'),
        ]);

        // Teachers/Tutors
        $users['teacher1'] = User::firstOrCreate([
            'email' => 'john.smith@theopencollege.com',
        ], [
            'name' => 'John Smith',
            'role' => 'teacher',
            'password' => Hash::make('password123'),
        ]);

        $users['teacher2'] = User::firstOrCreate([
            'email' => 'lisa.connor@theopencollege.com',
        ], [
            'name' => 'Lisa O\'Connor',
            'role' => 'teacher',
            'password' => Hash::make('password123'),
        ]);

        $users['teacher3'] = User::firstOrCreate([
            'email' => 'paul.davis@theopencollege.com',
        ], [
            'name' => 'Paul Davis',
            'role' => 'teacher',
            'password' => Hash::make('password123'),
        ]);

        $users['teacher4'] = User::firstOrCreate([
            'email' => 'emma.walsh@theopencollege.com',
        ], [
            'name' => 'Emma Walsh',
            'role' => 'teacher',
            'password' => Hash::make('password123'),
        ]);

        // Test student users
        $users['student1'] = User::firstOrCreate([
            'email' => 'emma.wilson@student.ie',
        ], [
            'name' => 'Emma Wilson',
            'role' => 'student',
            'password' => Hash::make('password123'),
        ]);

        $users['student2'] = User::firstOrCreate([
            'email' => 'michael.oconnor@student.ie',
        ], [
            'name' => 'Michael O\'Connor',
            'role' => 'student',
            'password' => Hash::make('password123'),
        ]);

        return $users;
    }

    /**
     * Create diverse programme blueprints
     */
    private function createProgrammes(): array
    {
        $this->command->info('🎓 Creating programme blueprints...');

        $programmes = [];

        $programmes['ba_business'] = Programme::create([
            'title' => 'BA in Business Management',
            'awarding_body' => 'The Open College',
            'nfq_level' => 8,
            'total_credits' => 180,
            'description' => 'A comprehensive undergraduate business management degree covering strategic management, finance, marketing, and operations.',
            'learning_outcomes' => 'Graduates will demonstrate advanced knowledge of business principles, strategic thinking, leadership capabilities, and ethical decision-making in complex business environments.',
        ]);

        $programmes['diploma_marketing'] = Programme::create([
            'title' => 'Diploma in Digital Marketing',
            'awarding_body' => 'The Open College',
            'nfq_level' => 6,
            'total_credits' => 60,
            'description' => 'Professional diploma focusing on digital marketing strategies, social media marketing, SEO, and online analytics.',
            'learning_outcomes' => 'Students will master digital marketing tools, develop comprehensive online marketing campaigns, and analyze digital marketing effectiveness.',
        ]);

        $programmes['msc_data'] = Programme::create([
            'title' => 'MSc in Data Analytics',
            'awarding_body' => 'The Open College',
            'nfq_level' => 9,
            'total_credits' => 90,
            'description' => 'Advanced postgraduate programme in data analytics, machine learning, and business intelligence.',
            'learning_outcomes' => 'Graduates will demonstrate expertise in data analysis, statistical modeling, machine learning algorithms, and data-driven decision making.',
        ]);

        $programmes['cert_project'] = Programme::create([
            'title' => 'Certificate in Project Management',
            'awarding_body' => 'The Open College',
            'nfq_level' => 6,
            'total_credits' => 30,
            'description' => 'Professional certificate in project management methodologies, tools, and best practices.',
            'learning_outcomes' => 'Students will apply project management principles, use project management software, and lead successful project teams.',
        ]);

        $programmes['diploma_hr'] = Programme::create([
            'title' => 'Diploma in Human Resource Management',
            'awarding_body' => 'The Open College',
            'nfq_level' => 7,
            'total_credits' => 60,
            'description' => 'Professional diploma covering HR strategy, employment law, recruitment, and performance management.',
            'learning_outcomes' => 'Graduates will demonstrate competency in HR practices, legal compliance, strategic HR planning, and employee relations.',
        ]);

        return $programmes;
    }

    /**
     * Create programme instances for multiple intake periods
     */
    private function createProgrammeInstances(array $programmes): array
    {
        $this->command->info('📅 Creating programme instances...');

        $instances = [];

        // BA Business Management instances
        $instances['ba_business_sept2024'] = ProgrammeInstance::create([
            'programme_id' => $programmes['ba_business']->id,
            'label' => 'September 2024 Intake',
            'intake_start_date' => Carbon::create(2024, 9, 1),
            'intake_end_date' => Carbon::create(2027, 6, 30),
            'default_delivery_style' => 'sync',
        ]);

        $instances['ba_business_jan2025'] = ProgrammeInstance::create([
            'programme_id' => $programmes['ba_business']->id,
            'label' => 'January 2025 Intake',
            'intake_start_date' => Carbon::create(2025, 1, 15),
            'intake_end_date' => Carbon::create(2028, 1, 15),
            'default_delivery_style' => 'sync',
        ]);

        $instances['ba_business_sept2025'] = ProgrammeInstance::create([
            'programme_id' => $programmes['ba_business']->id,
            'label' => 'September 2025 Intake',
            'intake_start_date' => Carbon::create(2025, 9, 1),
            'intake_end_date' => Carbon::create(2028, 6, 30),
            'default_delivery_style' => 'sync',
        ]);

        // Digital Marketing instances
        $instances['marketing_2024_rolling'] = ProgrammeInstance::create([
            'programme_id' => $programmes['diploma_marketing']->id,
            'label' => '2024 Rolling Enrolment',
            'intake_start_date' => Carbon::create(2024, 1, 1),
            'intake_end_date' => Carbon::create(2024, 12, 31),
            'default_delivery_style' => 'async',
        ]);

        $instances['marketing_2025_rolling'] = ProgrammeInstance::create([
            'programme_id' => $programmes['diploma_marketing']->id,
            'label' => '2025 Rolling Enrolment',
            'intake_start_date' => Carbon::create(2025, 1, 1),
            'intake_end_date' => Carbon::create(2025, 12, 31),
            'default_delivery_style' => 'async',
        ]);

        // MSc Data Analytics instances
        $instances['msc_data_sept2024'] = ProgrammeInstance::create([
            'programme_id' => $programmes['msc_data']->id,
            'label' => 'September 2024 Cohort',
            'intake_start_date' => Carbon::create(2024, 9, 15),
            'intake_end_date' => Carbon::create(2026, 6, 30),
            'default_delivery_style' => 'sync',
        ]);

        // Project Management instances
        $instances['cert_project_quarterly'] = ProgrammeInstance::create([
            'programme_id' => $programmes['cert_project']->id,
            'label' => 'Quarterly Intake 2024-2025',
            'intake_start_date' => Carbon::create(2024, 10, 1),
            'intake_end_date' => Carbon::create(2025, 9, 30),
            'default_delivery_style' => 'async',
        ]);

        // HR Management instances
        $instances['diploma_hr_feb2025'] = ProgrammeInstance::create([
            'programme_id' => $programmes['diploma_hr']->id,
            'label' => 'February 2025 Intake',
            'intake_start_date' => Carbon::create(2025, 2, 1),
            'intake_end_date' => Carbon::create(2026, 2, 1),
            'default_delivery_style' => 'sync',
        ]);

        return $instances;
    }

    /**
     * Create modules with diverse assessment strategies
     */
    private function createModules(): array
    {
        $this->command->info('📚 Creating module blueprints...');

        $modules = [];

        // Business modules
        $modules['business_strategy'] = Module::create([
            'title' => 'Business Strategy',
            'module_code' => 'BUS401',
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
        ]);

        $modules['marketing_fundamentals'] = Module::create([
            'title' => 'Marketing Fundamentals',
            'module_code' => 'MKT101',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Marketing Plan Project',
                    'weighting' => 70,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Case Study Analysis',
                    'weighting' => 30,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'monthly',
        ]);

        $modules['financial_management'] = Module::create([
            'title' => 'Financial Management',
            'module_code' => 'FIN201',
            'credit_value' => 10,
            'assessment_strategy' => [
                [
                    'component_name' => 'Financial Analysis Report',
                    'weighting' => 50,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Final Examination',
                    'weighting' => 50,
                    'is_must_pass' => true,
                    'component_pass_mark' => 40,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        $modules['operations_management'] = Module::create([
            'title' => 'Operations Management',
            'module_code' => 'OPS301',
            'credit_value' => 10,
            'assessment_strategy' => [
                [
                    'component_name' => 'Process Improvement Project',
                    'weighting' => 60,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Written Examination',
                    'weighting' => 40,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => false,
            'async_instance_cadence' => 'quarterly',
        ]);

        // Digital Marketing modules
        $modules['digital_marketing_intro'] = Module::create([
            'title' => 'Introduction to Digital Marketing',
            'module_code' => 'DIG101',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Digital Campaign Project',
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

        $modules['seo_analytics'] = Module::create([
            'title' => 'SEO and Web Analytics',
            'module_code' => 'DIG201',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'SEO Audit Report',
                    'weighting' => 50,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Analytics Dashboard Project',
                    'weighting' => 50,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'monthly',
        ]);

        $modules['social_media_marketing'] = Module::create([
            'title' => 'Social Media Marketing',
            'module_code' => 'DIG301',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Social Media Strategy',
                    'weighting' => 60,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Content Creation Portfolio',
                    'weighting' => 40,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'monthly',
        ]);

        // Data Analytics modules
        $modules['statistics_fundamentals'] = Module::create([
            'title' => 'Statistical Fundamentals',
            'module_code' => 'STA501',
            'credit_value' => 10,
            'assessment_strategy' => [
                [
                    'component_name' => 'Statistical Analysis Project',
                    'weighting' => 60,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Written Examination',
                    'weighting' => 40,
                    'is_must_pass' => true,
                    'component_pass_mark' => 40,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        $modules['machine_learning'] = Module::create([
            'title' => 'Machine Learning Applications',
            'module_code' => 'ML601',
            'credit_value' => 15,
            'assessment_strategy' => [
                [
                    'component_name' => 'ML Algorithm Implementation',
                    'weighting' => 40,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Data Science Project',
                    'weighting' => 40,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Viva Voce Examination',
                    'weighting' => 20,
                    'is_must_pass' => true,
                    'component_pass_mark' => 40,
                ],
            ],
            'allows_standalone_enrolment' => false,
            'async_instance_cadence' => 'quarterly',
        ]);

        // Project Management modules
        $modules['project_planning'] = Module::create([
            'title' => 'Project Planning and Control',
            'module_code' => 'PMP101',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Project Plan Development',
                    'weighting' => 100,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        $modules['risk_management'] = Module::create([
            'title' => 'Risk Management',
            'module_code' => 'PMP201',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Risk Assessment Report',
                    'weighting' => 70,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Risk Mitigation Plan',
                    'weighting' => 30,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        // HR Management modules
        $modules['employment_law'] = Module::create([
            'title' => 'Employment Law',
            'module_code' => 'LAW201',
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

        $modules['recruitment_selection'] = Module::create([
            'title' => 'Recruitment and Selection',
            'module_code' => 'HRM301',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Recruitment Strategy Design',
                    'weighting' => 60,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Interview Simulation',
                    'weighting' => 40,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        $modules['performance_management'] = Module::create([
            'title' => 'Performance Management',
            'module_code' => 'HRM401',
            'credit_value' => 5,
            'assessment_strategy' => [
                [
                    'component_name' => 'Performance System Design',
                    'weighting' => 50,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
                [
                    'component_name' => 'Performance Review Roleplay',
                    'weighting' => 50,
                    'is_must_pass' => false,
                    'component_pass_mark' => null,
                ],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        return $modules;
    }

    /**
     * Create module instances for curriculum delivery
     */
    private function createModuleInstances(array $modules): array
    {
        $this->command->info('🏫 Creating module instances...');

        $instances = [];
        $users = User::whereIn('role', ['teacher'])->get();

        // Get random tutors for assignment
        $tutor1 = $users->where('email', 'john.smith@theopencollege.com')->first();
        $tutor2 = $users->where('email', 'lisa.connor@theopencollege.com')->first();
        $tutor3 = $users->where('email', 'paul.davis@theopencollege.com')->first();
        $tutor4 = $users->where('email', 'emma.walsh@theopencollege.com')->first();

        // BA Business Management module instances
        $instances['business_strategy_sept2024'] = ModuleInstance::create([
            'module_id' => $modules['business_strategy']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2024, 9, 15),
            'target_end_date' => Carbon::create(2024, 12, 15),
            'delivery_style' => 'sync',
        ]);

        $instances['marketing_fundamentals_oct2024'] = ModuleInstance::create([
            'module_id' => $modules['marketing_fundamentals']->id,
            'tutor_id' => $tutor2->id,
            'start_date' => Carbon::create(2024, 10, 1),
            'target_end_date' => Carbon::create(2025, 1, 15),
            'delivery_style' => 'sync',
        ]);

        $instances['financial_management_jan2025'] = ModuleInstance::create([
            'module_id' => $modules['financial_management']->id,
            'tutor_id' => $tutor3->id,
            'start_date' => Carbon::create(2025, 1, 20),
            'target_end_date' => Carbon::create(2025, 4, 30),
            'delivery_style' => 'sync',
        ]);

        $instances['operations_management_feb2025'] = ModuleInstance::create([
            'module_id' => $modules['operations_management']->id,
            'tutor_id' => $tutor4->id,
            'start_date' => Carbon::create(2025, 2, 1),
            'target_end_date' => Carbon::create(2025, 5, 15),
            'delivery_style' => 'sync',
        ]);

        // January 2025 BA intake instances
        $instances['business_strategy_jan2025'] = ModuleInstance::create([
            'module_id' => $modules['business_strategy']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2025, 1, 20),
            'target_end_date' => Carbon::create(2025, 4, 20),
            'delivery_style' => 'sync',
        ]);

        $instances['marketing_fundamentals_feb2025'] = ModuleInstance::create([
            'module_id' => $modules['marketing_fundamentals']->id,
            'tutor_id' => $tutor2->id,
            'start_date' => Carbon::create(2025, 2, 15),
            'target_end_date' => Carbon::create(2025, 5, 30),
            'delivery_style' => 'sync',
        ]);

        // September 2025 BA intake instances
        $instances['business_strategy_sept2025'] = ModuleInstance::create([
            'module_id' => $modules['business_strategy']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2025, 9, 15),
            'target_end_date' => Carbon::create(2025, 12, 15),
            'delivery_style' => 'sync',
        ]);

        $instances['marketing_fundamentals_oct2025'] = ModuleInstance::create([
            'module_id' => $modules['marketing_fundamentals']->id,
            'tutor_id' => $tutor2->id,
            'start_date' => Carbon::create(2025, 10, 1),
            'target_end_date' => Carbon::create(2026, 1, 15),
            'delivery_style' => 'sync',
        ]);

        // Digital Marketing rolling instances
        $instances['digital_marketing_jan2024'] = ModuleInstance::create([
            'module_id' => $modules['digital_marketing_intro']->id,
            'tutor_id' => $tutor2->id,
            'start_date' => Carbon::create(2024, 1, 15),
            'target_end_date' => Carbon::create(2024, 3, 15),
            'delivery_style' => 'async',
        ]);

        $instances['seo_analytics_feb2024'] = ModuleInstance::create([
            'module_id' => $modules['seo_analytics']->id,
            'tutor_id' => $tutor3->id,
            'start_date' => Carbon::create(2024, 2, 1),
            'target_end_date' => Carbon::create(2024, 4, 1),
            'delivery_style' => 'async',
        ]);

        $instances['social_media_mar2024'] = ModuleInstance::create([
            'module_id' => $modules['social_media_marketing']->id,
            'tutor_id' => $tutor4->id,
            'start_date' => Carbon::create(2024, 3, 1),
            'target_end_date' => Carbon::create(2024, 5, 1),
            'delivery_style' => 'async',
        ]);

        // 2025 Digital Marketing instances
        $instances['digital_marketing_jan2025'] = ModuleInstance::create([
            'module_id' => $modules['digital_marketing_intro']->id,
            'tutor_id' => $tutor2->id,
            'start_date' => Carbon::create(2025, 1, 15),
            'target_end_date' => Carbon::create(2025, 3, 15),
            'delivery_style' => 'async',
        ]);

        $instances['seo_analytics_feb2025'] = ModuleInstance::create([
            'module_id' => $modules['seo_analytics']->id,
            'tutor_id' => $tutor3->id,
            'start_date' => Carbon::create(2025, 2, 1),
            'target_end_date' => Carbon::create(2025, 4, 1),
            'delivery_style' => 'async',
        ]);

        // MSc Data Analytics instances
        $instances['statistics_sept2024'] = ModuleInstance::create([
            'module_id' => $modules['statistics_fundamentals']->id,
            'tutor_id' => $tutor3->id,
            'start_date' => Carbon::create(2024, 9, 20),
            'target_end_date' => Carbon::create(2024, 12, 20),
            'delivery_style' => 'sync',
        ]);

        $instances['machine_learning_jan2025'] = ModuleInstance::create([
            'module_id' => $modules['machine_learning']->id,
            'tutor_id' => $tutor4->id,
            'start_date' => Carbon::create(2025, 1, 15),
            'target_end_date' => Carbon::create(2025, 5, 15),
            'delivery_style' => 'sync',
        ]);

        // Project Management instances
        $instances['project_planning_oct2024'] = ModuleInstance::create([
            'module_id' => $modules['project_planning']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2024, 10, 1),
            'target_end_date' => Carbon::create(2024, 11, 30),
            'delivery_style' => 'async',
        ]);

        $instances['risk_management_nov2024'] = ModuleInstance::create([
            'module_id' => $modules['risk_management']->id,
            'tutor_id' => $tutor2->id,
            'start_date' => Carbon::create(2024, 11, 1),
            'target_end_date' => Carbon::create(2024, 12, 31),
            'delivery_style' => 'async',
        ]);

        // HR Management instances
        $instances['employment_law_feb2025'] = ModuleInstance::create([
            'module_id' => $modules['employment_law']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2025, 2, 15),
            'target_end_date' => Carbon::create(2025, 4, 15),
            'delivery_style' => 'sync',
        ]);

        $instances['recruitment_selection_mar2025'] = ModuleInstance::create([
            'module_id' => $modules['recruitment_selection']->id,
            'tutor_id' => $tutor3->id,
            'start_date' => Carbon::create(2025, 3, 1),
            'target_end_date' => Carbon::create(2025, 5, 1),
            'delivery_style' => 'sync',
        ]);

        $instances['performance_management_apr2025'] = ModuleInstance::create([
            'module_id' => $modules['performance_management']->id,
            'tutor_id' => $tutor4->id,
            'start_date' => Carbon::create(2025, 4, 1),
            'target_end_date' => Carbon::create(2025, 6, 1),
            'delivery_style' => 'sync',
        ]);

        // Standalone module instances for CPD
        $instances['employment_law_standalone'] = ModuleInstance::create([
            'module_id' => $modules['employment_law']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2024, 10, 1),
            'target_end_date' => Carbon::create(2024, 11, 30),
            'delivery_style' => 'async',
        ]);

        $instances['business_strategy_standalone'] = ModuleInstance::create([
            'module_id' => $modules['business_strategy']->id,
            'tutor_id' => $tutor1->id,
            'start_date' => Carbon::create(2024, 11, 1),
            'target_end_date' => Carbon::create(2025, 1, 31),
            'delivery_style' => 'async',
        ]);

        return $instances;
    }

    /**
     * Create curriculum links between programme instances and module instances
     */
    private function createCurriculum(array $programmeInstances, array $moduleInstances): void
    {
        $this->command->info('🔗 Creating curriculum links...');

        // BA Business Management curriculum
        $programmeInstances['ba_business_sept2024']->moduleInstances()->attach([
            $moduleInstances['business_strategy_sept2024']->id,
            $moduleInstances['marketing_fundamentals_oct2024']->id,
            $moduleInstances['financial_management_jan2025']->id,
            $moduleInstances['operations_management_feb2025']->id,
        ]);

        $programmeInstances['ba_business_jan2025']->moduleInstances()->attach([
            $moduleInstances['business_strategy_jan2025']->id,
            $moduleInstances['marketing_fundamentals_feb2025']->id,
        ]);

        $programmeInstances['ba_business_sept2025']->moduleInstances()->attach([
            $moduleInstances['business_strategy_sept2025']->id,
            $moduleInstances['marketing_fundamentals_oct2025']->id,
        ]);

        // Digital Marketing curriculum
        $programmeInstances['marketing_2024_rolling']->moduleInstances()->attach([
            $moduleInstances['digital_marketing_jan2024']->id,
            $moduleInstances['seo_analytics_feb2024']->id,
            $moduleInstances['social_media_mar2024']->id,
        ]);

        $programmeInstances['marketing_2025_rolling']->moduleInstances()->attach([
            $moduleInstances['digital_marketing_jan2025']->id,
            $moduleInstances['seo_analytics_feb2025']->id,
        ]);

        // MSc Data Analytics curriculum
        $programmeInstances['msc_data_sept2024']->moduleInstances()->attach([
            $moduleInstances['statistics_sept2024']->id,
            $moduleInstances['machine_learning_jan2025']->id,
        ]);

        // Project Management curriculum
        $programmeInstances['cert_project_quarterly']->moduleInstances()->attach([
            $moduleInstances['project_planning_oct2024']->id,
            $moduleInstances['risk_management_nov2024']->id,
        ]);

        // HR Management curriculum
        $programmeInstances['diploma_hr_feb2025']->moduleInstances()->attach([
            $moduleInstances['employment_law_feb2025']->id,
            $moduleInstances['recruitment_selection_mar2025']->id,
            $moduleInstances['performance_management_apr2025']->id,
        ]);
    }

    /**
     * Create diverse student population with various characteristics
     */
    private function createStudents(): array
    {
        $this->command->info('👨‍🎓 Creating student population...');

        $students = [];

        // High-achieving students
        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Emma',
            'last_name' => 'Wilson',
            'email' => 'emma.wilson@student.ie',
            'phone' => '0851234567',
            'address' => '123 Main Street',
            'city' => 'Dublin',
            'county' => 'Dublin',
            'eircode' => 'D01 X123',
            'date_of_birth' => Carbon::create(1995, 5, 15),
            'status' => 'active',
        ]);

        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'James',
            'last_name' => 'O\'Sullivan',
            'email' => 'james.osullivan@student.ie',
            'phone' => '0862345678',
            'address' => '456 College Green',
            'city' => 'Cork',
            'county' => 'Cork',
            'eircode' => 'T12 Y456',
            'date_of_birth' => Carbon::create(1993, 8, 22),
            'status' => 'active',
        ]);

        // Average performing students
        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Sarah',
            'last_name' => 'Murphy',
            'email' => 'sarah.murphy@student.ie',
            'phone' => '0873456789',
            'address' => '789 Oak Avenue',
            'city' => 'Galway',
            'county' => 'Galway',
            'eircode' => 'H91 Z789',
            'date_of_birth' => Carbon::create(1997, 3, 10),
            'status' => 'active',
        ]);

        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Michael',
            'last_name' => 'O\'Connor',
            'email' => 'michael.oconnor@student.ie',
            'phone' => '0884567890',
            'address' => '321 Elm Street',
            'city' => 'Limerick',
            'county' => 'Limerick',
            'eircode' => 'V94 A321',
            'date_of_birth' => Carbon::create(1992, 11, 5),
            'status' => 'active',
        ]);

        // Struggling students
        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Lisa',
            'last_name' => 'Ryan',
            'email' => 'lisa.ryan@student.ie',
            'phone' => '0895678901',
            'address' => '654 Pine Road',
            'city' => 'Waterford',
            'county' => 'Waterford',
            'eircode' => 'X91 B654',
            'date_of_birth' => Carbon::create(1994, 7, 18),
            'status' => 'active',
        ]);

        // Part-time professional students
        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'David',
            'last_name' => 'Kelly',
            'email' => 'david.kelly@student.ie',
            'phone' => '0856789012',
            'address' => '987 Maple Lane',
            'city' => 'Kilkenny',
            'county' => 'Kilkenny',
            'eircode' => 'R95 C987',
            'date_of_birth' => Carbon::create(1985, 12, 3),
            'status' => 'active',
        ]);

        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Rachel',
            'last_name' => 'Walsh',
            'email' => 'rachel.walsh@student.ie',
            'phone' => '0867890123',
            'address' => '147 Cedar Close',
            'city' => 'Sligo',
            'county' => 'Sligo',
            'eircode' => 'F91 D147',
            'date_of_birth' => Carbon::create(1988, 4, 25),
            'status' => 'active',
        ]);

        // International students
        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Ahmed',
            'last_name' => 'Hassan',
            'email' => 'ahmed.hassan@student.ie',
            'phone' => '0878901234',
            'address' => '258 International Court',
            'city' => 'Dublin',
            'county' => 'Dublin',
            'eircode' => 'D02 E258',
            'date_of_birth' => Carbon::create(1996, 9, 14),
            'status' => 'active',
        ]);

        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Maria',
            'last_name' => 'Rodriguez',
            'email' => 'maria.rodriguez@student.ie',
            'phone' => '0889012345',
            'address' => '369 Global Heights',
            'city' => 'Cork',
            'county' => 'Cork',
            'eircode' => 'T23 F369',
            'date_of_birth' => Carbon::create(1995, 1, 30),
            'status' => 'active',
        ]);

        // Mature students
        $students[] = Student::create([
            'student_number' => Student::generateStudentNumber(),
            'first_name' => 'Patricia',
            'last_name' => 'Byrne',
            'email' => 'patricia.byrne@student.ie',
            'phone' => '0850123456',
            'address' => '741 Mature Way',
            'city' => 'Athlone',
            'county' => 'Westmeath',
            'eircode' => 'N37 G741',
            'date_of_birth' => Carbon::create(1975, 6, 12),
            'status' => 'active',
        ]);

        // Create additional students for bulk testing
        $firstNames = ['John', 'Mary', 'Sean', 'Anna', 'Patrick', 'Claire', 'Kevin', 'Emma', 'Daniel', 'Sophie', 'Mark', 'Laura', 'Paul', 'Amy', 'Robert'];
        $lastNames = ['Smith', 'Murphy', 'Kelly', 'O\'Sullivan', 'Walsh', 'Ryan', 'Byrne', 'Connor', 'McCarthy', 'Fitzgerald', 'Doyle', 'Gallagher', 'Doherty', 'Kennedy', 'Lynch'];
        $cities = ['Dublin', 'Cork', 'Galway', 'Limerick', 'Waterford', 'Kilkenny', 'Sligo', 'Athlone', 'Tralee', 'Wexford'];
        $statuses = ['enquiry', 'enrolled', 'active', 'active', 'active']; // Weight towards active

        for ($i = 0; $i < 40; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $city = $cities[array_rand($cities)];
            $status = $statuses[array_rand($statuses)];

            $students[] = Student::create([
                'student_number' => Student::generateStudentNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName.'.'.str_replace("'", '', $lastName).($i + 100).'@student.ie'),
                'phone' => '085'.str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'address' => rand(1, 999).' '.['Main', 'High', 'Church', 'Bridge', 'Castle'][array_rand(['Main', 'High', 'Church', 'Bridge', 'Castle'])].' Street',
                'city' => $city,
                'county' => $city,
                'eircode' => chr(rand(65, 90)).rand(10, 99).' '.chr(rand(65, 90)).rand(100, 999),
                'date_of_birth' => Carbon::create(rand(1980, 2000), rand(1, 12), rand(1, 28)),
                'status' => $status,
            ]);
        }

        return $students;
    }

    /**
     * Create diverse enrolment patterns
     */
    private function createEnrolments(array $students, array $programmeInstances, array $moduleInstances): void
    {
        $this->command->info('📝 Creating enrolments...');

        $enrolmentService = app(EnrolmentService::class);

        // Programme enrolments

        // BA Business Management September 2024 - High achievers
        $enrolmentService->enrolStudentInProgramme($students[0], $programmeInstances['ba_business_sept2024'], [
            'enrolment_date' => Carbon::create(2024, 8, 15),
        ]);

        $enrolmentService->enrolStudentInProgramme($students[1], $programmeInstances['ba_business_sept2024'], [
            'enrolment_date' => Carbon::create(2024, 8, 20),
        ]);

        // BA Business Management January 2025 - Average students
        $enrolmentService->enrolStudentInProgramme($students[2], $programmeInstances['ba_business_jan2025'], [
            'enrolment_date' => Carbon::create(2025, 1, 10),
        ]);

        $enrolmentService->enrolStudentInProgramme($students[3], $programmeInstances['ba_business_jan2025'], [
            'enrolment_date' => Carbon::create(2025, 1, 12),
        ]);

        // Digital Marketing rolling enrolments
        $enrolmentService->enrolStudentInProgramme($students[4], $programmeInstances['marketing_2024_rolling'], [
            'enrolment_date' => Carbon::create(2024, 1, 5),
        ]);

        $enrolmentService->enrolStudentInProgramme($students[5], $programmeInstances['marketing_2025_rolling'], [
            'enrolment_date' => Carbon::create(2025, 1, 8),
        ]);

        // MSc Data Analytics - International students
        $enrolmentService->enrolStudentInProgramme($students[7], $programmeInstances['msc_data_sept2024'], [
            'enrolment_date' => Carbon::create(2024, 9, 1),
        ]);

        $enrolmentService->enrolStudentInProgramme($students[8], $programmeInstances['msc_data_sept2024'], [
            'enrolment_date' => Carbon::create(2024, 9, 3),
        ]);

        // Project Management - Professional students
        $enrolmentService->enrolStudentInProgramme($students[6], $programmeInstances['cert_project_quarterly'], [
            'enrolment_date' => Carbon::create(2024, 9, 15),
        ]);

        // HR Management - Mature student
        $enrolmentService->enrolStudentInProgramme($students[9], $programmeInstances['diploma_hr_feb2025'], [
            'enrolment_date' => Carbon::create(2025, 1, 25),
        ]);

        // Standalone module enrolments (CPD students)
        $enrolmentService->enrolStudentInModule($students[6], $moduleInstances['employment_law_standalone'], [
            'enrolment_date' => Carbon::create(2024, 9, 20),
        ]);

        $enrolmentService->enrolStudentInModule($students[0], $moduleInstances['employment_law_standalone'], [
            'enrolment_date' => Carbon::create(2024, 9, 25),
        ]);

        $enrolmentService->enrolStudentInModule($students[9], $moduleInstances['business_strategy_standalone'], [
            'enrolment_date' => Carbon::create(2024, 10, 15),
        ]);

        // Bulk enrolments for remaining students
        $remainingStudents = array_slice($students, 10);
        $programmeList = array_values($programmeInstances);

        foreach ($remainingStudents as $index => $student) {
            if ($student->status === 'active') {
                $programmeInstance = $programmeList[$index % count($programmeList)];
                try {
                    $enrolmentService->enrolStudentInProgramme($student, $programmeInstance, [
                        'enrolment_date' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                    ]);
                } catch (\Exception $e) {
                    // Skip if programme instance not suitable (e.g., past intake date)
                    continue;
                }
            }
        }
    }

    /**
     * Create realistic grade records with various progression patterns
     */
    private function createGradeRecords(array $students, array $moduleInstances): void
    {
        $this->command->info('📊 Creating grade records...');

        $tutors = User::where('role', 'teacher')->get();

        // High achiever grades (Emma Wilson - Student 0)
        $this->createStudentGrades($students[0], 'high_achiever', $tutors);

        // High achiever grades (James O'Sullivan - Student 1)
        $this->createStudentGrades($students[1], 'high_achiever', $tutors);

        // Average student grades (Sarah Murphy - Student 2)
        $this->createStudentGrades($students[2], 'average', $tutors);

        // Average student grades (Michael O'Connor - Student 3)
        $this->createStudentGrades($students[3], 'average', $tutors);

        // Struggling student grades (Lisa Ryan - Student 4)
        $this->createStudentGrades($students[4], 'struggling', $tutors);

        // Professional student grades (David Kelly - Student 5)
        $this->createStudentGrades($students[5], 'professional', $tutors);

        // Create grades for other students with mixed patterns
        $remainingStudents = array_slice($students, 6);
        $patterns = ['high_achiever', 'average', 'average', 'struggling', 'professional'];

        foreach ($remainingStudents as $index => $student) {
            if ($student->status === 'active') {
                $pattern = $patterns[$index % count($patterns)];
                $this->createStudentGrades($student, $pattern, $tutors);
            }
        }
    }

    /**
     * Create grade records for a student based on their performance pattern
     */
    private function createStudentGrades(Student $student, string $pattern, $tutors): void
    {
        $gradeRecords = StudentGradeRecord::where('student_id', $student->id)->get();

        foreach ($gradeRecords as $record) {
            $grade = $this->generateGradeByPattern($pattern, $record->assessment_component_name);
            $tutor = $tutors->random();

            // Determine if this assessment should be graded (some may be pending)
            $shouldGrade = rand(1, 100) <= 80; // 80% chance of being graded

            if ($shouldGrade) {
                $record->update([
                    'grade' => $grade,
                    'max_grade' => 100,
                    'feedback' => $this->generateFeedback($pattern, $grade),
                    'submission_date' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                    'graded_date' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                    'graded_by_staff_id' => $tutor->id,
                    'is_visible_to_student' => rand(1, 100) <= 70, // 70% visible
                    'release_date' => rand(1, 100) <= 50 ? Carbon::create(2024, rand(1, 12), rand(1, 28)) : null,
                ]);
            }
        }
    }

    /**
     * Generate grade based on student performance pattern
     */
    private function generateGradeByPattern(string $pattern, string $assessmentType): float
    {
        switch ($pattern) {
            case 'high_achiever':
                return rand(75, 95) + (rand(0, 100) / 100); // 75-95%

            case 'average':
                return rand(55, 75) + (rand(0, 100) / 100); // 55-75%

            case 'struggling':
                return rand(30, 55) + (rand(0, 100) / 100); // 30-55%

            case 'professional':
                // Professionals tend to do better on practical assessments
                if (str_contains(strtolower($assessmentType), 'project') ||
                    str_contains(strtolower($assessmentType), 'report')) {
                    return rand(70, 85) + (rand(0, 100) / 100); // 70-85%
                } else {
                    return rand(60, 75) + (rand(0, 100) / 100); // 60-75%
                }

            default:
                return rand(40, 80) + (rand(0, 100) / 100); // 40-80%
        }
    }

    /**
     * Generate realistic feedback based on performance pattern
     */
    private function generateFeedback(string $pattern, float $grade): string
    {
        $feedbackTemplates = [
            'high_achiever' => [
                'Excellent work demonstrating deep understanding of the subject matter.',
                'Outstanding analysis with clear evidence of critical thinking.',
                'Exceptional quality throughout with innovative approaches.',
                'Comprehensive coverage of all key areas with excellent synthesis.',
            ],
            'average' => [
                'Good work that meets the learning objectives adequately.',
                'Solid understanding demonstrated with room for deeper analysis.',
                'Satisfactory completion of all requirements with some good insights.',
                'Generally well-structured with appropriate use of sources.',
            ],
            'struggling' => [
                'Demonstrates basic understanding but requires more development.',
                'Some good points made but analysis could be deeper.',
                'Assignment completed but key areas need strengthening.',
                'Shows effort but would benefit from additional support.',
            ],
            'professional' => [
                'Excellent practical application drawing on professional experience.',
                'Strong real-world examples enhance the theoretical framework.',
                'Good integration of theory with workplace practice.',
                'Professional insights add significant value to the analysis.',
            ],
        ];

        $templates = $feedbackTemplates[$pattern] ?? $feedbackTemplates['average'];
        $baseFeedback = $templates[array_rand($templates)];

        // Add grade-specific comments
        if ($grade >= 80) {
            $baseFeedback .= ' This work demonstrates excellence and exceeds expectations.';
        } elseif ($grade >= 70) {
            $baseFeedback .= ' This represents very good achievement of the learning outcomes.';
        } elseif ($grade >= 60) {
            $baseFeedback .= ' This work satisfactorily meets the assessment criteria.';
        } elseif ($grade >= 50) {
            $baseFeedback .= ' While this work passes, there are areas for improvement.';
        } else {
            $baseFeedback .= ' This work does not meet the required standard and will need to be repeated.';
        }

        return $baseFeedback;
    }

    /**
     * Print summary of created test data
     */
    private function printSummary(): void
    {
        $this->command->info('📋 WORKFLOW TESTING DATA SUMMARY');
        $this->command->info('═══════════════════════════════════');

        $programmeCount = Programme::count();
        $programmeInstanceCount = ProgrammeInstance::count();
        $moduleCount = Module::count();
        $moduleInstanceCount = ModuleInstance::count();
        $studentCount = Student::count();
        $enrolmentCount = Enrolment::count();
        $gradeRecordCount = StudentGradeRecord::count();
        $userCount = User::count();

        $this->command->info("👥 Users: {$userCount}");
        $this->command->info("🎓 Programmes: {$programmeCount}");
        $this->command->info("📅 Programme Instances: {$programmeInstanceCount}");
        $this->command->info("📚 Modules: {$moduleCount}");
        $this->command->info("🏫 Module Instances: {$moduleInstanceCount}");
        $this->command->info("👨‍🎓 Students: {$studentCount}");
        $this->command->info("📝 Enrolments: {$enrolmentCount}");
        $this->command->info("📊 Grade Records: {$gradeRecordCount}");

        $this->command->info('');
        $this->command->info('🎯 READY FOR WORKFLOW TESTING');
        $this->command->info('Use the scenarios in WORKFLOW_TESTING_SCENARIOS.md');
        $this->command->info('to test the complete TOC-SIS functionality.');
    }
}
