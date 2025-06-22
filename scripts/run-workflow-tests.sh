#!/bin/bash

# TOC-SIS Comprehensive Workflow Testing Orchestrator
# This script orchestrates the complete workflow testing suite

set -e

echo "========================================="
echo "TOC-SIS Comprehensive Workflow Testing"
echo "========================================="

# Configuration
TEST_SUITE="${TEST_SUITE:-full}"  # full, quick, validation-only, performance-only
DATA_SIZE="${DATA_SIZE:-medium}"   # small, medium, large, bulk
RESET_DATA="${RESET_DATA:-false}"  # Reset test data before running
PARALLEL_MODE="${PARALLEL_MODE:-false}"  # Run tests in parallel
GENERATE_REPORTS="${GENERATE_REPORTS:-true}"  # Generate comprehensive reports
CLEANUP_AFTER="${CLEANUP_AFTER:-false}"  # Cleanup test data after completion

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Test results tracking
declare -a TEST_SUITE_RESULTS=()
TOTAL_TEST_TIME=0
START_TIME=$(date +%s)

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}$message${NC}"
}

# Function to print section header
print_section() {
    local title="$1"
    echo ""
    print_status "$BOLD$CYAN" "╔══════════════════════════════════════════════════════════════════════════════╗"
    printf "${BOLD}${CYAN}║ %-76s ║${NC}\n" "$title"
    print_status "$BOLD$CYAN" "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo ""
}

# Function to record test suite result
record_test_suite() {
    local suite_name="$1"
    local status="$2"
    local duration="$3"
    local details="$4"
    
    TEST_SUITE_RESULTS+=("$suite_name|$status|$duration|$details")
    
    case "$status" in
        "PASS")
            print_status "$GREEN" "✅ $suite_name: PASSED in ${duration}s"
            ;;
        "FAIL")
            print_status "$RED" "❌ $suite_name: FAILED in ${duration}s - $details"
            ;;
        "SKIP")
            print_status "$YELLOW" "⏭️  $suite_name: SKIPPED - $details"
            ;;
        "WARNING")
            print_status "$YELLOW" "⚠️  $suite_name: COMPLETED WITH WARNINGS in ${duration}s - $details"
            ;;
    esac
}

# Function to measure execution time
measure_execution_time() {
    local command="$1"
    local start_time=$(date +%s)
    eval "$command"
    local exit_code=$?
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    echo "$duration $exit_code"
}

# Function to check prerequisites
check_prerequisites() {
    print_section "Checking Prerequisites"
    
    local prereq_status="PASS"
    local missing_deps=()
    
    # Check Laravel
    if ! php artisan --version >/dev/null 2>&1; then
        missing_deps+=("Laravel application not accessible")
        prereq_status="FAIL"
    else
        print_status "$GREEN" "✅ Laravel application accessible"
    fi
    
    # Check database connection
    if ! php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected';" 2>/dev/null | grep -q "Database connected"; then
        missing_deps+=("Database connection failed")
        prereq_status="FAIL"
    else
        print_status "$GREEN" "✅ Database connection working"
    fi
    
    # Check required commands
    local commands=("bc" "curl")
    for cmd in "${commands[@]}"; do
        if ! command -v "$cmd" >/dev/null 2>&1; then
            missing_deps+=("Command '$cmd' not found")
            prereq_status="FAIL"
        else
            print_status "$GREEN" "✅ Command '$cmd' available"
        fi
    done
    
    # Check script permissions
    local scripts=("workflow-validation.sh" "workflow-automation.sh" "generate-test-data.sh")
    for script in "${scripts[@]}"; do
        if [ ! -x "scripts/$script" ]; then
            print_status "$YELLOW" "⚠️  Making scripts/$script executable"
            chmod +x "scripts/$script"
        else
            print_status "$GREEN" "✅ scripts/$script is executable"
        fi
    done
    
    if [ "$prereq_status" = "FAIL" ]; then
        print_status "$RED" "❌ Prerequisites check failed:"
        for dep in "${missing_deps[@]}"; do
            echo "   - $dep"
        done
        exit 1
    fi
    
    print_status "$GREEN" "✅ All prerequisites satisfied"
}

