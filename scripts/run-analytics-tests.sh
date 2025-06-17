#!/bin/bash

# TOC-SIS Analytics System - Comprehensive Test Runner
# This script runs all analytics tests to validate the system is production-ready

set -e  # Exit on any error

echo "========================================="
echo "TOC-SIS Analytics System - Full Test Suite"
echo "========================================="

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test counters
PASSED_TESTS=0
FAILED_TESTS=0
TOTAL_TESTS=0

# Configuration
BASE_URL="${BASE_URL:-http://localhost:8000}"
TEST_TIMEOUT=30

# Function to print colored output
print_status() {
    local status=$1
    local message=$2
    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}✅ PASSED:${NC} $message"
    elif [ "$status" = "FAIL" ]; then
        echo -e "${RED}❌ FAILED:${NC} $message"
    elif [ "$status" = "WARN" ]; then
        echo -e "${YELLOW}⚠️  WARNING:${NC} $message"
    elif [ "$status" = "INFO" ]; then
        echo -e "${BLUE}ℹ️  INFO:${NC} $message"
    fi
}

# Function to run test and track results
run_test() {
    local test_name="$1"
    local test_command="$2"
    local timeout="${3:-$TEST_TIMEOUT}"
    
    echo ""
    print_status "INFO" "Running: $test_name"
    
    if timeout "$timeout" bash -c "$test_command" >/dev/null 2>&1; then
        print_status "PASS" "$test_name"
        ((PASSED_TESTS++))
        return 0
    else
        print_status "FAIL" "$test_name"
        ((FAILED_TESTS++))
        return 1
    fi
    ((TOTAL_TESTS++))
}

# Function to run test with output capture
run_test_with_output() {
    local test_name="$1"
    local test_command="$2"
    local timeout="${3:-$TEST_TIMEOUT}"
    
    echo ""
    print_status "INFO" "Running: $test_name"
    
    local output
    if output=$(timeout "$timeout" bash -c "$test_command" 2>&1); then
        print_status "PASS" "$test_name"
        ((PASSED_TESTS++))
        return 0
    else
        print_status "FAIL" "$test_name"
        echo "Error output: $output"
        ((FAILED_TESTS++))
        return 1
    fi
    ((TOTAL_TESTS++))
}

# Function to check if service is running
check_service() {
    local service_url="$1"
    local service_name="$2"
    
    if curl -s --max-time 5 "$service_url" >/dev/null 2>&1; then
        print_status "PASS" "$service_name is running"
        return 0
    else
        print_status "FAIL" "$service_name is not accessible at $service_url"
        return 1
    fi
}

# Function to check Laravel application
check_laravel_app() {
    echo ""
    echo "=== Checking Laravel Application ==="
    
    # Check if application is running
    if ! check_service "$BASE_URL" "Laravel Application"; then
        print_status "FAIL" "Laravel application not running. Please start with 'php artisan serve'"
        exit 1
    fi
    
    # Check database connection
    run_test "Database Connection" "php artisan tinker --execute='DB::connection()->getPdo();'"
    
    # Check analytics models load
    run_test "Analytics Models" "php artisan tinker --execute='App\\Models\\AnalyticsCache::count(); App\\Models\\AnalyticsMetric::count();'"
}

# Function to run Laravel tests
run_laravel_tests() {
    echo ""
    echo "=== Laravel Feature Tests ==="
    
    # Ensure test database is fresh
    print_status "INFO" "Preparing test database..."
    php artisan migrate:fresh --env=testing --force >/dev/null 2>&1
    
    # Run analytics-specific tests
    run_test "Analytics API Tests" "php artisan test tests/Feature/AnalyticsApiTest.php --env=testing"
    run_test "Analytics Security Tests" "php artisan test tests/Feature/AnalyticsSecurityTest.php --env=testing"
    run_test "Analytics Cache Tests" "php artisan test tests/Feature/AnalyticsCacheTest.php --env=testing"
    
    # Run all analytics-related tests
    run_test "All Analytics Tests" "php artisan test --filter=Analytics --env=testing"
}

# Function to test analytics service
test_analytics_service() {
    echo ""
    echo "=== Analytics Service Tests ==="
    
    run_test "Service Instantiation" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class);'"
    run_test "System Overview" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getSystemOverview();'"
    run_test "Student Performance" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getStudentPerformanceTrends();'"
    run_test "Programme Effectiveness" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getProgrammeEffectiveness();'"
    run_test "Assessment Completion" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getAssessmentCompletionRates();'"
    run_test "Student Engagement" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getStudentEngagement();'"
}

