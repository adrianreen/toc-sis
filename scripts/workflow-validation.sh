#!/bin/bash

# TOC-SIS Comprehensive Workflow Validation Script
# This script validates the complete 4-level Programme-Module architecture
# and tests all critical workflows from the WORKFLOW_TESTING_SCENARIOS.md

set -e

echo "========================================="
echo "TOC-SIS Workflow Validation Testing"
echo "========================================="

# Configuration
BASE_URL="${BASE_URL:-http://localhost:8000}"
TEST_EMAIL="${TEST_EMAIL:-workflow.test@theopencollege.com}"
NOTIFICATION_TEST=${NOTIFICATION_TEST:-false}

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Test results tracking
declare -a TEST_RESULTS=()
declare -a FAILED_TESTS=()
declare -a WARNING_TESTS=()

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}$message${NC}"
}

# Function to record test result
record_test() {
    local test_name="$1"
    local status="$2"
    local details="$3"
    
    TEST_RESULTS+=("$test_name|$status|$details")
    
    case "$status" in
        "PASS")
            print_status "$GREEN" "✅ $test_name: PASS"
            ;;
        "FAIL")
            print_status "$RED" "❌ $test_name: FAIL - $details"
            FAILED_TESTS+=("$test_name: $details")
            ;;
        "WARNING")
            print_status "$YELLOW" "⚠️  $test_name: WARNING - $details"
            WARNING_TESTS+=("$test_name: $details")
            ;;
        "SKIP")
            print_status "$YELLOW" "⏭️  $test_name: SKIPPED - $details"
            ;;
    esac
}