# Function to prepare test environment
prepare_test_environment() {
    print_section "Preparing Test Environment"
    
    local prep_result=$(measure_execution_time "
        if [ '$RESET_DATA' = 'true' ]; then
            echo 'Resetting test data...'
            ./scripts/generate-test-data.sh --reset --size $DATA_SIZE
        else
            echo 'Ensuring test data exists...'
            # Check if we have sufficient test data
            student_count=\$(php artisan tinker --execute='echo App\\Models\\Student::count();' 2>/dev/null)
            if [ \"\$student_count\" -lt 10 ]; then
                echo 'Insufficient test data, generating...'
                ./scripts/generate-test-data.sh --size $DATA_SIZE
            else
                echo 'Sufficient test data exists (\$student_count students)'
            fi
        fi
    ")
    
    local duration=$(echo "$prep_result" | cut -d' ' -f1)
    local exit_code=$(echo "$prep_result" | cut -d' ' -f2)
    
    if [ "$exit_code" -eq 0 ]; then
        record_test_suite "Environment Preparation" "PASS" "$duration" ""
    else
        record_test_suite "Environment Preparation" "FAIL" "$duration" "Test data preparation failed"
        exit 1
    fi
}

# Function to run database schema validation
run_schema_validation() {
    print_section "Database Schema Validation"
    
    local validation_result=$(measure_execution_time "./scripts/workflow-validation.sh --schema-only")
    local duration=$(echo "$validation_result" | cut -d' ' -f1)
    local exit_code=$(echo "$validation_result" | cut -d' ' -f2)
    
    case "$exit_code" in
        0)
            record_test_suite "Schema Validation" "PASS" "$duration" ""
            ;;
        1)
            record_test_suite "Schema Validation" "FAIL" "$duration" "Critical schema issues found"
            ;;
        2)
            record_test_suite "Schema Validation" "WARNING" "$duration" "Schema warnings detected"
            ;;
    esac
}

# Function to run architecture validation
run_architecture_validation() {
    print_section "4-Level Architecture Validation"
    
    local validation_result=$(measure_execution_time "./scripts/workflow-validation.sh --architecture-only")
    local duration=$(echo "$validation_result" | cut -d' ' -f1)
    local exit_code=$(echo "$validation_result" | cut -d' ' -f2)
    
    case "$exit_code" in
        0)
            record_test_suite "Architecture Validation" "PASS" "$duration" ""
            ;;
        1)
            record_test_suite "Architecture Validation" "FAIL" "$duration" "Architecture integrity issues"
            ;;
        2)
            record_test_suite "Architecture Validation" "WARNING" "$duration" "Architecture warnings"
            ;;
    esac
}

# Function to run comprehensive workflow validation
run_workflow_validation() {
    print_section "Comprehensive Workflow Validation"
    
    local validation_result=$(measure_execution_time "./scripts/workflow-validation.sh")
    local duration=$(echo "$validation_result" | cut -d' ' -f1)
    local exit_code=$(echo "$validation_result" | cut -d' ' -f2)
    
    case "$exit_code" in
        0)
            record_test_suite "Workflow Validation" "PASS" "$duration" ""
            ;;
        1)
            record_test_suite "Workflow Validation" "FAIL" "$duration" "Critical workflow validation failures"
            ;;
        2)
            record_test_suite "Workflow Validation" "WARNING" "$duration" "Workflow validation warnings"
            ;;
    esac
}

# Function to run workflow automation tests
run_workflow_automation() {
    print_section "Workflow Automation Testing"
    
    local automation_cmd="./scripts/workflow-automation.sh"
    if [ "$PARALLEL_MODE" = "true" ]; then
        automation_cmd="$automation_cmd --parallel"
    fi
    if [ "$CLEANUP_AFTER" = "true" ]; then
        automation_cmd="$automation_cmd --cleanup"
    fi
    
    local automation_result=$(measure_execution_time "$automation_cmd")
    local duration=$(echo "$automation_result" | cut -d' ' -f1)
    local exit_code=$(echo "$automation_result" | cut -d' ' -f2)
    
    case "$exit_code" in
        0)
            record_test_suite "Workflow Automation" "PASS" "$duration" ""
            ;;
        1)
            record_test_suite "Workflow Automation" "FAIL" "$duration" "Workflow automation failures"
            ;;
    esac
}