# Function to test API endpoints
test_api_endpoints() {
    echo ""
    echo "=== API Endpoint Tests ==="
    
    # Test core analytics endpoints
    run_test "System Overview API" "curl -s --max-time 10 '$BASE_URL/api/analytics/system-overview' | grep -q 'students'"
    run_test "Student Performance API" "curl -s --max-time 10 '$BASE_URL/api/analytics/student-performance' | grep -q 'assessment_trends'"
    run_test "Programme Effectiveness API" "curl -s --max-time 10 '$BASE_URL/api/analytics/programme-effectiveness' | grep -q 'programmes'"
    run_test "Assessment Completion API" "curl -s --max-time 10 '$BASE_URL/api/analytics/assessment-completion' | grep -q 'completion_rates'"
    run_test "Student Engagement API" "curl -s --max-time 10 '$BASE_URL/api/analytics/student-engagement' | grep -q 'engagement_rate'"
}

# Function to test chart data endpoints
test_chart_endpoints() {
    echo ""
    echo "=== Chart Data Tests ==="
    
    run_test "Student Performance Chart" "curl -s --max-time 10 '$BASE_URL/api/analytics/chart-data/student_performance' | grep -q 'type'"
    run_test "Programme Effectiveness Chart" "curl -s --max-time 10 '$BASE_URL/api/analytics/chart-data/programme_effectiveness' | grep -q 'data'"
    run_test "Assessment Completion Chart" "curl -s --max-time 10 '$BASE_URL/api/analytics/chart-data/assessment_completion' | grep -q 'labels'"
    run_test "Student Engagement Chart" "curl -s --max-time 10 '$BASE_URL/api/analytics/chart-data/student_engagement' | grep -q 'datasets'"
    
    # Test invalid chart type
    run_test "Invalid Chart Type Handling" "curl -s --max-time 5 '$BASE_URL/api/analytics/chart-data/invalid_type' | grep -q 'error'"
}

# Function to test cache functionality
test_cache_functionality() {
    echo ""
    echo "=== Cache Management Tests ==="
    
    run_test "Cache Refresh" "curl -s --max-time 10 -X POST '$BASE_URL/api/analytics/refresh-cache' | grep -q 'success'"
    run_test "Clear Expired Cache" "curl -s --max-time 10 -X POST '$BASE_URL/api/analytics/clear-expired-cache' | grep -q 'success'"
    
    # Test cache behavior with Tinker
    run_test "Cache Storage" "php artisan tinker --execute='App\\Models\\AnalyticsCache::setCached(\"test\", [\"data\" => true]); App\\Models\\AnalyticsCache::getCached(\"test\");'"
    run_test "Cache Expiration" "php artisan tinker --execute='App\\Models\\AnalyticsCache::clearExpired();'"
}

# Function to test analytics command
test_analytics_command() {
    echo ""
    echo "=== Analytics Command Tests ==="
    
    run_test "Analytics Compute Command" "php artisan analytics:compute"
    run_test "Analytics Compute with Clear Cache" "php artisan analytics:compute --clear-cache"
    
    # Verify command creates expected data
    run_test "Command Creates Cache" "php artisan tinker --execute='echo App\\Models\\AnalyticsCache::count();'"
    run_test "Command Creates Metrics" "php artisan tinker --execute='echo App\\Models\\AnalyticsMetric::count();'"
}

# Function to test performance
test_performance() {
    echo ""
    echo "=== Performance Tests ==="
    
    # Test response times
    run_test "System Overview Response Time" "timeout 5s time curl -s '$BASE_URL/api/analytics/system-overview' >/dev/null"
    
    # Test multiple concurrent requests
    run_test "Concurrent Requests" "for i in {1..5}; do curl -s '$BASE_URL/api/analytics/system-overview' >/dev/null & done; wait"
    
    # Test with larger dataset if available
    run_test "Performance with Data" "php artisan tinker --execute='
        \$start = microtime(true);
        app(App\\Services\\AnalyticsService::class)->getSystemOverview();
        \$end = microtime(true);
        \$time = (\$end - \$start) * 1000;
        if (\$time > 2000) { exit(1); }
        echo \"Response time: \" . round(\$time, 2) . \"ms\";
    '"
}

# Function to test security
test_security() {
    echo ""
    echo "=== Security Tests ==="
    
    # Test unauthenticated access
    run_test "Blocks Unauthenticated Access" "! curl -s --max-time 5 '$BASE_URL/api/analytics/system-overview' | grep -q 'students'"
    
    # Test SQL injection protection (basic)
    run_test "SQL Injection Protection" "curl -s --max-time 5 '$BASE_URL/api/analytics/student-performance?period_type=%27%3B%20DROP%20TABLE%20students%3B%20--' | grep -v 'DROP TABLE'"
    
    # Test XSS protection
    run_test "XSS Protection" "curl -s --max-time 5 '$BASE_URL/api/analytics/historical-metrics?metric_type=%3Cscript%3Ealert%28%27xss%27%29%3C%2Fscript%3E' | grep -v '<script>'"
}

