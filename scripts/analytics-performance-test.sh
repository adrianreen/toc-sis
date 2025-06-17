#!/bin/bash

# TOC-SIS Analytics System - Performance Testing Script
# This script tests the performance characteristics of the analytics system

set -e

echo "========================================="
echo "TOC-SIS Analytics Performance Testing"
echo "========================================="

# Configuration
BASE_URL="${BASE_URL:-http://localhost:8000}"
CONCURRENT_USERS=${CONCURRENT_USERS:-5}
REQUESTS_PER_USER=${REQUESTS_PER_USER:-10}
RESPONSE_TIME_LIMIT=${RESPONSE_TIME_LIMIT:-2000}  # 2 seconds in milliseconds

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Test results
PERFORMANCE_RESULTS=()

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}$message${NC}"
}

# Function to measure execution time
measure_time() {
    local command="$1"
    local start_time=$(date +%s%N)
    eval "$command"
    local end_time=$(date +%s%N)
    local duration=$(( (end_time - start_time) / 1000000 ))  # Convert to milliseconds
    echo "$duration"
}

# Function to test database query performance
test_database_performance() {
    print_status "$BLUE" "=== Database Query Performance Tests ==="
    
    echo "Creating test dataset for performance testing..."
    php artisan tinker --execute="
        // Create substantial test data
        if (App\Models\Student::count() < 100) {
            App\Models\Student::factory()->count(100)->create();
            echo 'Created 100 students' . PHP_EOL;
        }
        if (App\Models\Programme::count() < 20) {
            App\Models\Programme::factory()->count(20)->create();
            echo 'Created 20 programmes' . PHP_EOL;
        }
    " 2>/dev/null

    echo ""
    echo "Testing analytics service query performance..."
    
    # Test system overview performance
    local system_overview_time=$(measure_time "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getSystemOverview();' 2>/dev/null")
    printf "System Overview Query: %d ms " "$system_overview_time"
    if [ "$system_overview_time" -lt "$RESPONSE_TIME_LIMIT" ]; then
        print_status "$GREEN" "✅ PASS"
    else
        print_status "$RED" "❌ SLOW"
    fi
    
    # Test student performance trends
    local performance_time=$(measure_time "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getStudentPerformanceTrends();' 2>/dev/null")
    printf "Student Performance Query: %d ms " "$performance_time"
    if [ "$performance_time" -lt "$RESPONSE_TIME_LIMIT" ]; then
        print_status "$GREEN" "✅ PASS"
    else
        print_status "$RED" "❌ SLOW"
    fi
    
    # Test programme effectiveness
    local effectiveness_time=$(measure_time "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getProgrammeEffectiveness();' 2>/dev/null")
    printf "Programme Effectiveness Query: %d ms " "$effectiveness_time"
    if [ "$effectiveness_time" -lt 3000 ]; then  # Allow 3 seconds for this complex query
        print_status "$GREEN" "✅ PASS"
    else
        print_status "$RED" "❌ SLOW"
    fi
    
    PERFORMANCE_RESULTS+=("Database|System Overview|${system_overview_time}ms")
    PERFORMANCE_RESULTS+=("Database|Student Performance|${performance_time}ms")
    PERFORMANCE_RESULTS+=("Database|Programme Effectiveness|${effectiveness_time}ms")
}

# Function to test API endpoint performance
test_api_performance() {
    print_status "$BLUE" "=== API Endpoint Performance Tests ==="
    
    local endpoints=(
        "/api/analytics/system-overview"
        "/api/analytics/student-performance"
        "/api/analytics/programme-effectiveness"
        "/api/analytics/assessment-completion"
        "/api/analytics/student-engagement"
    )
    
    for endpoint in "${endpoints[@]}"; do
        echo "Testing $endpoint..."
        
        # Warm up the endpoint
        curl -s --max-time 10 "$BASE_URL$endpoint" >/dev/null 2>&1 || true
        
        # Measure response time
        local response_time=$(curl -s -w "%{time_total}" -o /dev/null --max-time 10 "$BASE_URL$endpoint" 2>/dev/null || echo "timeout")
        
        if [ "$response_time" = "timeout" ]; then
            printf "  %s: TIMEOUT " "$endpoint"
            print_status "$RED" "❌ FAIL"
            PERFORMANCE_RESULTS+=("API|$endpoint|TIMEOUT")
        else
            local time_ms=$(echo "$response_time * 1000" | bc -l | cut -d. -f1)
            printf "  %s: %d ms " "$endpoint" "$time_ms"
            
            if [ "$time_ms" -lt "$RESPONSE_TIME_LIMIT" ]; then
                print_status "$GREEN" "✅ PASS"
            else
                print_status "$RED" "❌ SLOW"
            fi
            PERFORMANCE_RESULTS+=("API|$endpoint|${time_ms}ms")
        fi
    done
}

