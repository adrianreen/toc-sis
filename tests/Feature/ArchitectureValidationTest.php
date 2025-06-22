<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrolment;
use App\Models\StudentGradeRecord;
use App\Services\ArchitectureValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ArchitectureValidationTest extends TestCase
{
    use RefreshDatabase;

    private $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new ArchitectureValidationService();
    }

    public function test_validates_complete_architecture_successfully()
    {
        // Create a complete, valid architecture
        $this->createCompleteValidArchitecture();

        $results = $this->validationService->validateEntireArchitecture();

        $this->assertTrue($results['valid']);
        $this->assertEmpty($results['errors']);
        $this->assertGreaterThan(0, $results['stats']['programmes']);
        $this->assertGreaterThan(0, $results['stats']['programme_instances']);
        $this->assertGreaterThan(0, $results['stats']['modules']);
        $this->assertGreaterThan(0, $results['stats']['module_instances']);
    }

    public function test_detects_missing_programme_fields()
    {
        // Create programme with missing fields
        Programme::create([
            'title' => '',  // Missing title
            'awarding_body' => 'Test Body',
            'nfq_level' => 6,
            'total_credits' => 0,  // Invalid credits
        ]);

        $results = $this->validationService->validateEntireArchitecture();

        $this->assertFalse($results['valid']);
        $this->assertContains('Programme ID 1: Missing title', $results['errors']);
        $this->assertContains('Programme ID 1: Invalid total credits (0)', $results['errors']);
    }

    public function test_detects_invalid_assessment_strategy()
    {
        // Create module with invalid assessment strategy
        Module::create([
            'title' => 'Test Module',
            'module_code' => 'TEST101',
            'credit_value' => 10,
            'assessment_strategy' => [
                ['component_name' => 'Essay', 'weighting' => 60, 'is_must_pass' => false],
                ['component_name' => 'Exam', 'weighting' => 50, 'is_must_pass' => true], // Total 110%
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        $results = $this->validationService->validateEntireArchitecture();

        $this->assertFalse($results['valid']);
        $this->assertContains('Module ID 1: Assessment weights total 110%, must be 100%', $results['errors']);
    }

    public function test_detects_invalid_enrolment_types()
    {
        $student = Student::factory()->create();
        $module = Module::factory()->create();
        $moduleInstance = ModuleInstance::factory()->create(['module_id' => $module->id]);

        // Create invalid enrolment (programme type but with module instance)
        Enrolment::create([
            'student_id' => $student->id,
            'enrolment_type' => 'programme',
            'module_instance_id' => $moduleInstance->id, // Should be null for programme type
            'enrolment_date' => now(),
            'status' => 'active',
        ]);

        $results = $this->validationService->validateEntireArchitecture();

        $this->assertFalse($results['valid']);
        $this->assertContains('Enrolment ID 1: Programme enrolment should not have module instance ID', $results['errors']);
    }

    public function test_detects_orphaned_curriculum_links()
    {
        // Create orphaned curriculum link
        DB::table('programme_instance_curriculum')->insert([
            'programme_instance_id' => 999, // Non-existent
            'module_instance_id' => 888,    // Non-existent
        ]);

        $results = $this->validationService->validateEntireArchitecture();

        $this->assertFalse($results['valid']);
        $this->assertContains('Orphaned curriculum link: Programme Instance 999 -> Module Instance 888', $results['errors']);
    }

    public function test_validates_programme_curriculum_correctly()
    {
        // Create programme with correct total credits
        $programme = Programme::create([
            'title' => 'Test Programme',
            'awarding_body' => 'Test Body',
            'nfq_level' => 6,
            'total_credits' => 120,
        ]);

        $programmeInstance = ProgrammeInstance::create([
            'programme_id' => $programme->id,
            'label' => 'Test Instance',
            'intake_start_date' => now(),
            'intake_end_date' => now()->addYear(),
            'default_delivery_style' => 'sync',
        ]);

        // Create modules totaling 120 credits
        $module1 = Module::factory()->create(['credit_value' => 60]);
        $module2 = Module::factory()->create(['credit_value' => 60]);
        
        $instance1 = ModuleInstance::factory()->create(['module_id' => $module1->id]);
        $instance2 = ModuleInstance::factory()->create(['module_id' => $module2->id]);

        // Link to curriculum
        $programmeInstance->moduleInstances()->attach([$instance1->id, $instance2->id]);

        $results = $this->validationService->validateProgrammeCurriculum($programmeInstance);

        $this->assertEmpty($results['errors']);
        $this->assertEmpty($results['warnings']);
    }

    public function test_detects_credit_mismatches()
    {
        // Create programme expecting 120 credits
        $programme = Programme::create([
            'title' => 'Test Programme',
            'awarding_body' => 'Test Body',
            'nfq_level' => 6,
            'total_credits' => 120,
        ]);

        $programmeInstance = ProgrammeInstance::create([
            'programme_id' => $programme->id,
            'label' => 'Test Instance',
            'intake_start_date' => now(),
            'intake_end_date' => now()->addYear(),
            'default_delivery_style' => 'sync',
        ]);

        // Create modules totaling only 90 credits
        $module1 = Module::factory()->create(['credit_value' => 60]);
        $module2 = Module::factory()->create(['credit_value' => 30]);
        
        $instance1 = ModuleInstance::factory()->create(['module_id' => $module1->id]);
        $instance2 = ModuleInstance::factory()->create(['module_id' => $module2->id]);

        $programmeInstance->moduleInstances()->attach([$instance1->id, $instance2->id]);

        $results = $this->validationService->validateProgrammeCurriculum($programmeInstance);

        $this->assertContains('Curriculum credits (90) less than programme requirement (120)', $results['warnings']);
    }

    public function test_auto_fix_removes_orphaned_records()
    {
        // Create orphaned curriculum link
        DB::table('programme_instance_curriculum')->insert([
            'programme_instance_id' => 999,
            'module_instance_id' => 888,
        ]);

        // Create orphaned grade record
        StudentGradeRecord::create([
            'student_id' => 999, // Non-existent student
            'module_instance_id' => 888, // Non-existent module instance
            'assessment_component_name' => 'Test',
            'grade' => 85,
        ]);

        $results = $this->validationService->autoFixIssues();

        $this->assertContains('Removed 1 orphaned curriculum links', $results['fixed']);
        $this->assertContains('Removed 1 orphaned grade records', $results['fixed']);
        
        // Verify removal
        $this->assertEquals(0, DB::table('programme_instance_curriculum')->count());
        $this->assertEquals(0, StudentGradeRecord::count());
    }

    public function test_validates_grade_record_integrity()
    {
        $student = Student::factory()->create();
        $module = Module::factory()->create([
            'assessment_strategy' => [
                ['component_name' => 'Essay', 'weighting' => 100, 'is_must_pass' => false],
            ]
        ]);
        $moduleInstance = ModuleInstance::factory()->create(['module_id' => $module->id]);

        // Valid grade record
        StudentGradeRecord::create([
            'student_id' => $student->id,
            'module_instance_id' => $moduleInstance->id,
            'assessment_component_name' => 'Essay',
            'grade' => 85,
        ]);

        // Invalid grade record (component doesn't exist in module)
        StudentGradeRecord::create([
            'student_id' => $student->id,
            'module_instance_id' => $moduleInstance->id,
            'assessment_component_name' => 'NonExistentComponent',
            'grade' => 85,
        ]);

        $results = $this->validationService->validateEntireArchitecture();

        $this->assertFalse($results['valid']);
        $this->assertContains("Grade Record ID 2: Assessment component 'NonExistentComponent' not found in module strategy", $results['errors']);
    }

    public function test_command_runs_successfully()
    {
        $this->createCompleteValidArchitecture();

        $this->artisan('architecture:validate')
            ->expectsOutput('✅ Architecture validation PASSED!')
            ->assertExitCode(0);
    }

    public function test_command_shows_stats()
    {
        $this->createCompleteValidArchitecture();

        $this->artisan('architecture:validate --stats')
            ->expectsOutput('📊 SYSTEM STATISTICS:')
            ->assertExitCode(0);
    }

    public function test_command_validates_specific_programme()
    {
        $programmeInstance = $this->createCompleteValidArchitecture();

        $this->artisan("architecture:validate --programme={$programmeInstance->id}")
            ->expectsOutput('✅ Programme curriculum is valid!')
            ->assertExitCode(0);
    }

    private function createCompleteValidArchitecture()
    {
        // Create programme
        $programme = Programme::create([
            'title' => 'Bachelor of Science',
            'awarding_body' => 'Test University',
            'nfq_level' => 6,
            'total_credits' => 120,
        ]);

        // Create programme instance
        $programmeInstance = ProgrammeInstance::create([
            'programme_id' => $programme->id,
            'label' => 'September 2024',
            'intake_start_date' => now(),
            'intake_end_date' => now()->addYear(),
            'default_delivery_style' => 'sync',
        ]);

        // Create modules
        $module1 = Module::create([
            'title' => 'Introduction to Programming',
            'module_code' => 'CS101',
            'credit_value' => 60,
            'assessment_strategy' => [
                ['component_name' => 'Assignment', 'weighting' => 40, 'is_must_pass' => false],
                ['component_name' => 'Exam', 'weighting' => 60, 'is_must_pass' => true],
            ],
            'allows_standalone_enrolment' => true,
            'async_instance_cadence' => 'quarterly',
        ]);

        $module2 = Module::create([
            'title' => 'Mathematics',
            'module_code' => 'MATH101',
            'credit_value' => 60,
            'assessment_strategy' => [
                ['component_name' => 'Test', 'weighting' => 100, 'is_must_pass' => false],
            ],
            'allows_standalone_enrolment' => false,
            'async_instance_cadence' => 'quarterly',
        ]);

        // Create module instances
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        $instance1 = ModuleInstance::create([
            'module_id' => $module1->id,
            'tutor_id' => $teacher->id,
            'start_date' => now(),
            'target_end_date' => now()->addMonths(4),
            'delivery_style' => 'sync',
        ]);

        $instance2 = ModuleInstance::create([
            'module_id' => $module2->id,
            'tutor_id' => $teacher->id,
            'start_date' => now(),
            'target_end_date' => now()->addMonths(4),
            'delivery_style' => 'sync',
        ]);

        // Create curriculum links
        $programmeInstance->moduleInstances()->attach([$instance1->id, $instance2->id]);

        // Create student and enrolment
        $student = Student::factory()->create();
        
        Enrolment::create([
            'student_id' => $student->id,
            'enrolment_type' => 'programme',
            'programme_instance_id' => $programmeInstance->id,
            'enrolment_date' => now(),
            'status' => 'active',
        ]);

        return $programmeInstance;
    }
}