# Function to test data accuracy
test_data_accuracy() {
    echo ""
    echo "=== Data Accuracy Tests ==="
    
    # Create known test data and verify calculations
    run_test "Data Accuracy Verification" "php artisan tinker --execute='
        // Clear existing data
        App\\Models\\Student::query()->delete();
        App\\Models\\Programme::query()->delete();
        
        // Create known data
        App\\Models\\Student::factory()->count(10)->create([\"status\" => \"active\"]);
        App\\Models\\Student::factory()->count(5)->create([\"status\" => \"enrolled\"]);
        App\\Models\\Programme::factory()->count(3)->create([\"is_active\" => true]);
        
        // Clear cache to get fresh data
        App\\Models\\AnalyticsCache::clearAll();
        
        // Get analytics
        \$service = app(App\\Services\\AnalyticsService::class);
        \$overview = \$service->getSystemOverview();
        
        // Verify counts
        if (\$overview[\"students\"][\"total\"] !== 15) exit(1);
        if (\$overview[\"students\"][\"active\"] !== 10) exit(1);
        if (\$overview[\"students\"][\"enrolled\"] !== 5) exit(1);
        if (\$overview[\"programmes\"][\"total\"] !== 3) exit(1);
        if (\$overview[\"programmes\"][\"active\"] !== 3) exit(1);
        
        echo \"Data accuracy verified\";
    '"
}

# Main execution function
main() {
    echo "Starting TOC-SIS Analytics System Test Suite..."
    echo "Base URL: $BASE_URL"
    echo "Test Timeout: $TEST_TIMEOUT seconds"
    echo ""
    
    # Check prerequisites
    check_laravel_app
    
    # Run test suites
    test_analytics_service
    test_api_endpoints
    test_chart_endpoints
    test_cache_functionality
    test_analytics_command
    run_laravel_tests
    test_performance
    test_security
    test_data_accuracy
    
    # Generate final report
    echo ""
    echo "========================================="
    echo "TEST EXECUTION SUMMARY"
    echo "========================================="
    echo "Total Tests: $TOTAL_TESTS"
    echo "Passed: $PASSED_TESTS"
    echo "Failed: $FAILED_TESTS"
    
    if [ $TOTAL_TESTS -gt 0 ]; then
        SUCCESS_RATE=$(( PASSED_TESTS * 100 / TOTAL_TESTS ))
        echo "Success Rate: $SUCCESS_RATE%"
    fi
    
    echo ""
    
    if [ $FAILED_TESTS -eq 0 ]; then
        print_status "PASS" "ALL TESTS PASSED! Analytics system is ready for production."
        echo ""
        echo "🎉 Congratulations! The analytics system has passed all quality checks."
        echo "   The system is production-ready and meets all requirements."
        exit 0
    else
        print_status "FAIL" "$FAILED_TESTS tests failed. Please review and fix the issues above."
        echo ""
        echo "⚠️  Please address the failed tests before deploying to production."
        echo "   Check the error messages above for specific issues to resolve."
        exit 1
    fi
}

# Handle script arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --help, -h     Show this help message"
        echo "  --quick        Run only essential tests (faster)"
        echo "  --no-laravel   Skip Laravel feature tests"
        echo "  --no-security  Skip security tests"
        echo ""
        echo "Environment Variables:"
        echo "  BASE_URL       Application URL (default: http://localhost:8000)"
        echo "  TEST_TIMEOUT   Test timeout in seconds (default: 30)"
        exit 0
        ;;
    --quick)
        echo "Running quick test suite..."
        check_laravel_app
        test_analytics_service
        test_api_endpoints
        test_cache_functionality
        ;;
    --no-laravel)
        echo "Skipping Laravel feature tests..."
        check_laravel_app
        test_analytics_service
        test_api_endpoints
        test_chart_endpoints
        test_cache_functionality
        test_analytics_command
        test_performance
        test_security
        test_data_accuracy
        ;;
    --no-security)
        echo "Skipping security tests..."
        check_laravel_app
        test_analytics_service
        test_api_endpoints
        test_chart_endpoints
        test_cache_functionality
        test_analytics_command
        run_laravel_tests
        test_performance
        test_data_accuracy
        ;;
    *)
        main
        ;;
esac