# Function to test chart data performance
test_chart_performance() {
    print_status "$BLUE" "=== Chart Data Performance Tests ==="
    
    local chart_types=(
        "student_performance"
        "programme_effectiveness"
        "assessment_completion"
        "student_engagement"
    )
    
    for chart_type in "${chart_types[@]}"; do
        echo "Testing chart data: $chart_type..."
        
        local chart_time=$(measure_time "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getChartData(\"$chart_type\");' 2>/dev/null")
        printf "  Chart $chart_type: %d ms " "$chart_time"
        
        if [ "$chart_time" -lt "$RESPONSE_TIME_LIMIT" ]; then
            print_status "$GREEN" "✅ PASS"
        else
            print_status "$RED" "❌ SLOW"
        fi
        
        PERFORMANCE_RESULTS+=("Chart|$chart_type|${chart_time}ms")
    done
}

# Function to test cache performance
test_cache_performance() {
    print_status "$BLUE" "=== Cache Performance Tests ==="
    
    # Clear cache first
    php artisan tinker --execute="App\Models\AnalyticsCache::clearAll();" 2>/dev/null
    
    # Test cache miss (first call)
    echo "Testing cache miss performance..."
    local cache_miss_time=$(measure_time "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getSystemOverview();' 2>/dev/null")
    printf "Cache Miss (System Overview): %d ms " "$cache_miss_time"
    if [ "$cache_miss_time" -lt 5000 ]; then  # Allow 5 seconds for cache miss
        print_status "$GREEN" "✅ PASS"
    else
        print_status "$RED" "❌ SLOW"
    fi
    
    # Test cache hit (second call)
    echo "Testing cache hit performance..."
    local cache_hit_time=$(measure_time "php artisan tinker --execute='app(App\\Services\\AnalyticsService::class)->getSystemOverview();' 2>/dev/null")
    printf "Cache Hit (System Overview): %d ms " "$cache_hit_time"
    if [ "$cache_hit_time" -lt 100 ]; then  # Cache hit should be very fast
        print_status "$GREEN" "✅ PASS"
    else
        print_status "$YELLOW" "⚠️  SLOW"
    fi
    
    # Calculate cache improvement
    local improvement=$(( (cache_miss_time - cache_hit_time) * 100 / cache_miss_time ))
    echo "Cache Performance Improvement: ${improvement}%"
    
    PERFORMANCE_RESULTS+=("Cache|Cache Miss|${cache_miss_time}ms")
    PERFORMANCE_RESULTS+=("Cache|Cache Hit|${cache_hit_time}ms")
    PERFORMANCE_RESULTS+=("Cache|Improvement|${improvement}%")
}