# Function to run performance testing
run_performance_testing() {
    print_section "Performance Testing"
    
    # Check if analytics performance script exists
    if [ -f "scripts/analytics-performance-test.sh" ]; then
        local perf_result=$(measure_execution_time "./scripts/analytics-performance-test.sh --quick")
        local duration=$(echo "$perf_result" | cut -d' ' -f1)
        local exit_code=$(echo "$perf_result" | cut -d' ' -f2)
        
        case "$exit_code" in
            0)
                record_test_suite "Performance Testing" "PASS" "$duration" ""
                ;;
            1)
                record_test_suite "Performance Testing" "FAIL" "$duration" "Performance benchmarks failed"
                ;;
        esac
    else
        # Run workflow validation performance tests
        local perf_result=$(measure_execution_time "./scripts/workflow-validation.sh --performance-only")
        local duration=$(echo "$perf_result" | cut -d' ' -f1)
        local exit_code=$(echo "$perf_result" | cut -d' ' -f2)
        
        case "$exit_code" in
            0)
                record_test_suite "Performance Testing" "PASS" "$duration" ""
                ;;
            1)
                record_test_suite "Performance Testing" "FAIL" "$duration" "Performance tests failed"
                ;;
            2)
                record_test_suite "Performance Testing" "WARNING" "$duration" "Performance warnings"
                ;;
        esac
    fi
}

# Function to run integration testing
run_integration_testing() {
    print_section "Integration Testing"
    
    # Test notification system integration
    local notification_test=$(measure_execution_time "
        php artisan tinker --execute=\"
            try {
                \\\$service = app(App\\Services\\NotificationService::class);
                \\\$user = App\\Models\\User::where('role', 'student')->first();
                if (\\\$user) {
                    \\\$notification = \\\$service->notifyGradeReleased(\\\$user, 'Test Module', 'Test Assessment', 75.0);
                    if (\\\$notification) {
                        echo 'Notification integration: PASS';
                    } else {
                        echo 'Notification integration: FAIL';
                        exit(1);
                    }
                } else {
                    echo 'Notification integration: SKIP - No student users';
                }
            } catch (Exception \\\$e) {
                echo 'Notification integration: FAIL - ' . \\\$e->getMessage();
                exit(1);
            }
        \" 2>/dev/null
    ")
    
    local duration=$(echo "$notification_test" | cut -d' ' -f1)
    local exit_code=$(echo "$notification_test" | cut -d' ' -f2)
    
    case "$exit_code" in
        0)
            record_test_suite "Integration Testing" "PASS" "$duration" ""
            ;;
        1)
            record_test_suite "Integration Testing" "FAIL" "$duration" "Integration tests failed"
            ;;
    esac
}

# Function to run stress testing
run_stress_testing() {
    print_section "Stress Testing"
    
    # Simulate high-volume operations
    local stress_test=$(measure_execution_time "
        php artisan tinker --execute=\"
            try {
                \\\$start = microtime(true);
                
                // Test bulk student queries
                \\\$students = App\\Models\\Student::with(['enrolments.programmeInstance', 'gradeRecords'])->limit(100)->get();
                \\\$query_time = (microtime(true) - \\\$start) * 1000;
                
                if (\\\$query_time > 5000) {
                    echo 'Stress test: FAIL - Query too slow: ' . round(\\\$query_time, 2) . 'ms';
                    exit(1);
                }
                
                // Test bulk enrolment simulation
                \\\$enrolment_start = microtime(true);
                \\\$service = app(App\\Services\\EnrolmentService::class);
                // Just test service instantiation under load
                for (\\\$i = 0; \\\$i < 10; \\\$i++) {
                    \\\$test_service = app(App\\Services\\EnrolmentService::class);
                }
                \\\$enrolment_time = (microtime(true) - \\\$enrolment_start) * 1000;
                
                if (\\\$enrolment_time > 1000) {
                    echo 'Stress test: FAIL - Service instantiation too slow: ' . round(\\\$enrolment_time, 2) . 'ms';
                    exit(1);
                }
                
                echo 'Stress test: PASS - Query: ' . round(\\\$query_time, 2) . 'ms, Service: ' . round(\\\$enrolment_time, 2) . 'ms';
            } catch (Exception \\\$e) {
                echo 'Stress test: FAIL - ' . \\\$e->getMessage();
                exit(1);
            }
        \" 2>/dev/null
    ")
    
    local duration=$(echo "$stress_test" | cut -d' ' -f1)
    local exit_code=$(echo "$stress_test" | cut -d' ' -f2)
    
    case "$exit_code" in
        0)
            record_test_suite "Stress Testing" "PASS" "$duration" ""
            ;;
        1)
            record_test_suite "Stress Testing" "FAIL" "$duration" "Stress tests failed"
            ;;
    esac
}