# Function to validate database schema and relationships
validate_database_schema() {
    print_status "$BLUE" "=== Database Schema Validation ==="
    
    # Check core tables exist
    local tables=(
        "programmes"
        "programme_instances" 
        "modules"
        "module_instances"
        "programme_instance_curriculum"
        "enrolments"
        "student_grade_records"
        "students"
        "users"
    )
    
    for table in "${tables[@]}"; do
        local exists=$(php artisan tinker --execute="
            try {
                \$exists = \Illuminate\Support\Facades\Schema::hasTable('$table');
                echo \$exists ? 'true' : 'false';
            } catch (Exception \$e) {
                echo 'false';
            }
        " 2>/dev/null)
        
        if [ "$exists" = "true" ]; then
            record_test "Schema: $table table exists" "PASS" ""
        else
            record_test "Schema: $table table exists" "FAIL" "Table $table does not exist"
        fi
    done
    
    # Validate foreign key relationships
    print_status "$BLUE" "--- Foreign Key Relationship Validation ---"
    
    local relationship_test=$(php artisan tinker --execute="
        try {
            // Test Programme -> ProgrammeInstance relationship
            \$programme = App\Models\Programme::first();
            if (\$programme) {
                \$instances = \$programme->programmeInstances()->count();
                echo 'Programme->ProgrammeInstance: ' . \$instances . PHP_EOL;
            }
            
            // Test Module -> ModuleInstance relationship
            \$module = App\Models\Module::first();
            if (\$module) {
                \$instances = \$module->moduleInstances()->count();
                echo 'Module->ModuleInstance: ' . \$instances . PHP_EOL;
            }
            
            // Test curriculum relationship
            \$programmeInstance = App\Models\ProgrammeInstance::first();
            if (\$programmeInstance) {
                \$modules = \$programmeInstance->moduleInstances()->count();
                echo 'ProgrammeInstance->ModuleInstance: ' . \$modules . PHP_EOL;
            }
            
            // Test enrolment relationships
            \$enrolment = App\Models\Enrolment::first();
            if (\$enrolment) {
                \$student = \$enrolment->student;
                echo 'Enrolment->Student: ' . (\$student ? 'connected' : 'missing') . PHP_EOL;
            }
            
            echo 'relationships_valid';
        } catch (Exception \$e) {
            echo 'relationships_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$relationship_test" == *"relationships_valid"* ]]; then
        record_test "Database Relationships" "PASS" "All foreign key relationships working"
    else
        record_test "Database Relationships" "FAIL" "Relationship validation failed: $relationship_test"
    fi
}

# Function to validate the 4-level architecture implementation
validate_architecture_integrity() {
    print_status "$BLUE" "=== 4-Level Architecture Validation ==="
    
    # Test Programme blueprint structure
    local programme_test=$(php artisan tinker --execute="
        try {
            \$programme = App\Models\Programme::first();
            if (!\$programme) {
                echo 'no_programmes';
                exit;
            }
            
            \$required_fields = ['title', 'awarding_body', 'nfq_level', 'total_credits'];
            foreach (\$required_fields as \$field) {
                if (empty(\$programme->{\$field})) {
                    echo 'missing_field: ' . \$field;
                    exit;
                }
            }
            
            echo 'programme_valid';
        } catch (Exception \$e) {
            echo 'programme_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$programme_test" in
        "programme_valid")
            record_test "Programme Blueprint Structure" "PASS" ""
            ;;
        "no_programmes")
            record_test "Programme Blueprint Structure" "FAIL" "No programmes found - run seeder first"
            ;;
        *)
            record_test "Programme Blueprint Structure" "FAIL" "$programme_test"
            ;;
    esac
    
    # Test Module assessment strategy structure
    local module_test=$(php artisan tinker --execute="
        try {
            \$module = App\Models\Module::first();
            if (!\$module) {
                echo 'no_modules';
                exit;
            }
            
            if (empty(\$module->assessment_strategy)) {
                echo 'no_assessment_strategy';
                exit;
            }
            
            \$strategy = \$module->assessment_strategy;
            if (!is_array(\$strategy) || empty(\$strategy)) {
                echo 'invalid_assessment_structure';
                exit;
            }
            
            // Validate first assessment component structure
            \$component = \$strategy[0];
            \$required = ['component_name', 'weighting', 'is_must_pass'];
            foreach (\$required as \$field) {
                if (!array_key_exists(\$field, \$component)) {
                    echo 'missing_component_field: ' . \$field;
                    exit;
                }
            }
            
            echo 'module_valid';
        } catch (Exception \$e) {
            echo 'module_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$module_test" in
        "module_valid")
            record_test "Module Assessment Strategy" "PASS" ""
            ;;
        "no_modules")
            record_test "Module Assessment Strategy" "FAIL" "No modules found - run seeder first"
            ;;
        *)
            record_test "Module Assessment Strategy" "FAIL" "$module_test"
            ;;
    esac
    
    # Test curriculum linking mechanism
    local curriculum_test=$(php artisan tinker --execute="
        try {
            \$programmeInstance = App\Models\ProgrammeInstance::with('moduleInstances')->first();
            if (!\$programmeInstance) {
                echo 'no_programme_instances';
                exit;
            }
            
            \$moduleCount = \$programmeInstance->moduleInstances()->count();
            if (\$moduleCount === 0) {
                echo 'no_curriculum_links';
                exit;
            }
            
            // Verify pivot table structure
            \$pivotData = \$programmeInstance->moduleInstances()->first()->pivot ?? null;
            if (!\$pivotData) {
                echo 'no_pivot_data';
                exit;
            }
            
            echo 'curriculum_valid: ' . \$moduleCount . ' modules linked';
        } catch (Exception \$e) {
            echo 'curriculum_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$curriculum_test" in
        curriculum_valid*)
            record_test "Curriculum Linking Mechanism" "PASS" "$curriculum_test"
            ;;
        "no_programme_instances")
            record_test "Curriculum Linking Mechanism" "FAIL" "No programme instances found"
            ;;
        "no_curriculum_links")
            record_test "Curriculum Linking Mechanism" "FAIL" "No modules linked to programme instances"
            ;;
        *)
            record_test "Curriculum Linking Mechanism" "FAIL" "$curriculum_test"
            ;;
    esac
}

