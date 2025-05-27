<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Programme;
use App\Models\Cohort;
use App\Models\Module;
use App\Models\Student;
use App\Models\Enrolment;
use App\Models\ModuleInstance;
use App\Models\StudentModuleEnrolment;
use App\Models\AssessmentComponent;
use App\Models\Deferral;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Users with different roles
        $this->createUsers();
        
        // Create Programmes as defined in the design document
        $this->createProgrammes();
        
        // Create Modules for each programme
        $this->createModules();
        
        // Create Cohorts for cohort-based programmes
        $this->createCohorts();
        
        // Create Module Instances
        $this->createModuleInstances();
        
        // Create Assessment Components
        $this->createAssessmentComponents();
        
        // Create Students with realistic data
        $this->createStudents();
        
        // Create Enrolments and complex scenarios
        $this->createEnrolments();
        
        // Create some deferrals to test the workflow
        $this->createDeferrals();
    }
    
    private function createUsers(): void
    {
        // Curriculum Manager
        User::create([
            'name' => 'Sarah Manager',
            'email' => 'manager@theopencollege.ie',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'azure_id' => 'manager-azure-id',
            'azure_groups' => ['managers-group-id'],
            'email_verified_at' => now(),
        ]);
        
        // Student Services Staff
        User::create([
            'name' => 'John StudentServices',
            'email' => 'studentservices@theopencollege.ie',
            'password' => Hash::make('password'),
            'role' => 'student_services',
            'azure_id' => 'ss-azure-id',
            'azure_groups' => ['student-services-group-id'],
            'email_verified_at' => now(),
        ]);
        
        // Teachers
        $teachers = [
            ['name' => 'Dr. Emily Watson', 'email' => 'emily.watson@theopencollege.ie'],
            ['name' => 'Michael O\'Connor', 'email' => 'michael.oconnor@theopencollege.ie'],
            ['name' => 'Lisa Murphy', 'email' => 'lisa.murphy@theopencollege.ie'],
            ['name' => 'David Kelly', 'email' => 'david.kelly@theopencollege.ie'],
            ['name' => 'Rachel Flynn', 'email' => 'rachel.flynn@theopencollege.ie'],
        ];
        
        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'azure_id' => 'teacher-' . str_replace([' ', '.', '\''], '', strtolower($teacher['name'])),
                'azure_groups' => ['teachers-group-id'],
                'email_verified_at' => now(),
            ]);
        }
        
        // Sample Student Users (for testing self-service)
        User::create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'azure_id' => 'student-azure-id',
            'azure_groups' => ['students-group-id'],
            'email_verified_at' => now(),
        ]);
    }
    
    private function createProgrammes(): void
    {
        // Early Learning & Care Level 5 (Priority programme from design doc)
        Programme::create([
            'code' => 'ELC5',
            'title' => 'Early Learning & Care Level 5',
            'description' => 'QQI Level 5 qualification in Early Learning and Care. Includes 12-month placement module running parallel to sequential modules.',
            'enrolment_type' => 'cohort',
            'settings' => [
                'intake_months' => [1, 4, 9], // January, April, September
                'duration_months' => 12,
                'placement_required' => true,
                'sequential_modules' => true,
            ],
            'is_active' => true,
        ]);
        
        // Early Learning & Care Level 6
        Programme::create([
            'code' => 'ELC6',
            'title' => 'Early Learning & Care Level 6',
            'description' => 'QQI Level 6 qualification in Early Learning and Care. Advanced programme building on Level 5.',
            'enrolment_type' => 'cohort',
            'settings' => [
                'intake_months' => [1, 4, 9],
                'duration_months' => 12,
                'placement_required' => true,
                'sequential_modules' => true,
            ],
            'is_active' => true,
        ]);
        
        // Health and Social Care (Degree programme)
        Programme::create([
            'code' => 'HSC',
            'title' => 'Health and Social Care (Oxford Brookes)',
            'description' => '3-year degree programme in Health and Social Care, certified by Oxford Brookes University. Institution\'s only degree programme.',
            'enrolment_type' => 'academic_term',
            'settings' => [
                'duration_years' => 3,
                'partner_institution' => 'Oxford Brookes University',
                'academic_calendar' => true,
            ],
            'is_active' => true,
        ]);
        
        // Rolling Enrolment Programme Examples
        Programme::create([
            'code' => 'QQI5-GEN',
            'title' => 'QQI Level 5 General Studies',
            'description' => 'Individual QQI Level 5 modules available for rolling enrolment throughout the year.',
            'enrolment_type' => 'rolling',
            'settings' => [
                'module_duration_months' => 3,
                'flexible_start' => true,
                'asynchronous_delivery' => true,
            ],
            'is_active' => true,
        ]);
        
        Programme::create([
            'code' => 'QQI6-SPEC',
            'title' => 'QQI Level 6 Specialist Modules',
            'description' => 'Specialist QQI Level 6 modules for continuous professional development.',
            'enrolment_type' => 'rolling',
            'settings' => [
                'module_duration_months' => 3,
                'flexible_start' => true,
                'asynchronous_delivery' => true,
            ],
            'is_active' => true,
        ]);
    }
    
    private function createModules(): void
    {
        // ELC5 Modules (as per design document structure)
        $elc5 = Programme::where('code', 'ELC5')->first();
        
        $elc5Modules = [
            ['code' => 'ELC501', 'title' => 'Child Development', 'credits' => 15, 'sequence' => 1],
            ['code' => 'ELC502', 'title' => 'Play and Creative Development', 'credits' => 15, 'sequence' => 2],
            ['code' => 'ELC503', 'title' => 'Health, Safety and Welfare', 'credits' => 15, 'sequence' => 3],
            ['code' => 'ELC504', 'title' => 'Work Practice Placement', 'credits' => 15, 'sequence' => 4, 'parallel' => true],
        ];
        
        foreach ($elc5Modules as $moduleData) {
            $module = Module::create([
                'code' => $moduleData['code'],
                'title' => $moduleData['title'],
                'description' => 'QQI Level 5 module: ' . $moduleData['title'],
                'credits' => $moduleData['credits'],
                'hours' => 150,
                'is_active' => true,
            ]);
            
            $module->programmes()->attach($elc5->id, [
                'sequence' => $moduleData['sequence'],
                'is_mandatory' => true,
            ]);
        }
        
        // ELC6 Modules
        $elc6 = Programme::where('code', 'ELC6')->first();
        
        $elc6Modules = [
            ['code' => 'ELC601', 'title' => 'Advanced Child Development', 'credits' => 15, 'sequence' => 1],
            ['code' => 'ELC602', 'title' => 'Leadership in Early Years', 'credits' => 15, 'sequence' => 2],
            ['code' => 'ELC603', 'title' => 'Special Educational Needs', 'credits' => 15, 'sequence' => 3],
            ['code' => 'ELC604', 'title' => 'Advanced Work Practice', 'credits' => 15, 'sequence' => 4],
        ];
        
        foreach ($elc6Modules as $moduleData) {
            $module = Module::create([
                'code' => $moduleData['code'],
                'title' => $moduleData['title'],
                'description' => 'QQI Level 6 module: ' . $moduleData['title'],
                'credits' => $moduleData['credits'],
                'hours' => 150,
                'is_active' => true,
            ]);
            
            $module->programmes()->attach($elc6->id, [
                'sequence' => $moduleData['sequence'],
                'is_mandatory' => true,
            ]);
        }
        
        // HSC Modules (simplified for testing)
        $hsc = Programme::where('code', 'HSC')->first();
        
        $hscModules = [
            ['code' => 'HSC101', 'title' => 'Introduction to Health and Social Care', 'credits' => 20],
            ['code' => 'HSC102', 'title' => 'Human Anatomy and Physiology', 'credits' => 20],
            ['code' => 'HSC103', 'title' => 'Psychology for Health Professionals', 'credits' => 20],
        ];
        
        foreach ($hscModules as $index => $moduleData) {
            $module = Module::create([
                'code' => $moduleData['code'],
                'title' => $moduleData['title'],
                'description' => 'Degree level module: ' . $moduleData['title'],
                'credits' => $moduleData['credits'],
                'hours' => 200,
                'is_active' => true,
            ]);
            
            $module->programmes()->attach($hsc->id, [
                'sequence' => $index + 1,
                'is_mandatory' => true,
            ]);
        }
        
        // Rolling Programme Modules
        $rolling5 = Programme::where('code', 'QQI5-GEN')->first();
        $rolling6 = Programme::where('code', 'QQI6-SPEC')->first();
        
        $rollingModules = [
            ['code' => 'COM101', 'title' => 'Communications', 'level' => 5, 'programme' => $rolling5],
            ['code' => 'WOR101', 'title' => 'Work Experience', 'level' => 5, 'programme' => $rolling5],
            ['code' => 'PER101', 'title' => 'Personal Effectiveness', 'level' => 5, 'programme' => $rolling5],
            ['code' => 'MGT201', 'title' => 'Management Practice', 'level' => 6, 'programme' => $rolling6],
            ['code' => 'TRA201', 'title' => 'Training and Development', 'level' => 6, 'programme' => $rolling6],
        ];
        
        foreach ($rollingModules as $index => $moduleData) {
            $module = Module::create([
                'code' => $moduleData['code'],
                'title' => $moduleData['title'],
                'description' => 'QQI Level ' . $moduleData['level'] . ' module: ' . $moduleData['title'],
                'credits' => 15,
                'hours' => 150,
                'is_active' => true,
            ]);
            
            $module->programmes()->attach($moduleData['programme']->id, [
                'sequence' => $index + 1,
                'is_mandatory' => false,
            ]);
        }
    }
    
    private function createCohorts(): void
    {
        $elc5 = Programme::where('code', 'ELC5')->first();
        $elc6 = Programme::where('code', 'ELC6')->first();
        
        // Create cohorts for 2024 and 2025 as per design document format (YYMM)
        $cohortData = [
            // 2024 cohorts
            ['code' => '2401', 'name' => 'January 2024', 'start' => '2024-01-15', 'end' => '2024-12-15', 'status' => 'completed'],
            ['code' => '2404', 'name' => 'April 2024', 'start' => '2024-04-15', 'end' => '2025-03-15', 'status' => 'active'],
            ['code' => '2409', 'name' => 'September 2024', 'start' => '2024-09-15', 'end' => '2025-08-15', 'status' => 'active'],
            
            // 2025 cohorts (planned/future)
            ['code' => '2501', 'name' => 'January 2025', 'start' => '2025-01-15', 'end' => '2025-12-15', 'status' => 'planned'],
            ['code' => '2504', 'name' => 'April 2025', 'start' => '2025-04-15', 'end' => '2026-03-15', 'status' => 'planned'],
            ['code' => '2509', 'name' => 'September 2025', 'start' => '2025-09-15', 'end' => '2026-08-15', 'status' => 'planned'],
        ];
        
        // Create cohorts for both ELC5 and ELC6
        foreach ([$elc5, $elc6] as $programme) {
            foreach ($cohortData as $cohort) {
                Cohort::create([
                    'programme_id' => $programme->id,
                    'code' => $cohort['code'],
                    'name' => $cohort['name'],
                    'start_date' => $cohort['start'],
                    'end_date' => $cohort['end'],
                    'status' => $cohort['status'],
                ]);
            }
        }
    }
    
    private function createModuleInstances(): void
    {
        $teachers = User::where('role', 'teacher')->get();
        
        // Create module instances for ELC5 2404 cohort (active)
        $elc5_2404 = Cohort::whereHas('programme', function($q) {
                $q->where('code', 'ELC5');
            })->where('code', '2404')->first();
        
        $elc5Modules = Module::whereHas('programmes', function($q) {
                $q->where('code', 'ELC5');
            })->get();
        
        foreach ($elc5Modules as $index => $module) {
            ModuleInstance::create([
                'module_id' => $module->id,
                'cohort_id' => $elc5_2404->id,
                'instance_code' => $module->code . '-' . $elc5_2404->code,
                'start_date' => $module->code === 'ELC504' ? $elc5_2404->start_date : // Placement runs parallel
                    $elc5_2404->start_date->addMonths($index * 3), // Sequential modules
                'end_date' => $module->code === 'ELC504' ? $elc5_2404->end_date : // Placement runs full duration
                    $elc5_2404->start_date->addMonths(($index * 3) + 4),
                'teacher_id' => $teachers->random()->id,
                'status' => 'active',
            ]);
        }
    }
    
    private function createAssessmentComponents(): void
    {
        $modules = Module::all();
        
        foreach ($modules as $module) {
            // Standard assessment structure for most modules
            if (str_contains($module->code, 'ELC') && !str_contains($module->code, '04')) {
                // Regular ELC modules (not placement)
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Assignment 1',
                    'type' => 'assignment',
                    'weight' => 40.00,
                    'sequence' => 1,
                    'is_active' => true,
                ]);
                
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Assignment 2',
                    'type' => 'assignment',
                    'weight' => 35.00,
                    'sequence' => 2,
                    'is_active' => true,
                ]);
                
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Final Assessment',
                    'type' => 'exam',
                    'weight' => 25.00,
                    'sequence' => 3,
                    'is_active' => true,
                ]);
            } else if (str_contains($module->code, '04')) {
                // Placement modules
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Placement Portfolio',
                    'type' => 'project',
                    'weight' => 60.00,
                    'sequence' => 1,
                    'is_active' => true,
                ]);
                
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Reflective Essay',
                    'type' => 'assignment',
                    'weight' => 40.00,
                    'sequence' => 2,
                    'is_active' => true,
                ]);
            } else {
                // Other modules - simple structure
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Coursework',
                    'type' => 'assignment',
                    'weight' => 60.00,
                    'sequence' => 1,
                    'is_active' => true,
                ]);
                
                AssessmentComponent::create([
                    'module_id' => $module->id,
                    'name' => 'Final Assessment',
                    'type' => 'exam',
                    'weight' => 40.00,
                    'sequence' => 2,
                    'is_active' => true,
                ]);
            }
        }
    }
    
    private function createStudents(): void
    {
        $studentData = [
            // Active students in various programmes
            ['first_name' => 'Emma', 'last_name' => 'Murphy', 'email' => 'emma.murphy@student.ie', 'status' => 'active', 'county' => 'Dublin'],
            ['first_name' => 'Liam', 'last_name' => 'O\'Sullivan', 'email' => 'liam.osullivan@student.ie', 'status' => 'active', 'county' => 'Cork'],
            ['first_name' => 'Sophie', 'last_name' => 'Walsh', 'email' => 'sophie.walsh@student.ie', 'status' => 'active', 'county' => 'Galway'],
            ['first_name' => 'James', 'last_name' => 'Kelly', 'email' => 'james.kelly@student.ie', 'status' => 'deferred', 'county' => 'Mayo'],
            ['first_name' => 'Chloe', 'last_name' => 'Ryan', 'email' => 'chloe.ryan@student.ie', 'status' => 'active', 'county' => 'Limerick'],
            
            // Students for testing various scenarios
            ['first_name' => 'Daniel', 'last_name' => 'McCarthy', 'email' => 'daniel.mccarthy@student.ie', 'status' => 'completed', 'county' => 'Kerry'],
            ['first_name' => 'Grace', 'last_name' => 'O\'Brien', 'email' => 'grace.obrien@student.ie', 'status' => 'active', 'county' => 'Waterford'],
            ['first_name' => 'Ryan', 'last_name' => 'Flynn', 'email' => 'ryan.flynn@student.ie', 'status' => 'enquiry', 'county' => 'Wicklow'],
            ['first_name' => 'Aoife', 'last_name' => 'Byrne', 'email' => 'aoife.byrne@student.ie', 'status' => 'active', 'county' => 'Kildare'],
            ['first_name' => 'Conor', 'last_name' => 'Doyle', 'email' => 'conor.doyle@student.ie', 'status' => 'cancelled', 'county' => 'Meath'],
            
            // Additional students for realistic numbers
            ['first_name' => 'Hannah', 'last_name' => 'Power', 'email' => 'hannah.power@student.ie', 'status' => 'active', 'county' => 'Tipperary'],
            ['first_name' => 'Sean', 'last_name' => 'Fitzgerald', 'email' => 'sean.fitzgerald@student.ie', 'status' => 'active', 'county' => 'Clare'],
            ['first_name' => 'Mia', 'last_name' => 'Connolly', 'email' => 'mia.connolly@student.ie', 'status' => 'deferred', 'county' => 'Sligo'],
            ['first_name' => 'Adam', 'last_name' => 'Hughes', 'email' => 'adam.hughes@student.ie', 'status' => 'active', 'county' => 'Donegal'],
            ['first_name' => 'Ella', 'last_name' => 'Brennan', 'email' => 'ella.brennan@student.ie', 'status' => 'active', 'county' => 'Louth'],
        ];
        
        $createdBy = User::where('role', 'student_services')->first()->id;
        
        foreach ($studentData as $data) {
            Student::create([
                'student_number' => Student::generateStudentNumber(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => '08' . rand(10000000, 99999999),
                'address' => rand(1, 999) . ' ' . ['Main Street', 'Church Road', 'Park Avenue', 'Mill Lane'][rand(0, 3)],
                'city' => ['Dublin', 'Cork', 'Galway', 'Limerick', 'Waterford'][rand(0, 4)],
                'county' => $data['county'],
                'eircode' => strtoupper(substr($data['county'], 0, 1)) . rand(10, 99) . ' ' . chr(rand(65, 90)) . rand(100, 999),
                'date_of_birth' => Carbon::now()->subYears(rand(22, 45))->subDays(rand(1, 365)),
                'status' => $data['status'],
                'notes' => $data['status'] === 'deferred' ? 'Student deferred due to personal circumstances' : null,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);
        }
    }
    
    private function createEnrolments(): void
    {
        $students = Student::all();
        $elc5_2404 = Cohort::whereHas('programme', function($q) {
                $q->where('code', 'ELC5');
            })->where('code', '2404')->first();
        $elc6_2404 = Cohort::whereHas('programme', function($q) {
                $q->where('code', 'ELC6');
            })->where('code', '2404')->first();
        
        foreach ($students->take(10) as $index => $student) {
            if ($student->status === 'enquiry' || $student->status === 'cancelled') {
                continue; // Skip students not yet enrolled
            }
            
            $cohort = $index % 2 === 0 ? $elc5_2404 : $elc6_2404;
            
            $enrolment = Enrolment::create([
                'student_id' => $student->id,
                'programme_id' => $cohort->programme_id,
                'cohort_id' => $cohort->id,
                'enrolment_date' => $cohort->start_date->subDays(rand(10, 30)),
                'expected_completion_date' => $cohort->end_date,
                'status' => $student->status === 'completed' ? 'completed' : 
                           ($student->status === 'deferred' ? 'deferred' : 'active'),
            ]);
            
            // Create student module enrolments for active students
            if ($enrolment->status === 'active') {
                $moduleInstances = ModuleInstance::where('cohort_id', $cohort->id)->get();
                foreach ($moduleInstances as $instance) {
                    StudentModuleEnrolment::create([
                        'student_id' => $student->id,
                        'enrolment_id' => $enrolment->id,
                        'module_instance_id' => $instance->id,
                        'status' => 'active',
                        'attempt_number' => 1,
                    ]);
                }
            }
        }
    }
    
    private function createDeferrals(): void
    {
        // Create some deferral scenarios for testing
        $deferredStudents = Student::where('status', 'deferred')->get();
        $futureCohorts = Cohort::where('start_date', '>', now())->get();
        
        foreach ($deferredStudents as $student) {
            $enrolment = $student->enrolments()->first();
            if ($enrolment && $futureCohorts->count() > 0) {
                Deferral::create([
                    'student_id' => $student->id,
                    'enrolment_id' => $enrolment->id,
                    'from_cohort_id' => $enrolment->cohort_id,
                    'to_cohort_id' => $futureCohorts->random()->id,
                    'deferral_date' => now()->subDays(rand(10, 60)),
                    'expected_return_date' => $futureCohorts->random()->start_date,
                    'reason' => collect([
                        'Personal family circumstances require attention',
                        'Health issues preventing continuation at this time',
                        'Financial difficulties - need time to arrange funding',
                        'Work commitments conflict with current schedule',
                        'Childcare arrangements need to be reorganized'
                    ])->random(),
                    'status' => collect(['pending', 'approved'])->random(),
                    'approved_by' => User::where('role', 'manager')->first()->id,
                    'approved_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}