# Function to generate comprehensive reports
generate_comprehensive_reports() {
    if [ "$GENERATE_REPORTS" = "true" ]; then
        print_section "Generating Comprehensive Reports"
        
        local report_time=$(measure_execution_time "
            # Create reports directory
            mkdir -p storage/reports/workflow-testing
            
            # Generate summary report
            report_file=\"storage/reports/workflow-testing/comprehensive-report-\$(date +%Y%m%d-%H%M%S).md\"
            
            cat > \"\$report_file\" << 'EOF'
# TOC-SIS Comprehensive Workflow Testing Report

Generated: \$(date)

## Test Configuration
- Test Suite: $TEST_SUITE
- Data Size: $DATA_SIZE
- Reset Data: $RESET_DATA
- Parallel Mode: $PARALLEL_MODE
- Total Test Time: \$((TOTAL_TEST_TIME))s

## Test Suite Results

EOF
            
            # Add test results
            for result in \"\${TEST_SUITE_RESULTS[@]}\"; do
                IFS='|' read -r suite_name status duration details <<< \"\$result\"
                echo \"### \$suite_name\" >> \"\$report_file\"
                echo \"- Status: \$status\" >> \"\$report_file\"
                echo \"- Duration: \${duration}s\" >> \"\$report_file\"
                if [ -n \"\$details\" ]; then
                    echo \"- Details: \$details\" >> \"\$report_file\"
                fi
                echo \"\" >> \"\$report_file\"
            done
            
            # Add system information
            cat >> \"\$report_file\" << 'EOF'
## System Information

EOF
            
            php artisan tinker --execute=\"
                echo '### Database Statistics' . PHP_EOL;
                echo '- Students: ' . App\\Models\\Student::count() . PHP_EOL;
                echo '- Programmes: ' . App\\Models\\Programme::count() . PHP_EOL;
                echo '- Programme Instances: ' . App\\Models\\ProgrammeInstance::count() . PHP_EOL;
                echo '- Modules: ' . App\\Models\\Module::count() . PHP_EOL;
                echo '- Module Instances: ' . App\\Models\\ModuleInstance::count() . PHP_EOL;
                echo '- Enrolments: ' . App\\Models\\Enrolment::count() . PHP_EOL;
                echo '- Grade Records: ' . App\\Models\\StudentGradeRecord::count() . PHP_EOL;
                echo '- Users: ' . App\\Models\\User::count() . PHP_EOL;
                echo '' . PHP_EOL;
                
                echo '### Performance Metrics' . PHP_EOL;
                \\\$start = microtime(true);
                \\\$students = App\\Models\\Student::with('enrolments')->limit(10)->get();
                \\\$query_time = (microtime(true) - \\\$start) * 1000;
                echo '- Student Query Performance: ' . round(\\\$query_time, 2) . 'ms for 10 students' . PHP_EOL;
                
                \\\$memory_usage = memory_get_usage(true) / 1024 / 1024;
                echo '- Memory Usage: ' . round(\\\$memory_usage, 2) . 'MB' . PHP_EOL;
                echo '' . PHP_EOL;
            \" 2>/dev/null >> \"\$report_file\"
            
            echo \"Comprehensive report generated: \$report_file\"
        ")
        
        local duration=$(echo "$report_time" | cut -d' ' -f1)
        local exit_code=$(echo "$report_time" | cut -d' ' -f2)
        
        if [ "$exit_code" -eq 0 ]; then
            record_test_suite "Report Generation" "PASS" "$duration" ""
        else
            record_test_suite "Report Generation" "FAIL" "$duration" "Report generation failed"
        fi
    fi
}

# Function to cleanup test environment
cleanup_test_environment() {
    if [ "$CLEANUP_AFTER" = "true" ]; then
        print_section "Cleaning Up Test Environment"
        
        local cleanup_result=$(measure_execution_time "
            # Remove test-specific data
            php artisan tinker --execute=\"
                // Clean up test students created during workflow testing
                App\\Models\\Student::where('email', 'like', '%.workflow@%')->delete();
                App\\Models\\Student::where('last_name', 'like', '%Test%')->delete();
                
                // Clean up test notifications
                App\\Models\\Notification::where('content', 'like', '%Test%')->delete();
                
                echo 'Test data cleanup completed';
            \" 2>/dev/null
        ")
        
        local duration=$(echo "$cleanup_result" | cut -d' ' -f1)
        local exit_code=$(echo "$cleanup_result" | cut -d' ' -f2)
        
        if [ "$exit_code" -eq 0 ]; then
            record_test_suite "Environment Cleanup" "PASS" "$duration" ""
        else
            record_test_suite "Environment Cleanup" "FAIL" "$duration" "Cleanup failed"
        fi
    fi
}

# Function to generate final summary
generate_final_summary() {
    print_section "Final Testing Summary"
    
    local total_suites=${#TEST_SUITE_RESULTS[@]}
    local passed_suites=0
    local failed_suites=0
    local warning_suites=0
    local skipped_suites=0
    
    # Count results
    for result in "${TEST_SUITE_RESULTS[@]}"; do
        IFS='|' read -r suite_name status duration details <<< "$result"
        case "$status" in
            "PASS") ((passed_suites++)) ;;
            "FAIL") ((failed_suites++)) ;;
            "WARNING") ((warning_suites++)) ;;
            "SKIP") ((skipped_suites++)) ;;
        esac
        TOTAL_TEST_TIME=$((TOTAL_TEST_TIME + duration))
    done
    
    # Calculate success rate
    local success_rate=0
    if [ $total_suites -gt 0 ]; then
        success_rate=$(echo "scale=1; $passed_suites * 100 / $total_suites" | bc -l)
    fi
    
    echo ""
    print_status "$BOLD$BLUE" "╔══════════════════════════════════════════════════════════════════════════════╗"
    print_status "$BOLD$BLUE" "║                            TESTING SUMMARY                                  ║"
    print_status "$BOLD$BLUE" "╚══════════════════════════════════════════════════════════════════════════════╝"
    echo ""
    
    printf "📊 ${BOLD}Test Suite Results:${NC}\n"
    printf "   Total Suites: %d\n" "$total_suites"
    printf "   ✅ Passed: %d (%.1f%%)\n" "$passed_suites" "$success_rate"
    printf "   ❌ Failed: %d\n" "$failed_suites"
    printf "   ⚠️  Warnings: %d\n" "$warning_suites"
    printf "   ⏭️  Skipped: %d\n" "$skipped_suites"
    echo ""
    
    printf "⏱️  ${BOLD}Execution Time:${NC}\n"
    printf "   Total Test Time: %ds\n" "$TOTAL_TEST_TIME"
    printf "   Wall Clock Time: %ds\n" "$(($(date +%s) - START_TIME))"
    echo ""
    
    # Detailed results
    print_status "$BOLD$BLUE" "📋 Detailed Test Suite Results:"
    echo "==============================================================================="
    printf "%-25s %-10s %-10s %s\n" "Test Suite" "Status" "Duration" "Details"
    echo "-------------------------------------------------------------------------------"
    
    for result in "${TEST_SUITE_RESULTS[@]}"; do
        IFS='|' read -r suite_name status duration details <<< "$result"
        printf "%-25s %-10s %-10s %s\n" "$suite_name" "$status" "${duration}s" "$details"
    done
    
    echo ""
    
    # Recommendations
    print_status "$BOLD$BLUE" "🎯 Recommendations:"
    echo "=================="
    
    if [ $failed_suites -eq 0 ] && [ $warning_suites -eq 0 ]; then
        print_status "$GREEN" "🎉 Excellent! All test suites passed successfully."
        echo "   Your TOC-SIS system is ready for production deployment."
        echo "   Consider running regular workflow tests to maintain quality."
    elif [ $failed_suites -eq 0 ]; then
        print_status "$YELLOW" "👍 Good! All critical tests passed, but review warnings."
        echo "   Address warning conditions to improve system robustness."
        echo "   The system is generally ready for production use."
    elif [ "$success_rate" = "$(echo "$success_rate >= 70" | bc -l)" ]; then
        print_status "$YELLOW" "⚠️  Partial success with $success_rate% pass rate."
        echo "   Address failed test suites before production deployment."
        echo "   Core functionality appears to be working correctly."
    else
        print_status "$RED" "❌ Critical issues require immediate attention:"
        echo "   1. Review and fix all failed test suites"
        echo "   2. Verify database schema and relationships"
        echo "   3. Check service configurations and dependencies"
        echo "   4. Re-run testing after addressing issues"
    fi
    
    echo ""
    
    if [ "$GENERATE_REPORTS" = "true" ]; then
        print_status "$BLUE" "📄 Detailed reports available in: storage/reports/workflow-testing/"
    fi
    
    # Return appropriate exit code
    if [ $failed_suites -gt 0 ]; then
        exit 1
    elif [ $warning_suites -gt 0 ]; then
        exit 2
    else
        exit 0
    fi
}

