#!/bin/bash

# TOC-SIS Analytics System - Quick Smoke Test
# This script performs a rapid health check of the analytics system

set -e

echo "========================================="
echo "TOC-SIS Analytics System - Smoke Test"
echo "========================================="

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Function to print status
print_result() {
    local status=$1
    local message=$2
    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}✅ $message${NC}"
        ((TESTS_PASSED++))
    else
        echo -e "${RED}❌ $message${NC}"
        ((TESTS_FAILED++))
    fi
}

# Function to run smoke test
smoke_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo -n "Testing $test_name... "
    if eval "$test_command" >/dev/null 2>&1; then
        print_result "PASS" "$test_name"
        return 0
    else
        print_result "FAIL" "$test_name"
        return 1
    fi
}

echo "Running quick smoke tests..."
echo ""

# 1. Test Laravel application is running
echo "1. Checking Laravel application..."
if curl -s --max-time 5 http://localhost:8000 >/dev/null 2>&1; then
    print_result "PASS" "Laravel application is running"
else
    print_result "FAIL" "Laravel application is not accessible"
    echo "Please start the application with: php artisan serve"
    exit 1
fi

# 2. Test database connection
echo "2. Checking database connection..."
smoke_test "Database connection" "php artisan tinker --execute='DB::connection()->getPdo();'"

# 3. Test analytics service loads
echo "3. Testing analytics service..."
smoke_test "Service instantiation" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class);'"

# 4. Test analytics models
echo "4. Testing analytics models..."
smoke_test "Analytics models" "php artisan tinker --execute='App\\Models\\AnalyticsCache::count(); App\\Models\\AnalyticsMetric::count();'"

# 5. Test system overview generation
echo "5. Testing system overview..."
smoke_test "System overview generation" "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getSystemOverview();'"

# 6. Test cache functionality
echo "6. Testing cache functionality..."
smoke_test "Cache operations" "php artisan tinker --execute='App\\Models\\AnalyticsCache::setCached(\"smoke_test\", [\"data\" => true]); \$result = App\\Models\\AnalyticsCache::getCached(\"smoke_test\"); if (!\$result || !\$result[\"data\"]) exit(1);'"

# 7. Test analytics command
echo "7. Testing analytics command..."
smoke_test "Analytics compute command" "php artisan analytics:compute"

# 8. Test API endpoints (if accessible)
echo "8. Testing API endpoints..."
if curl -s --max-time 5 http://localhost:8000/api/analytics/system-overview | grep -q "students" 2>/dev/null; then
    print_result "PASS" "API endpoints accessible"
else
    print_result "FAIL" "API endpoints not accessible (may require authentication)"
fi

# 9. Test chart data generation
echo "9. Testing chart data..."
smoke_test "Chart data generation" "php artisan tinker --execute='\$service = app(App\\Services\\AnalyticsService::class); \$chart = \$service->getChartData(\"student_performance\"); if (!isset(\$chart[\"type\"]) || !isset(\$chart[\"data\"])) exit(1);'"

# 10. Test cache cleanup
echo "10. Testing cache cleanup..."
smoke_test "Cache cleanup" "php artisan tinker --execute='App\\Models\\AnalyticsCache::clearExpired();'"

echo ""
echo "========================================="
echo "SMOKE TEST SUMMARY"
echo "========================================="
echo "Tests Passed: $TESTS_PASSED"
echo "Tests Failed: $TESTS_FAILED"
echo "Total Tests: $((TESTS_PASSED + TESTS_FAILED))"

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 ALL SMOKE TESTS PASSED!${NC}"
    echo "The analytics system is functional and ready for detailed testing."
    echo ""
    echo "Next steps:"
    echo "  - Run full test suite: ./scripts/run-analytics-tests.sh"
    echo "  - Check browser-based charts and UI components"
    echo "  - Verify role-based access controls"
    exit 0
else
    echo -e "${RED}⚠️  $TESTS_FAILED SMOKE TESTS FAILED${NC}"
    echo "Please fix the basic issues before running the full test suite."
    echo ""
    echo "Common fixes:"
    echo "  - Ensure Laravel application is running: php artisan serve"
    echo "  - Check database connectivity and migrations"
    echo "  - Verify analytics models and services are properly installed"
    exit 1
fi