# Function to test concurrent load
test_concurrent_load() {
    print_status "$BLUE" "=== Concurrent Load Testing ==="
    
    echo "Testing $CONCURRENT_USERS concurrent users with $REQUESTS_PER_USER requests each..."
    
    # Create temporary directory for results
    local temp_dir=$(mktemp -d)
    
    # Function to run concurrent requests
    run_concurrent_requests() {
        local user_id=$1
        local results_file="$temp_dir/user_${user_id}_results.txt"
        
        for ((i=1; i<=REQUESTS_PER_USER; i++)); do
            local start_time=$(date +%s%N)
            local http_code=$(curl -s -w "%{http_code}" -o /dev/null --max-time 10 "$BASE_URL/api/analytics/system-overview" 2>/dev/null || echo "000")
            local end_time=$(date +%s%N)
            local duration=$(( (end_time - start_time) / 1000000 ))
            
            echo "$user_id,$i,$http_code,$duration" >> "$results_file"
        done
    }
    
    # Start concurrent users
    local pids=()
    for ((user=1; user<=CONCURRENT_USERS; user++)); do
        run_concurrent_requests "$user" &
        pids+=($!)
    done
    
    # Wait for all users to complete
    for pid in "${pids[@]}"; do
        wait "$pid"
    done
    
    # Analyze results
    local total_requests=0
    local successful_requests=0
    local total_time=0
    local max_time=0
    local min_time=999999
    
    for results_file in "$temp_dir"/*_results.txt; do
        while IFS=',' read -r user_id request_id http_code duration; do
            ((total_requests++))
            if [ "$http_code" = "200" ]; then
                ((successful_requests++))
            fi
            total_time=$((total_time + duration))
            if [ "$duration" -gt "$max_time" ]; then
                max_time=$duration
            fi
            if [ "$duration" -lt "$min_time" ]; then
                min_time=$duration
            fi
        done < "$results_file"
    done
    
    # Calculate statistics
    local success_rate=$(( successful_requests * 100 / total_requests ))
    local avg_time=$(( total_time / total_requests ))
    
    echo ""
    echo "Concurrent Load Test Results:"
    echo "  Total Requests: $total_requests"
    echo "  Successful Requests: $successful_requests"
    echo "  Success Rate: ${success_rate}%"
    echo "  Average Response Time: ${avg_time}ms"
    echo "  Min Response Time: ${min_time}ms"
    echo "  Max Response Time: ${max_time}ms"
    
    # Evaluate performance
    if [ "$success_rate" -ge 95 ] && [ "$avg_time" -lt "$RESPONSE_TIME_LIMIT" ]; then
        print_status "$GREEN" "✅ Concurrent load test PASSED"
    else
        print_status "$RED" "❌ Concurrent load test FAILED"
    fi
    
    # Cleanup
    rm -rf "$temp_dir"
    
    PERFORMANCE_RESULTS+=("Load|Success Rate|${success_rate}%")
    PERFORMANCE_RESULTS+=("Load|Average Time|${avg_time}ms")
    PERFORMANCE_RESULTS+=("Load|Max Time|${max_time}ms")
}

# Function to test memory usage
test_memory_usage() {
    print_status "$BLUE" "=== Memory Usage Tests ==="
    
    echo "Testing memory usage for analytics operations..."
    
    local memory_result=$(php artisan tinker --execute="
        \$memoryBefore = memory_get_usage(true);
        \$peakBefore = memory_get_peak_usage(true);
        
        \$service = app(App\\Services\\AnalyticsService::class);
        \$overview = \$service->getSystemOverview();
        \$performance = \$service->getStudentPerformanceTrends();
        \$effectiveness = \$service->getProgrammeEffectiveness();
        \$completion = \$service->getAssessmentCompletionRates();
        \$engagement = \$service->getStudentEngagement();
        
        \$memoryAfter = memory_get_usage(true);
        \$peakAfter = memory_get_peak_usage(true);
        
        \$memoryUsed = \$memoryAfter - \$memoryBefore;
        \$peakIncrease = \$peakAfter - \$peakBefore;
        
        echo 'Memory Used: ' . number_format(\$memoryUsed / 1024 / 1024, 2) . ' MB' . PHP_EOL;
        echo 'Peak Increase: ' . number_format(\$peakIncrease / 1024 / 1024, 2) . ' MB' . PHP_EOL;
        echo 'Current Memory: ' . number_format(\$memoryAfter / 1024 / 1024, 2) . ' MB' . PHP_EOL;
        
        if (\$memoryUsed > 256 * 1024 * 1024) { // 256MB limit
            exit(1);
        }
    " 2>/dev/null)
    
    echo "$memory_result"
    
    if [ $? -eq 0 ]; then
        print_status "$GREEN" "✅ Memory usage within acceptable limits"
    else
        print_status "$RED" "❌ Memory usage too high"
    fi
}

# Function to test analytics command performance
test_command_performance() {
    print_status "$BLUE" "=== Analytics Command Performance ==="
    
    echo "Testing analytics:compute command performance..."
    
    local command_time=$(measure_time "php artisan analytics:compute 2>/dev/null")
    printf "Analytics Compute Command: %d ms " "$command_time"
    
    if [ "$command_time" -lt 10000 ]; then  # Allow 10 seconds for command
        print_status "$GREEN" "✅ PASS"
    else
        print_status "$RED" "❌ SLOW"
    fi
    
    PERFORMANCE_RESULTS+=("Command|Analytics Compute|${command_time}ms")
}

# Function to generate performance report
generate_performance_report() {
    print_status "$BLUE" "=== Performance Test Report ==="
    
    echo ""
    echo "Detailed Performance Results:"
    echo "=============================="
    printf "%-12s %-25s %-15s\n" "Category" "Test" "Result"
    echo "------------------------------------------------------"
    
    for result in "${PERFORMANCE_RESULTS[@]}"; do
        IFS='|' read -r category test_name result_value <<< "$result"
        printf "%-12s %-25s %-15s\n" "$category" "$test_name" "$result_value"
    done
    
    echo ""
    echo "Performance Recommendations:"
    echo "============================"
    echo "1. API endpoints should respond in < ${RESPONSE_TIME_LIMIT}ms"
    echo "2. Cache hits should be < 100ms"
    echo "3. Database queries should be optimized with proper indexes"
    echo "4. Memory usage should stay under 256MB per request"
    echo "5. Concurrent load success rate should be > 95%"
    echo ""
    
    # Save detailed report
    local report_file="/var/www/toc-sis/storage/logs/analytics-performance-$(date +%Y%m%d-%H%M%S).log"
    {
        echo "TOC-SIS Analytics Performance Test Report"
        echo "Generated: $(date)"
        echo "Configuration:"
        echo "  Base URL: $BASE_URL"
        echo "  Response Time Limit: ${RESPONSE_TIME_LIMIT}ms"
        echo "  Concurrent Users: $CONCURRENT_USERS"
        echo "  Requests per User: $REQUESTS_PER_USER"
        echo ""
        echo "Results:"
        for result in "${PERFORMANCE_RESULTS[@]}"; do
            echo "$result"
        done
    } > "$report_file"
    
    echo "Detailed report saved to: $report_file"
}

# Main execution
main() {
    echo "Starting performance testing..."
    echo "Configuration:"
    echo "  Base URL: $BASE_URL"
    echo "  Response Time Limit: ${RESPONSE_TIME_LIMIT}ms"
    echo "  Concurrent Users: $CONCURRENT_USERS"
    echo "  Requests per User: $REQUESTS_PER_USER"
    echo ""
    
    # Check if application is running
    if ! curl -s --max-time 5 "$BASE_URL" >/dev/null 2>&1; then
        print_status "$RED" "❌ Laravel application not accessible at $BASE_URL"
        echo "Please start the application with: php artisan serve"
        exit 1
    fi
    
    # Run performance tests
    test_database_performance
    echo ""
    test_api_performance
    echo ""
    test_chart_performance
    echo ""
    test_cache_performance
    echo ""
    test_memory_usage
    echo ""
    test_command_performance
    echo ""
    test_concurrent_load
    echo ""
    
    # Generate final report
    generate_performance_report
}

# Handle script arguments
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --help, -h     Show this help message"
        echo "  --quick        Run basic performance tests only"
        echo "  --load-only    Run only concurrent load tests"
        echo ""
        echo "Environment Variables:"
        echo "  BASE_URL              Application URL (default: http://localhost:8000)"
        echo "  RESPONSE_TIME_LIMIT   Response time limit in ms (default: 2000)"
        echo "  CONCURRENT_USERS      Number of concurrent users (default: 5)"
        echo "  REQUESTS_PER_USER     Requests per user (default: 10)"
        exit 0
        ;;
    --quick)
        echo "Running quick performance tests..."
        test_database_performance
        test_api_performance
        test_cache_performance
        generate_performance_report
        ;;
    --load-only)
        echo "Running load tests only..."
        test_concurrent_load
        generate_performance_report
        ;;
    *)
        main
        ;;
esac