# Main execution function
main() {
    echo "Starting comprehensive workflow testing orchestration..."
    echo "Configuration:"
    echo "  Test Suite: $TEST_SUITE"
    echo "  Data Size: $DATA_SIZE"
    echo "  Reset Data: $RESET_DATA"
    echo "  Parallel Mode: $PARALLEL_MODE"
    echo "  Generate Reports: $GENERATE_REPORTS"
    echo "  Cleanup After: $CLEANUP_AFTER"
    echo ""
    
    # Run test sequence based on suite type
    check_prerequisites
    
    case "$TEST_SUITE" in
        "full")
            prepare_test_environment
            run_schema_validation
            run_architecture_validation
            run_workflow_validation
            run_workflow_automation
            run_performance_testing
            run_integration_testing
            run_stress_testing
            generate_comprehensive_reports
            cleanup_test_environment
            ;;
        "quick")
            prepare_test_environment
            run_schema_validation
            run_workflow_validation
            run_workflow_automation
            generate_comprehensive_reports
            ;;
        "validation-only")
            prepare_test_environment
            run_schema_validation
            run_architecture_validation
            run_workflow_validation
            generate_comprehensive_reports
            ;;
        "performance-only")
            prepare_test_environment
            run_performance_testing
            run_stress_testing
            generate_comprehensive_reports
            ;;
        "automation-only")
            prepare_test_environment
            run_workflow_automation
            generate_comprehensive_reports
            ;;
        *)
            print_status "$RED" "❌ Invalid test suite: $TEST_SUITE"
            echo "Valid options: full, quick, validation-only, performance-only, automation-only"
            exit 1
            ;;
    esac
    
    generate_final_summary
}