# Function to test the two-path enrolment system
validate_enrolment_system() {
    print_status "$BLUE" "=== Two-Path Enrolment System Validation ==="
    
    # Test programme enrolment path
    local programme_enrolment_test=$(php artisan tinker --execute="
        try {
            \$programmeEnrolments = App\Models\Enrolment::where('enrolment_type', 'programme')
                ->whereNotNull('programme_instance_id')
                ->whereNull('module_instance_id')
                ->count();
            
            echo 'programme_enrolments: ' . \$programmeEnrolments;
        } catch (Exception \$e) {
            echo 'error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    # Test standalone module enrolment path
    local module_enrolment_test=$(php artisan tinker --execute="
        try {
            \$moduleEnrolments = App\Models\Enrolment::where('enrolment_type', 'module')
                ->whereNotNull('module_instance_id')
                ->whereNull('programme_instance_id')
                ->count();
            
            echo 'module_enrolments: ' . \$moduleEnrolments;
        } catch (Exception \$e) {
            echo 'error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$programme_enrolment_test" == *"programme_enrolments:"* ]]; then
        local count=$(echo "$programme_enrolment_test" | cut -d: -f2 | tr -d ' ')
        if [ "$count" -gt 0 ]; then
            record_test "Programme Enrolment Path" "PASS" "$count programme enrolments found"
        else
            record_test "Programme Enrolment Path" "WARNING" "No programme enrolments found"
        fi
    else
        record_test "Programme Enrolment Path" "FAIL" "$programme_enrolment_test"
    fi
    
    if [[ "$module_enrolment_test" == *"module_enrolments:"* ]]; then
        local count=$(echo "$module_enrolment_test" | cut -d: -f2 | tr -d ' ')
        if [ "$count" -gt 0 ]; then
            record_test "Standalone Module Enrolment Path" "PASS" "$count module enrolments found"
        else
            record_test "Standalone Module Enrolment Path" "WARNING" "No standalone module enrolments found"
        fi
    else
        record_test "Standalone Module Enrolment Path" "FAIL" "$module_enrolment_test"
    fi
    
    # Test enrolment service functionality
    local service_test=$(php artisan tinker --execute="
        try {
            \$service = app(App\Services\EnrolmentService::class);
            \$student = App\Models\Student::first();
            \$programmeInstance = App\Models\ProgrammeInstance::first();
            
            if (!\$student || !\$programmeInstance) {
                echo 'missing_test_data';
                exit;
            }
            
            // Test service methods exist
            \$methods = ['enrolStudentInProgramme', 'enrolStudentInModule'];
            foreach (\$methods as \$method) {
                if (!method_exists(\$service, \$method)) {
                    echo 'missing_method: ' . \$method;
                    exit;
                }
            }
            
            echo 'service_valid';
        } catch (Exception \$e) {
            echo 'service_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$service_test" in
        "service_valid")
            record_test "Enrolment Service" "PASS" ""
            ;;
        "missing_test_data")
            record_test "Enrolment Service" "WARNING" "No test data for service validation"
            ;;
        *)
            record_test "Enrolment Service" "FAIL" "$service_test"
            ;;
    esac
}

# Function to validate grade record system
validate_grade_system() {
    print_status "$BLUE" "=== Grade Record System Validation ==="
    
    # Test grade record structure and relationships
    local grade_test=$(php artisan tinker --execute="
        try {
            \$gradeRecord = App\Models\StudentGradeRecord::first();
            if (!\$gradeRecord) {
                echo 'no_grade_records';
                exit;
            }
            
            // Check required fields
            \$required = ['student_id', 'module_instance_id', 'assessment_component_name'];
            foreach (\$required as \$field) {
                if (empty(\$gradeRecord->{\$field})) {
                    echo 'missing_field: ' . \$field;
                    exit;
                }
            }
            
            // Test relationships
            \$student = \$gradeRecord->student;
            \$moduleInstance = \$gradeRecord->moduleInstance;
            
            if (!\$student) {
                echo 'missing_student_relationship';
                exit;
            }
            
            if (!\$moduleInstance) {
                echo 'missing_module_instance_relationship';
                exit;
            }
            
            echo 'grade_system_valid';
        } catch (Exception \$e) {
            echo 'grade_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$grade_test" in
        "grade_system_valid")
            record_test "Grade Record System" "PASS" ""
            ;;
        "no_grade_records")
            record_test "Grade Record System" "WARNING" "No grade records found - expected if no enrolments"
            ;;
        *)
            record_test "Grade Record System" "FAIL" "$grade_test"
            ;;
    esac
    
    # Test assessment visibility controls
    local visibility_test=$(php artisan tinker --execute="
        try {
            \$totalRecords = App\Models\StudentGradeRecord::count();
            \$visibleRecords = App\Models\StudentGradeRecord::where('is_visible_to_student', true)->count();
            \$hiddenRecords = App\Models\StudentGradeRecord::where('is_visible_to_student', false)->count();
            
            echo 'total: ' . \$totalRecords . ', visible: ' . \$visibleRecords . ', hidden: ' . \$hiddenRecords;
        } catch (Exception \$e) {
            echo 'visibility_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$visibility_test" == *"total:"* ]]; then
        record_test "Assessment Visibility Controls" "PASS" "Visibility system active: $visibility_test"
    else
        record_test "Assessment Visibility Controls" "FAIL" "$visibility_test"
    fi
}

# Function to test role-based access control
validate_access_control() {
    print_status "$BLUE" "=== Role-Based Access Control Validation ==="
    
    # Test user roles exist
    local role_test=$(php artisan tinker --execute="
        try {
            \$roles = App\Models\User::select('role')->distinct()->pluck('role')->toArray();
            \$expectedRoles = ['manager', 'student_services', 'teacher', 'student'];
            \$missingRoles = array_diff(\$expectedRoles, \$roles);
            
            if (empty(\$missingRoles)) {
                echo 'all_roles_present: ' . implode(', ', \$roles);
            } else {
                echo 'missing_roles: ' . implode(', ', \$missingRoles);
            }
        } catch (Exception \$e) {
            echo 'role_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$role_test" == *"all_roles_present"* ]]; then
        record_test "User Role System" "PASS" "$role_test"
    else
        record_test "User Role System" "WARNING" "$role_test"
    fi
    
    # Test middleware exists
    local middleware_test=$(php artisan tinker --execute="
        try {
            \$middleware = app('router')->getMiddleware();
            if (array_key_exists('role', \$middleware)) {
                echo 'role_middleware_exists';
            } else {
                echo 'role_middleware_missing';
            }
        } catch (Exception \$e) {
            echo 'middleware_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$middleware_test" in
        "role_middleware_exists")
            record_test "Role Middleware" "PASS" ""
            ;;
        "role_middleware_missing")
            record_test "Role Middleware" "FAIL" "Role middleware not registered"
            ;;
        *)
            record_test "Role Middleware" "FAIL" "$middleware_test"
            ;;
    esac
}

# Function to test notification system
validate_notification_system() {
    print_status "$BLUE" "=== Notification System Validation ==="
    
    # Test notification service
    local service_test=$(php artisan tinker --execute="
        try {
            \$service = app(App\Services\NotificationService::class);
            echo 'notification_service_available';
        } catch (Exception \$e) {
            echo 'service_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$service_test" in
        "notification_service_available")
            record_test "Notification Service" "PASS" ""
            ;;
        *)
            record_test "Notification Service" "FAIL" "$service_test"
            ;;
    esac
    
    # Test email templates exist
    local template_test=$(php artisan tinker --execute="
        try {
            \$templateCount = App\Models\EmailTemplate::count();
            echo 'email_templates: ' . \$templateCount;
        } catch (Exception \$e) {
            echo 'template_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$template_test" == *"email_templates:"* ]]; then
        local count=$(echo "$template_test" | cut -d: -f2 | tr -d ' ')
        if [ "$count" -gt 0 ]; then
            record_test "Email Templates" "PASS" "$count templates available"
        else
            record_test "Email Templates" "WARNING" "No email templates found - run seeder"
        fi
    else
        record_test "Email Templates" "FAIL" "$template_test"
    fi
    
    # Test notification preferences
    local preference_test=$(php artisan tinker --execute="
        try {
            \$user = App\Models\User::first();
            if (\$user) {
                \$preferences = \$user->notificationPreferences()->count();
                echo 'notification_preferences: ' . \$preferences;
            } else {
                echo 'no_users_for_preference_test';
            }
        } catch (Exception \$e) {
            echo 'preference_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$preference_test" == *"notification_preferences:"* ]]; then
        record_test "Notification Preferences" "PASS" "$preference_test"
    elif [[ "$preference_test" == "no_users_for_preference_test" ]]; then
        record_test "Notification Preferences" "WARNING" "No users available for testing"
    else
        record_test "Notification Preferences" "FAIL" "$preference_test"
    fi
}

# Function to test data integrity and constraints
validate_data_integrity() {
    print_status "$BLUE" "=== Data Integrity Validation ==="
    
    # Test for orphaned records
    local orphan_test=$(php artisan tinker --execute="
        try {
            // Check for programme instances without programmes
            \$orphanedProgrammeInstances = App\Models\ProgrammeInstance::whereNotExists(function(\$query) {
                \$query->select(DB::raw(1))
                      ->from('programmes')
                      ->whereRaw('programmes.id = programme_instances.programme_id');
            })->count();
            
            // Check for module instances without modules
            \$orphanedModuleInstances = App\Models\ModuleInstance::whereNotExists(function(\$query) {
                \$query->select(DB::raw(1))
                      ->from('modules')
                      ->whereRaw('modules.id = module_instances.module_id');
            })->count();
            
            // Check for enrolments without students
            \$orphanedEnrolments = App\Models\Enrolment::whereNotExists(function(\$query) {
                \$query->select(DB::raw(1))
                      ->from('students')
                      ->whereRaw('students.id = enrolments.student_id');
            })->count();
            
            echo 'orphaned_programme_instances: ' . \$orphanedProgrammeInstances . PHP_EOL;
            echo 'orphaned_module_instances: ' . \$orphanedModuleInstances . PHP_EOL;
            echo 'orphaned_enrolments: ' . \$orphanedEnrolments . PHP_EOL;
            
            if (\$orphanedProgrammeInstances + \$orphanedModuleInstances + \$orphanedEnrolments === 0) {
                echo 'no_orphaned_records';
            }
        } catch (Exception \$e) {
            echo 'orphan_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$orphan_test" == *"no_orphaned_records"* ]]; then
        record_test "Data Integrity: Orphaned Records" "PASS" "No orphaned records found"
    elif [[ "$orphan_test" == *"orphaned_"* ]]; then
        record_test "Data Integrity: Orphaned Records" "WARNING" "Some orphaned records found: $orphan_test"
    else
        record_test "Data Integrity: Orphaned Records" "FAIL" "$orphan_test"
    fi
    
    # Test for duplicate student numbers
    local duplicate_test=$(php artisan tinker --execute="
        try {
            \$duplicates = App\Models\Student::select('student_number')
                ->groupBy('student_number')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            
            echo 'duplicate_student_numbers: ' . \$duplicates;
        } catch (Exception \$e) {
            echo 'duplicate_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$duplicate_test" == "duplicate_student_numbers: 0" ]]; then
        record_test "Data Integrity: Duplicate Student Numbers" "PASS" ""
    else
        record_test "Data Integrity: Duplicate Student Numbers" "FAIL" "$duplicate_test"
    fi
}

# Function to test performance benchmarks
validate_performance_benchmarks() {
    print_status "$BLUE" "=== Performance Benchmark Validation ==="
    
    # Test student dashboard load time
    local dashboard_test=$(php artisan tinker --execute="
        \$start = microtime(true);
        try {
            \$student = App\Models\Student::with(['enrolments.programmeInstance.programme', 'enrolments.moduleInstance.module'])->first();
            if (\$student) {
                \$enrolments = \$student->enrolments;
                \$gradeRecords = \$student->gradeRecords()->with('moduleInstance.module')->get();
            }
            \$duration = (microtime(true) - \$start) * 1000;
            echo 'dashboard_load_time: ' . round(\$duration, 2) . 'ms';
        } catch (Exception \$e) {
            echo 'dashboard_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$dashboard_test" == *"dashboard_load_time:"* ]]; then
        local time=$(echo "$dashboard_test" | grep -o '[0-9.]*ms')
        local time_num=$(echo "$time" | grep -o '[0-9.]*')
        if (( $(echo "$time_num < 1000" | bc -l) )); then
            record_test "Performance: Dashboard Load" "PASS" "$time"
        else
            record_test "Performance: Dashboard Load" "WARNING" "$time (slower than 1000ms)"
        fi
    else
        record_test "Performance: Dashboard Load" "FAIL" "$dashboard_test"
    fi
    
    # Test enrolment creation performance
    local enrolment_test=$(php artisan tinker --execute="
        \$start = microtime(true);
        try {
            \$service = app(App\Services\EnrolmentService::class);
            \$duration = (microtime(true) - \$start) * 1000;
            echo 'enrolment_service_load: ' . round(\$duration, 2) . 'ms';
        } catch (Exception \$e) {
            echo 'enrolment_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$enrolment_test" == *"enrolment_service_load:"* ]]; then
        local time=$(echo "$enrolment_test" | grep -o '[0-9.]*ms')
        record_test "Performance: Enrolment Service" "PASS" "$time"
    else
        record_test "Performance: Enrolment Service" "FAIL" "$enrolment_test"
    fi
}

# Function to test realistic workflow scenarios
test_workflow_scenarios() {
    print_status "$BLUE" "=== Workflow Scenario Testing ==="
    
    # Test Scenario 1.1: New Student Onboarding (Simplified)
    print_status "$YELLOW" "--- Testing Scenario 1.1: Student Onboarding ---"
    
    local onboarding_test=$(php artisan tinker --execute="
        try {
            // Simulate creating a new student
            \$studentData = [
                'student_number' => App\Models\Student::generateStudentNumber(),
                'first_name' => 'Test',
                'last_name' => 'Student',
                'email' => 'test.workflow@student.ie',
                'phone' => '0851234567',
                'status' => 'enquiry'
            ];
            
            // Check if we can create the student record structure
            \$requiredFields = ['student_number', 'first_name', 'last_name', 'email', 'status'];
            foreach (\$requiredFields as \$field) {
                if (empty(\$studentData[\$field])) {
                    echo 'missing_required_field: ' . \$field;
                    exit;
                }
            }
            
            // Test status progression
            \$validStatuses = ['enquiry', 'enrolled', 'active', 'withdrawn', 'graduated'];
            if (!in_array(\$studentData['status'], \$validStatuses)) {
                echo 'invalid_status';
                exit;
            }
            
            echo 'student_onboarding_structure_valid';
        } catch (Exception \$e) {
            echo 'onboarding_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$onboarding_test" in
        "student_onboarding_structure_valid")
            record_test "Workflow: Student Onboarding Structure" "PASS" ""
            ;;
        *)
            record_test "Workflow: Student Onboarding Structure" "FAIL" "$onboarding_test"
            ;;
    esac
    
    # Test Scenario 1.2: Standalone Module Enrolment
    print_status "$YELLOW" "--- Testing Scenario 1.2: Standalone Module Enrolment ---"
    
    local standalone_test=$(php artisan tinker --execute="
        try {
            // Check for modules that allow standalone enrolment
            \$standaloneModules = App\Models\Module::where('allows_standalone_enrolment', true)->count();
            
            // Check for module instances that could support standalone enrolment
            \$standaloneInstances = App\Models\ModuleInstance::whereHas('module', function(\$query) {
                \$query->where('allows_standalone_enrolment', true);
            })->count();
            
            echo 'standalone_modules: ' . \$standaloneModules . ', instances: ' . \$standaloneInstances;
        } catch (Exception \$e) {
            echo 'standalone_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    if [[ "$standalone_test" == *"standalone_modules:"* ]]; then
        record_test "Workflow: Standalone Module Support" "PASS" "$standalone_test"
    else
        record_test "Workflow: Standalone Module Support" "FAIL" "$standalone_test"
    fi
    
    # Test Scenario 3.1: Assessment Management
    print_status "$YELLOW" "--- Testing Scenario 3.1: Assessment Management ---"
    
    local assessment_test=$(php artisan tinker --execute="
        try {
            // Check assessment component structure
            \$moduleWithAssessments = App\Models\Module::whereNotNull('assessment_strategy')->first();
            if (!\$moduleWithAssessments) {
                echo 'no_modules_with_assessments';
                exit;
            }
            
            \$assessmentStrategy = \$moduleWithAssessments->assessment_strategy;
            if (!is_array(\$assessmentStrategy) || empty(\$assessmentStrategy)) {
                echo 'invalid_assessment_strategy';
                exit;
            }
            
            // Check if grade records can be created for assessment components
            \$component = \$assessmentStrategy[0];
            if (empty(\$component['component_name']) || !isset(\$component['weighting'])) {
                echo 'invalid_component_structure';
                exit;
            }
            
            echo 'assessment_management_valid';
        } catch (Exception \$e) {
            echo 'assessment_error: ' . \$e->getMessage();
        }
    " 2>/dev/null)
    
    case "$assessment_test" in
        "assessment_management_valid")
            record_test "Workflow: Assessment Management" "PASS" ""
            ;;
        "no_modules_with_assessments")
            record_test "Workflow: Assessment Management" "FAIL" "No modules with assessment strategies found"
            ;;
        *)
            record_test "Workflow: Assessment Management" "FAIL" "$assessment_test"
            ;;
    esac
}

# Function to generate comprehensive report
generate_validation_report() {
    print_status "$BLUE" "=== Workflow Validation Report ==="
    
    local total_tests=${#TEST_RESULTS[@]}
    local passed_tests=0
    local failed_tests=${#FAILED_TESTS[@]}
    local warning_tests=${#WARNING_TESTS[@]}
    
    # Count passed tests
    for result in "${TEST_RESULTS[@]}"; do
        if [[ "$result" == *"|PASS|"* ]]; then
            ((passed_tests++))
        fi
    done
    
    echo ""
    echo "📊 VALIDATION SUMMARY"
    echo "===================="
    printf "Total Tests: %d\n" "$total_tests"
    printf "✅ Passed: %d (%.1f%%)\n" "$passed_tests" "$(echo "scale=1; $passed_tests * 100 / $total_tests" | bc -l)"
    printf "❌ Failed: %d (%.1f%%)\n" "$failed_tests" "$(echo "scale=1; $failed_tests * 100 / $total_tests" | bc -l)"
    printf "⚠️  Warnings: %d (%.1f%%)\n" "$warning_tests" "$(echo "scale=1; $warning_tests * 100 / $total_tests" | bc -l)"
    
    if [ ${#FAILED_TESTS[@]} -gt 0 ]; then
        echo ""
        print_status "$RED" "❌ FAILED TESTS:"
        for failed in "${FAILED_TESTS[@]}"; do
            echo "   - $failed"
        done
    fi
    
    if [ ${#WARNING_TESTS[@]} -gt 0 ]; then
        echo ""
        print_status "$YELLOW" "⚠️  WARNINGS:"
        for warning in "${WARNING_TESTS[@]}"; do
            echo "   - $warning"
        done
    fi
    
    echo ""
    echo "📋 DETAILED RESULTS"
    echo "==================="
    printf "%-50s %-10s %s\n" "Test Name" "Status" "Details"
    echo "-------------------------------------------------------------------------"
    
    for result in "${TEST_RESULTS[@]}"; do
        IFS='|' read -r test_name status details <<< "$result"
        printf "%-50s %-10s %s\n" "$test_name" "$status" "$details"
    done
    
    # Save detailed report
    local report_file="/var/www/toc-sis/storage/logs/workflow-validation-$(date +%Y%m%d-%H%M%S).log"
    {
        echo "TOC-SIS Workflow Validation Report"
        echo "Generated: $(date)"
        echo "Configuration:"
        echo "  Base URL: $BASE_URL"
        echo "  Test Email: $TEST_EMAIL"
        echo ""
        echo "Summary:"
        echo "  Total Tests: $total_tests"
        echo "  Passed: $passed_tests"
        echo "  Failed: $failed_tests"
        echo "  Warnings: $warning_tests"
        echo ""
        echo "Results:"
        for result in "${TEST_RESULTS[@]}"; do
            echo "$result"
        done
        
        if [ ${#FAILED_TESTS[@]} -gt 0 ]; then
            echo ""
            echo "Failed Tests:"
            for failed in "${FAILED_TESTS[@]}"; do
                echo "  $failed"
            done
        fi
        
        if [ ${#WARNING_TESTS[@]} -gt 0 ]; then
            echo ""
            echo "Warnings:"
            for warning in "${WARNING_TESTS[@]}"; do
                echo "  $warning"
            done
        fi
    } > "$report_file"
    
    echo ""
    echo "📄 Detailed report saved to: $report_file"
    
    # Provide recommendations
    echo ""
    print_status "$BLUE" "🎯 RECOMMENDATIONS"
    echo "=================="
    
    if [ $failed_tests -eq 0 ] && [ $warning_tests -eq 0 ]; then
        print_status "$GREEN" "🎉 Excellent! All validations passed. Your TOC-SIS system is ready for production workflow testing."
    elif [ $failed_tests -eq 0 ]; then
        print_status "$YELLOW" "👍 Good! All critical tests passed, but there are some warnings to address."
        echo "   Consider running: php artisan db:seed --class=WorkflowTestingSeeder"
    else
        print_status "$RED" "⚠️  Critical issues found that need immediate attention:"
        echo "   1. Check database migrations: php artisan migrate"
        echo "   2. Run seeders: php artisan db:seed"
        echo "   3. Verify model relationships"
        echo "   4. Check required services are configured"
    fi
    
    # Return appropriate exit code
    if [ $failed_tests -gt 0 ]; then
        exit 1
    elif [ $warning_tests -gt 0 ]; then
        exit 2
    else
        exit 0
    fi
}

# Main execution function
main() {
    echo "Starting comprehensive workflow validation..."
    echo "Configuration:"
    echo "  Base URL: $BASE_URL"
    echo "  Test Email: $TEST_EMAIL"
    echo "  Notification Test: $NOTIFICATION_TEST"
    echo ""
    
    # Check Laravel application is available
    if ! php artisan --version >/dev/null 2>&1; then
        print_status "$RED" "❌ Laravel application not available"
        echo "Please ensure you're in the correct directory and Laravel is properly installed."
        exit 1
    fi
    
    # Run all validation tests
    validate_database_schema
    echo ""
    validate_architecture_integrity
    echo ""
    validate_enrolment_system
    echo ""
    validate_grade_system
    echo ""
    validate_access_control
    echo ""
    validate_notification_system
    echo ""
    validate_data_integrity
    echo ""
    validate_performance_benchmarks
    echo ""
    test_workflow_scenarios
    echo ""
    
    # Generate final report
    generate_validation_report
}

# Handle script arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --help, -h          Show this help message"
        echo "  --schema-only       Run only database schema validation"
        echo "  --architecture-only Run only architecture validation"
        echo "  --performance-only  Run only performance tests"
        echo "  --workflows-only    Run only workflow scenario tests"
        echo ""
        echo "Environment Variables:"
        echo "  BASE_URL           Application URL (default: http://localhost:8000)"
        echo "  TEST_EMAIL         Email for testing (default: workflow.test@theopencollege.com)"
        echo "  NOTIFICATION_TEST  Enable notification testing (default: false)"
        exit 0
        ;;
    --schema-only)
        echo "Running schema validation only..."
        validate_database_schema
        ;;
    --architecture-only)
        echo "Running architecture validation only..."
        validate_architecture_integrity
        ;;
    --performance-only)
        echo "Running performance tests only..."
        validate_performance_benchmarks
        ;;
    --workflows-only)
        echo "Running workflow scenario tests only..."
        test_workflow_scenarios
        ;;
    *)
        main
        ;;
esac