# Handle script arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Test Suites:"
        echo "  --full              Run complete test suite (default)"
        echo "  --quick             Run essential tests only"
        echo "  --validation-only   Run validation tests only"
        echo "  --performance-only  Run performance tests only"
        echo "  --automation-only   Run workflow automation only"
        echo ""
        echo "Options:"
        echo "  --help, -h          Show this help message"
        echo "  --reset-data        Reset test data before testing"
        echo "  --parallel          Run tests in parallel mode"
        echo "  --no-reports        Skip report generation"
        echo "  --cleanup           Cleanup test data after completion"
        echo "  --size SIZE         Test data size: small, medium, large, bulk"
        echo ""
        echo "Environment Variables:"
        echo "  TEST_SUITE          Test suite to run"
        echo "  DATA_SIZE           Test data size"
        echo "  RESET_DATA          Reset test data: true/false"
        echo "  PARALLEL_MODE       Parallel execution: true/false"
        echo "  GENERATE_REPORTS    Generate reports: true/false"
        echo "  CLEANUP_AFTER       Cleanup after testing: true/false"
        echo ""
        echo "Examples:"
        echo "  $0 --quick                      # Quick test run"
        echo "  $0 --full --reset-data          # Full test with fresh data"
        echo "  $0 --performance-only --size large  # Performance testing with large dataset"
        exit 0
        ;;
    --full)
        TEST_SUITE="full"
        main
        ;;
    --quick)
        TEST_SUITE="quick"
        main
        ;;
    --validation-only)
        TEST_SUITE="validation-only"
        main
        ;;
    --performance-only)
        TEST_SUITE="performance-only"
        main
        ;;
    --automation-only)
        TEST_SUITE="automation-only"
        main
        ;;
    --reset-data)
        RESET_DATA=true
        main
        ;;
    --parallel)
        PARALLEL_MODE=true
        main
        ;;
    --no-reports)
        GENERATE_REPORTS=false
        main
        ;;
    --cleanup)
        CLEANUP_AFTER=true
        main
        ;;
    --size)
        DATA_SIZE="$2"
        shift 2
        main
        ;;
    *)
        main
        ;;
esac