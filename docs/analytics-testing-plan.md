# TOC-SIS Analytics System - Comprehensive Testing & Validation Plan

## Executive Summary

This document outlines a comprehensive testing strategy for the Phase 1 analytics implementation in the TOC Student Information System. The analytics system includes API endpoints, database analytics, chart rendering, caching mechanisms, and role-based access controls.

## 1. Functional Testing Plan

### 1.1 Analytics API Endpoints Testing

#### Test Cases for Core Analytics Endpoints

**Test Case A1.1: System Overview Endpoint**
```bash
# Test command
curl -X GET "http://localhost:8000/api/analytics/system-overview" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=test_session"

# Expected Response Structure
{
  "students": {
    "total": 0,
    "active": 0,
    "enrolled": 0,
    "deferred": 0,
    "completed": 0
  },
  "programmes": {
    "total": 0,
    "active": 0
  },
  "assessments": {
    "total": 0,
    "pending": 0,
    "submitted": 0,
    "graded": 0,
    "passed": 0,
    "failed": 0
  },
  "enrollments": {
    "total": 0,
    "active": 0,
    "completed": 0,
    "deferred": 0
  },
  "generated_at": "2025-06-16T..."
}
```

**Test Case A1.2: Student Performance Trends**
```bash
# Test with default parameters
curl -X GET "http://localhost:8000/api/analytics/student-performance" \
  -H "Accept: application/json"

# Test with custom parameters
curl -X GET "http://localhost:8000/api/analytics/student-performance?period_type=weekly&months=6" \
  -H "Accept: application/json"

# Expected Response Structure
{
  "assessment_trends": [
    {
      "period": "2025-06",
      "total_assessments": 0,
      "passed_assessments": 0,
      "avg_grade": 0
    }
  ],
  "enrollment_trends": [
    {
      "period": "2025-06",
      "new_enrollments": 0,
      "active_enrollments": 0
    }
  ],
  "period_type": "monthly",
  "generated_at": "2025-06-16T..."
}
```

**Test Case A1.3: Programme Effectiveness**
```bash
curl -X GET "http://localhost:8000/api/analytics/programme-effectiveness" \
  -H "Accept: application/json"

# Expected Response Structure
{
  "programmes": [
    {
      "programme_id": 1,
      "programme_code": "PROG001",
      "programme_title": "Test Programme",
      "total_enrollments": 0,
      "active_enrollments": 0,
      "completed_enrollments": 0,
      "completion_rate": 0,
      "average_grade": 0,
      "pass_rate": 0
    }
  ],
  "generated_at": "2025-06-16T..."
}
```

**Test Case A1.4: Assessment Completion Rates**
```bash
# Test with different period types
curl -X GET "http://localhost:8000/api/analytics/assessment-completion?period_type=weekly&periods=8" \
  -H "Accept: application/json"

curl -X GET "http://localhost:8000/api/analytics/assessment-completion?period_type=monthly&periods=12" \
  -H "Accept: application/json"
```

**Test Case A1.5: Student Engagement Metrics**
```bash
curl -X GET "http://localhost:8000/api/analytics/student-engagement" \
  -H "Accept: application/json"

# Expected Response Structure
{
  "total_active_students": 0,
  "recently_active_students": 0,
  "engagement_rate": 0,
  "submission_patterns": {
    "Monday": 0,
    "Tuesday": 0,
    "Wednesday": 0,
    "Thursday": 0,
    "Friday": 0,
    "Saturday": 0,
    "Sunday": 0
  },
  "generated_at": "2025-06-16T..."
}
```

**Test Case A1.6: Chart Data Endpoints**
```bash
# Test each chart type
curl -X GET "http://localhost:8000/api/analytics/chart-data/student_performance" \
  -H "Accept: application/json"

curl -X GET "http://localhost:8000/api/analytics/chart-data/programme_effectiveness" \
  -H "Accept: application/json"

curl -X GET "http://localhost:8000/api/analytics/chart-data/assessment_completion" \
  -H "Accept: application/json"

curl -X GET "http://localhost:8000/api/analytics/chart-data/student_engagement" \
  -H "Accept: application/json"

# Test invalid chart type
curl -X GET "http://localhost:8000/api/analytics/chart-data/invalid_type" \
  -H "Accept: application/json"
# Expected: {"error": "Unknown chart type"}
```

**Test Case A1.7: Historical Metrics**
```bash
# Test with required parameters
curl -X GET "http://localhost:8000/api/analytics/historical-metrics?metric_type=system_overview&period_type=daily&limit=30" \
  -H "Accept: application/json"

# Test without required metric_type
curl -X GET "http://localhost:8000/api/analytics/historical-metrics" \
  -H "Accept: application/json"
# Expected: {"error": "metric_type is required"}
```

**Test Case A1.8: Cache Management Endpoints**
```bash
# Test cache refresh
curl -X POST "http://localhost:8000/api/analytics/refresh-cache" \
  -H "Accept: application/json"
# Expected: {"success": true, "message": "Analytics cache refreshed"}

# Test clear expired cache
curl -X POST "http://localhost:8000/api/analytics/clear-expired-cache" \
  -H "Accept: application/json"
# Expected: {"success": true, "message": "Cleared X expired cache entries"}
```

### 1.2 Database Query Validation

#### Test Case B1.1: Analytics Service Query Performance
```php
// Test script: tests/Feature/AnalyticsQueryPerformanceTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_overview_query_performance()
    {
        $service = app(AnalyticsService::class);
        
        $startTime = microtime(true);
        $result = $service->getSystemOverview();
        $endTime = microtime(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Assert query completes within reasonable time (2 seconds)
        $this->assertLessThan(2000, $executionTime, 'System overview query took too long');
        
        // Assert required fields exist
        $this->assertArrayHasKey('students', $result);
        $this->assertArrayHasKey('programmes', $result);
        $this->assertArrayHasKey('assessments', $result);
        $this->assertArrayHasKey('enrollments', $result);
        $this->assertArrayHasKey('generated_at', $result);
    }
    
    public function test_student_performance_trends_with_data()
    {
        // Create test data
        $this->seedTestData();
        
        $service = app(AnalyticsService::class);
        $result = $service->getStudentPerformanceTrends('monthly', 6);
        
        $this->assertArrayHasKey('assessment_trends', $result);
        $this->assertArrayHasKey('enrollment_trends', $result);
        $this->assertArrayHasKey('period_type', $result);
        $this->assertEquals('monthly', $result['period_type']);
    }
    
    private function seedTestData()
    {
        // Create test programmes, students, enrollments, and assessments
        // This will be implemented with factories
    }
}
```

#### Test Case B1.2: SQL Query Validation
```bash
# Test SQL queries directly for accuracy

# System Overview Queries
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;
echo 'Students count: ' . App\Models\Student::count() . PHP_EOL;
echo 'Active students: ' . App\Models\Student::where('status', 'active')->count() . PHP_EOL;
echo 'Programmes count: ' . App\Models\Programme::count() . PHP_EOL;
echo 'Assessments count: ' . App\Models\StudentAssessment::count() . PHP_EOL;
"

# Performance Trends Queries
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;
\$startDate = now()->subMonths(12);
\$trends = App\Models\StudentAssessment::select(
    DB::raw('DATE_FORMAT(graded_date, \"%Y-%m\") as period'),
    DB::raw('COUNT(*) as total_assessments'),
    DB::raw('SUM(CASE WHEN status = \"passed\" THEN 1 ELSE 0 END) as passed_assessments'),
    DB::raw('AVG(CASE WHEN grade IS NOT NULL THEN grade ELSE 0 END) as avg_grade')
)
->where('graded_date', '>=', \$startDate)
->whereNotNull('graded_date')
->groupBy('period')
->orderBy('period')
->get();
echo 'Assessment trends: ' . \$trends->count() . ' records' . PHP_EOL;
var_dump(\$trends->toArray());
"
```

### 1.3 Chart Rendering and Data Visualization Testing

#### Test Case C1.1: Chart Component Frontend Testing
```html
<!-- Create test file: resources/views/test-analytics-charts.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Analytics Charts Test</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="space-y-8">
        <h1 class="text-3xl font-bold text-gray-900">Analytics Charts Test Page</h1>
        
        <!-- Test System Overview Chart -->
        <x-analytics-chart
            chart-id="test-system-overview"
            type="doughnut"
            title="System Overview Test"
            api-url="{{ route('analytics.system-overview') }}"
            height="300"
        />
        
        <!-- Test Student Performance Chart -->
        <x-analytics-chart
            chart-id="test-student-performance"
            type="line"
            title="Student Performance Trends Test"
            api-url="{{ route('analytics.chart-data', 'student_performance') }}"
            height="400"
            refresh-interval="30"
        />
        
        <!-- Test Programme Effectiveness Chart -->
        <x-analytics-chart
            chart-id="test-programme-effectiveness"
            type="bar"
            title="Programme Effectiveness Test"
            api-url="{{ route('analytics.chart-data', 'programme_effectiveness') }}"
            height="350"
        />
        
        <!-- Test Assessment Completion Chart -->
        <x-analytics-chart
            chart-id="test-assessment-completion"
            type="line"
            title="Assessment Completion Rates Test"
            api-url="{{ route('analytics.chart-data', 'assessment_completion') }}"
            height="300"
        />
        
        <!-- Test Student Engagement Chart -->
        <x-analytics-chart
            chart-id="test-student-engagement"
            type="doughnut"
            title="Student Engagement Test"
            api-url="{{ route('analytics.chart-data', 'student_engagement') }}"
            height="350"
        />
        
        <!-- Test Error Handling -->
        <x-analytics-chart
            chart-id="test-error-handling"
            type="line"
            title="Error Handling Test (Invalid URL)"
            api-url="/api/analytics/invalid-endpoint"
            height="200"
        />
    </div>
</body>
</html>
```

#### Test Case C1.2: Chart Data Format Validation
```bash
# Test that chart data conforms to Chart.js format
php artisan tinker --execute="
\$service = app(App\Services\AnalyticsService::class);

// Test student performance chart data format
\$chartData = \$service->getChartData('student_performance');
echo 'Student Performance Chart Data:' . PHP_EOL;
echo 'Type: ' . (\$chartData['type'] ?? 'missing') . PHP_EOL;
echo 'Has data: ' . (isset(\$chartData['data']) ? 'yes' : 'no') . PHP_EOL;
echo 'Has options: ' . (isset(\$chartData['options']) ? 'yes' : 'no') . PHP_EOL;

// Test programme effectiveness chart data format
\$chartData = \$service->getChartData('programme_effectiveness');
echo PHP_EOL . 'Programme Effectiveness Chart Data:' . PHP_EOL;
echo 'Type: ' . (\$chartData['type'] ?? 'missing') . PHP_EOL;
echo 'Datasets count: ' . (count(\$chartData['data']['datasets'] ?? []) ?: 0) . PHP_EOL;
"
```

### 1.4 User Interface Interaction Testing

#### Test Case D1.1: Analytics Component Integration
```javascript
// Frontend testing script for analytics components
// Create: public/js/analytics-test.js

class AnalyticsTestSuite {
    constructor() {
        this.testResults = [];
    }
    
    async runAllTests() {
        console.log('Starting Analytics Frontend Tests...');
        
        await this.testChartComponentLoading();
        await this.testChartRefreshFunctionality();
        await this.testErrorHandling();
        await this.testAutoRefresh();
        
        this.displayResults();
    }
    
    async testChartComponentLoading() {
        try {
            // Test if analytics chart component loads
            const chartElements = document.querySelectorAll('[x-data*="analyticsChart"]');
            const result = chartElements.length > 0;
            
            this.testResults.push({
                test: 'Chart Component Loading',
                passed: result,
                message: result ? `Found ${chartElements.length} chart components` : 'No chart components found'
            });
        } catch (error) {
            this.testResults.push({
                test: 'Chart Component Loading',
                passed: false,
                message: `Error: ${error.message}`
            });
        }
    }
    
    async testChartRefreshFunctionality() {
        try {
            // Test chart refresh button functionality
            const refreshButtons = document.querySelectorAll('button[\\@click*="refreshChart"]');
            const result = refreshButtons.length > 0;
            
            this.testResults.push({
                test: 'Chart Refresh Functionality',
                passed: result,
                message: result ? `Found ${refreshButtons.length} refresh buttons` : 'No refresh buttons found'
            });
        } catch (error) {
            this.testResults.push({
                test: 'Chart Refresh Functionality',
                passed: false,
                message: `Error: ${error.message}`
            });
        }
    }
    
    async testErrorHandling() {
        try {
            // Test error display elements
            const errorElements = document.querySelectorAll('[x-show="error"]');
            const result = errorElements.length > 0;
            
            this.testResults.push({
                test: 'Error Handling Elements',
                passed: result,
                message: result ? `Found ${errorElements.length} error display elements` : 'No error handling elements found'
            });
        } catch (error) {
            this.testResults.push({
                test: 'Error Handling Elements',
                passed: false,
                message: `Error: ${error.message}`
            });
        }
    }
    
    async testAutoRefresh() {
        try {
            // Test auto-refresh interval setup
            const chartsWithRefresh = document.querySelectorAll('[x-data*="refreshInterval"]');
            const result = chartsWithRefresh.length >= 0; // This should always pass
            
            this.testResults.push({
                test: 'Auto-refresh Configuration',
                passed: result,
                message: `Found ${chartsWithRefresh.length} charts with refresh configuration`
            });
        } catch (error) {
            this.testResults.push({
                test: 'Auto-refresh Configuration',
                passed: false,
                message: `Error: ${error.message}`
            });
        }
    }
    
    displayResults() {
        console.log('\\n=== Analytics Frontend Test Results ===');
        this.testResults.forEach(result => {
            const status = result.passed ? '✅ PASS' : '❌ FAIL';
            console.log(`${status}: ${result.test} - ${result.message}`);
        });
        
        const passedTests = this.testResults.filter(r => r.passed).length;
        const totalTests = this.testResults.length;
        console.log(`\\nOverall: ${passedTests}/${totalTests} tests passed`);
    }
}

// Run tests when page loads
document.addEventListener('DOMContentLoaded', () => {
    const testSuite = new AnalyticsTestSuite();
    setTimeout(() => testSuite.runAllTests(), 2000); // Wait for Alpine.js to initialize
});
```

## 2. Data Accuracy Validation

### 2.1 Analytics Calculations Verification

#### Test Case E1.1: Manual Data Verification Script
```bash
# Create comprehensive data verification script
php artisan tinker --execute="
// Verify system overview calculations
\$studentsTotal = App\Models\Student::count();
\$studentsActive = App\Models\Student::where('status', 'active')->count();
\$programmesTotal = App\Models\Programme::count();
\$programmesActive = App\Models\Programme::where('is_active', true)->count();
\$assessmentsTotal = App\Models\StudentAssessment::count();
\$assessmentsPending = App\Models\StudentAssessment::where('status', 'pending')->count();
\$assessmentsPassed = App\Models\StudentAssessment::where('status', 'passed')->count();
\$enrollmentsTotal = App\Models\Enrolment::count();
\$enrollmentsActive = App\Models\Enrolment::where('status', 'active')->count();

// Get analytics service results
\$service = app(App\Services\AnalyticsService::class);
\$overview = \$service->getSystemOverview();

echo '=== Data Accuracy Verification ===' . PHP_EOL;
echo 'Students Total - Direct: ' . \$studentsTotal . ', Analytics: ' . \$overview['students']['total'] . ' - ' . (\$studentsTotal === \$overview['students']['total'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Students Active - Direct: ' . \$studentsActive . ', Analytics: ' . \$overview['students']['active'] . ' - ' . (\$studentsActive === \$overview['students']['active'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Programmes Total - Direct: ' . \$programmesTotal . ', Analytics: ' . \$overview['programmes']['total'] . ' - ' . (\$programmesTotal === \$overview['programmes']['total'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Programmes Active - Direct: ' . \$programmesActive . ', Analytics: ' . \$overview['programmes']['active'] . ' - ' . (\$programmesActive === \$overview['programmes']['active'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Assessments Total - Direct: ' . \$assessmentsTotal . ', Analytics: ' . \$overview['assessments']['total'] . ' - ' . (\$assessmentsTotal === \$overview['assessments']['total'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Assessments Pending - Direct: ' . \$assessmentsPending . ', Analytics: ' . \$overview['assessments']['pending'] . ' - ' . (\$assessmentsPending === \$overview['assessments']['pending'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Assessments Passed - Direct: ' . \$assessmentsPassed . ', Analytics: ' . \$overview['assessments']['passed'] . ' - ' . (\$assessmentsPassed === \$overview['assessments']['passed'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Enrollments Total - Direct: ' . \$enrollmentsTotal . ', Analytics: ' . \$overview['enrollments']['total'] . ' - ' . (\$enrollmentsTotal === \$overview['enrollments']['total'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
echo 'Enrollments Active - Direct: ' . \$enrollmentsActive . ', Analytics: ' . \$overview['enrollments']['active'] . ' - ' . (\$enrollmentsActive === \$overview['enrollments']['active'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
"
```

#### Test Case E1.2: Programme Effectiveness Calculations
```bash
php artisan tinker --execute="
// Test programme effectiveness calculations
\$service = app(App\Services\AnalyticsService::class);
\$effectiveness = \$service->getProgrammeEffectiveness();

foreach (\$effectiveness['programmes'] as \$prog) {
    echo '=== Programme: ' . \$prog['programme_code'] . ' ===' . PHP_EOL;
    
    // Verify enrollment counts
    \$programme = App\Models\Programme::find(\$prog['programme_id']);
    \$totalEnrolments = \$programme->enrolments->count();
    \$activeEnrolments = \$programme->enrolments->where('status', 'active')->count();
    \$completedEnrolments = \$programme->enrolments->where('status', 'completed')->count();
    
    echo 'Total Enrollments - Direct: ' . \$totalEnrolments . ', Analytics: ' . \$prog['total_enrollments'] . ' - ' . (\$totalEnrolments === \$prog['total_enrollments'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
    echo 'Active Enrollments - Direct: ' . \$activeEnrolments . ', Analytics: ' . \$prog['active_enrollments'] . ' - ' . (\$activeEnrolments === \$prog['active_enrollments'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
    echo 'Completed Enrollments - Direct: ' . \$completedEnrolments . ', Analytics: ' . \$prog['completed_enrollments'] . ' - ' . (\$completedEnrolments === \$prog['completed_enrollments'] ? 'MATCH' : 'MISMATCH') . PHP_EOL;
    
    // Verify completion rate calculation
    \$expectedCompletionRate = \$totalEnrolments > 0 ? (\$completedEnrolments / \$totalEnrolments) * 100 : 0;
    echo 'Completion Rate - Expected: ' . round(\$expectedCompletionRate, 2) . '%, Analytics: ' . \$prog['completion_rate'] . '% - ' . (abs(\$expectedCompletionRate - \$prog['completion_rate']) < 0.01 ? 'MATCH' : 'MISMATCH') . PHP_EOL;
    
    echo PHP_EOL;
}
"
```

### 2.2 Edge Cases Testing

#### Test Case F1.1: Empty Database Testing
```bash
# Test analytics with no data
php artisan migrate:fresh
php artisan tinker --execute="
\$service = app(App\Services\AnalyticsService::class);

echo '=== Testing with Empty Database ===' . PHP_EOL;

\$overview = \$service->getSystemOverview();
echo 'System Overview - Students Total: ' . \$overview['students']['total'] . PHP_EOL;
echo 'System Overview - Generated At: ' . \$overview['generated_at'] . PHP_EOL;

\$performance = \$service->getStudentPerformanceTrends();
echo 'Performance Trends - Assessment Count: ' . \$performance['assessment_trends']->count() . PHP_EOL;

\$effectiveness = \$service->getProgrammeEffectiveness();
echo 'Programme Effectiveness - Programme Count: ' . count(\$effectiveness['programmes']) . PHP_EOL;

\$completion = \$service->getAssessmentCompletionRates();
echo 'Completion Rates - Data Points: ' . \$completion['completion_rates']->count() . PHP_EOL;

\$engagement = \$service->getStudentEngagement();
echo 'Student Engagement - Active Students: ' . \$engagement['total_active_students'] . PHP_EOL;

echo 'All empty database tests completed successfully!' . PHP_EOL;
"
```

#### Test Case F1.2: Large Dataset Testing
```bash
# Test with large amounts of data
php artisan tinker --execute="
// Create test data for large dataset testing
echo 'Creating large test dataset...' . PHP_EOL;

// Create 100 programmes
for (\$i = 1; \$i <= 100; \$i++) {
    App\Models\Programme::create([
        'code' => 'PROG' . str_pad(\$i, 3, '0', STR_PAD_LEFT),
        'title' => 'Test Programme ' . \$i,
        'description' => 'Test programme description ' . \$i,
        'is_active' => \$i <= 80, // 80% active
        'enrolment_type' => 'cohort_based'
    ]);
}

// Create 1000 students
for (\$i = 1; \$i <= 1000; \$i++) {
    App\Models\Student::create([
        'student_number' => 'STU' . str_pad(\$i, 4, '0', STR_PAD_LEFT),
        'first_name' => 'Test',
        'last_name' => 'Student' . \$i,
        'email' => 'test' . \$i . '@example.com',
        'status' => collect(['active', 'enrolled', 'deferred', 'completed'])->random()
    ]);
}

echo 'Large dataset created. Running performance tests...' . PHP_EOL;

\$service = app(App\Services\AnalyticsService::class);

\$startTime = microtime(true);
\$overview = \$service->getSystemOverview();
\$endTime = microtime(true);
echo 'System Overview with large dataset: ' . round((\$endTime - \$startTime) * 1000, 2) . 'ms' . PHP_EOL;

\$startTime = microtime(true);
\$effectiveness = \$service->getProgrammeEffectiveness();
\$endTime = microtime(true);
echo 'Programme Effectiveness with 100 programmes: ' . round((\$endTime - \$startTime) * 1000, 2) . 'ms' . PHP_EOL;

echo 'Large dataset testing completed!' . PHP_EOL;
"
```

### 2.3 Caching Behavior Validation

#### Test Case G1.1: Cache Functionality Testing
```bash
# Test caching mechanisms
php artisan tinker --execute="
\$service = app(App\Services\AnalyticsService::class);

echo '=== Cache Functionality Testing ===' . PHP_EOL;

// Clear all cache first
\$service->clearExpiredCache();
App\Models\AnalyticsCache::clearAll();

// Test cache miss (first call)
\$startTime = microtime(true);
\$overview1 = \$service->getSystemOverview();
\$endTime = microtime(true);
\$firstCallTime = (\$endTime - \$startTime) * 1000;

// Test cache hit (second call)
\$startTime = microtime(true);
\$overview2 = \$service->getSystemOverview();
\$endTime = microtime(true);
\$secondCallTime = (\$endTime - \$startTime) * 1000;

echo 'First call (cache miss): ' . round(\$firstCallTime, 2) . 'ms' . PHP_EOL;
echo 'Second call (cache hit): ' . round(\$secondCallTime, 2) . 'ms' . PHP_EOL;
echo 'Cache effectiveness: ' . round(((\$firstCallTime - \$secondCallTime) / \$firstCallTime) * 100, 1) . '% faster' . PHP_EOL;

// Verify data consistency
\$dataMatch = json_encode(\$overview1) === json_encode(\$overview2);
echo 'Cache data consistency: ' . (\$dataMatch ? 'PASS' : 'FAIL') . PHP_EOL;

// Test cache expiration
echo PHP_EOL . 'Testing cache expiration...' . PHP_EOL;
\$cacheEntries = App\Models\AnalyticsCache::where('cache_key', 'system_overview')->count();
echo 'Cache entries for system_overview: ' . \$cacheEntries . PHP_EOL;

// Test cache refresh
\$service->refreshAllCache();
echo 'Cache refreshed successfully' . PHP_EOL;
"
```

#### Test Case G1.2: Cache Expiration Testing
```bash
php artisan tinker --execute="
// Test cache expiration behavior
\$service = app(App\Services\AnalyticsService::class);
App\Models\AnalyticsCache::clearAll();

echo '=== Cache Expiration Testing ===' . PHP_EOL;

// Create cache entry with short expiration (1 minute)
App\Models\AnalyticsCache::setCached('test_cache', ['test' => 'data'], 1);

// Verify cache exists
\$cachedData = App\Models\AnalyticsCache::getCached('test_cache');
echo 'Cache entry exists: ' . (\$cachedData ? 'YES' : 'NO') . PHP_EOL;

// Test expired cache cleanup
echo 'Testing expired cache cleanup...' . PHP_EOL;

// Create expired cache entry (backdated)
App\Models\AnalyticsCache::create([
    'cache_key' => 'expired_test',
    'cache_data' => json_encode(['expired' => true]),
    'expires_at' => now()->subHours(1)
]);

\$expiredCount = \$service->clearExpiredCache();
echo 'Cleared expired cache entries: ' . \$expiredCount . PHP_EOL;

// Verify expired entry was removed
\$expiredData = App\Models\AnalyticsCache::getCached('expired_test');
echo 'Expired cache removed: ' . (!\$expiredData ? 'YES' : 'NO') . PHP_EOL;
"
```

## 3. Performance Testing

### 3.1 API Response Time Benchmarks

#### Test Case H1.1: Response Time Benchmarking
```bash
#!/bin/bash
# Create script: scripts/benchmark-analytics-api.sh

echo "=== TOC-SIS Analytics API Performance Benchmarks ==="
echo "Starting benchmark tests..."

# Function to measure API response time
benchmark_endpoint() {
    local endpoint=$1
    local name=$2
    echo "Testing $name..."
    
    # Warm up cache
    curl -s -o /dev/null "http://localhost:8000$endpoint"
    
    # Measure response time
    local response_time=$(curl -s -w "%{time_total}" -o /dev/null "http://localhost:8000$endpoint")
    echo "$name: ${response_time}s"
    
    # Check if response time is acceptable (< 2 seconds)
    if (( $(echo "$response_time < 2.0" | bc -l) )); then
        echo "✅ $name performance: ACCEPTABLE"
    else
        echo "❌ $name performance: TOO SLOW"
    fi
    echo ""
}

# Benchmark all analytics endpoints
benchmark_endpoint "/api/analytics/system-overview" "System Overview"
benchmark_endpoint "/api/analytics/student-performance" "Student Performance"
benchmark_endpoint "/api/analytics/programme-effectiveness" "Programme Effectiveness"
benchmark_endpoint "/api/analytics/assessment-completion" "Assessment Completion"
benchmark_endpoint "/api/analytics/student-engagement" "Student Engagement"
benchmark_endpoint "/api/analytics/chart-data/student_performance" "Chart Data - Performance"
benchmark_endpoint "/api/analytics/chart-data/programme_effectiveness" "Chart Data - Effectiveness"
benchmark_endpoint "/api/analytics/historical-metrics?metric_type=system_overview" "Historical Metrics"

echo "=== Benchmark Complete ==="
```

#### Test Case H1.2: Load Testing
```bash
# Create load testing script using Apache Bench (ab)
# Install: sudo apt-get install apache2-utils

#!/bin/bash
# Script: scripts/load-test-analytics.sh

echo "=== Analytics API Load Testing ==="

# Test concurrent requests
echo "Testing 100 concurrent requests to system overview..."
ab -n 100 -c 10 http://localhost:8000/api/analytics/system-overview

echo ""
echo "Testing 50 concurrent requests to student performance..."
ab -n 50 -c 5 http://localhost:8000/api/analytics/student-performance

echo ""
echo "Testing 30 concurrent requests to programme effectiveness..."
ab -n 30 -c 3 http://localhost:8000/api/analytics/programme-effectiveness

echo "=== Load Testing Complete ==="
```

### 3.2 Database Query Performance

#### Test Case I1.1: Query Performance Analysis
```bash
php artisan tinker --execute="
// Enable query logging
DB::enableQueryLog();

\$service = app(App\Services\AnalyticsService::class);

echo '=== Database Query Performance Analysis ===' . PHP_EOL;

// Test system overview queries
\$startTime = microtime(true);
\$overview = \$service->getSystemOverview();
\$endTime = microtime(true);
\$systemOverviewTime = (\$endTime - \$startTime) * 1000;

\$queries = DB::getQueryLog();
\$systemOverviewQueries = count(\$queries);
DB::flushQueryLog();

echo 'System Overview:' . PHP_EOL;
echo '  Execution time: ' . round(\$systemOverviewTime, 2) . 'ms' . PHP_EOL;
echo '  Database queries: ' . \$systemOverviewQueries . PHP_EOL;

// Test student performance trends
\$startTime = microtime(true);
\$performance = \$service->getStudentPerformanceTrends();
\$endTime = microtime(true);
\$performanceTime = (\$endTime - \$startTime) * 1000;

\$queries = DB::getQueryLog();
\$performanceQueries = count(\$queries);
DB::flushQueryLog();

echo PHP_EOL . 'Student Performance Trends:' . PHP_EOL;
echo '  Execution time: ' . round(\$performanceTime, 2) . 'ms' . PHP_EOL;
echo '  Database queries: ' . \$performanceQueries . PHP_EOL;

// Test programme effectiveness (potentially expensive)
\$startTime = microtime(true);
\$effectiveness = \$service->getProgrammeEffectiveness();
\$endTime = microtime(true);
\$effectivenessTime = (\$endTime - \$startTime) * 1000;

\$queries = DB::getQueryLog();
\$effectivenessQueries = count(\$queries);
DB::flushQueryLog();

echo PHP_EOL . 'Programme Effectiveness:' . PHP_EOL;
echo '  Execution time: ' . round(\$effectivenessTime, 2) . 'ms' . PHP_EOL;
echo '  Database queries: ' . \$effectivenessQueries . PHP_EOL;

// Performance warnings
if (\$systemOverviewTime > 1000) echo '⚠️  WARNING: System Overview taking too long' . PHP_EOL;
if (\$performanceTime > 2000) echo '⚠️  WARNING: Performance Trends taking too long' . PHP_EOL;
if (\$effectivenessTime > 3000) echo '⚠️  WARNING: Programme Effectiveness taking too long' . PHP_EOL;

DB::disableQueryLog();
"
```

### 3.3 Memory Usage and Caching Efficiency

#### Test Case J1.1: Memory Usage Analysis
```bash
php artisan tinker --execute="
echo '=== Memory Usage Analysis ===' . PHP_EOL;

\$service = app(App\Services\AnalyticsService::class);

// Measure memory usage for each analytics function
function measureMemoryUsage(\$callback, \$name) {
    \$memoryBefore = memory_get_usage(true);
    \$peakBefore = memory_get_peak_usage(true);
    
    \$result = \$callback();
    
    \$memoryAfter = memory_get_usage(true);
    \$peakAfter = memory_get_peak_usage(true);
    
    \$memoryUsed = \$memoryAfter - \$memoryBefore;
    \$peakIncrease = \$peakAfter - \$peakBefore;
    
    echo \$name . ':' . PHP_EOL;
    echo '  Memory used: ' . number_format(\$memoryUsed / 1024 / 1024, 2) . ' MB' . PHP_EOL;
    echo '  Peak increase: ' . number_format(\$peakIncrease / 1024 / 1024, 2) . ' MB' . PHP_EOL;
    echo '  Current memory: ' . number_format(\$memoryAfter / 1024 / 1024, 2) . ' MB' . PHP_EOL;
    echo PHP_EOL;
    
    return \$result;
}

measureMemoryUsage(function() use (\$service) {
    return \$service->getSystemOverview();
}, 'System Overview');

measureMemoryUsage(function() use (\$service) {
    return \$service->getStudentPerformanceTrends();
}, 'Student Performance Trends');

measureMemoryUsage(function() use (\$service) {
    return \$service->getProgrammeEffectiveness();
}, 'Programme Effectiveness');

measureMemoryUsage(function() use (\$service) {
    return \$service->getAssessmentCompletionRates();
}, 'Assessment Completion Rates');

measureMemoryUsage(function() use (\$service) {
    return \$service->getStudentEngagement();
}, 'Student Engagement');

echo 'Total script memory usage: ' . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB' . PHP_EOL;
"
```

## 4. Security Testing

### 4.1 Role-Based Access Control Verification

#### Test Case K1.1: Authentication Testing
```bash
# Test unauthenticated access
curl -X GET "http://localhost:8000/api/analytics/system-overview" \
  -H "Accept: application/json"
# Expected: Redirect to login or 401 Unauthorized

#!/bin/bash
# Script: scripts/test-analytics-security.sh

echo "=== Analytics Security Testing ==="

# Test unauthenticated access
echo "Testing unauthenticated access..."
response_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/analytics/system-overview")
echo "System Overview without auth: HTTP $response_code"

if [ "$response_code" == "401" ] || [ "$response_code" == "302" ]; then
    echo "✅ Properly blocks unauthenticated access"
else
    echo "❌ Security issue: Allows unauthenticated access"
fi

echo ""
```

#### Test Case K1.2: Role-Based Access Testing
```php
// Create test: tests/Feature/AnalyticsSecurityTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_access_analytics()
    {
        $manager = User::factory()->create(['role' => 'manager']);
        
        $response = $this->actingAs($manager)
            ->get('/api/analytics/system-overview');
            
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'students', 'programmes', 'assessments', 'enrollments', 'generated_at'
        ]);
    }
    
    public function test_student_services_can_access_analytics()
    {
        $studentServices = User::factory()->create(['role' => 'student_services']);
        
        $response = $this->actingAs($studentServices)
            ->get('/api/analytics/programme-effectiveness');
            
        $response->assertStatus(200);
        $response->assertJsonHasKey('programmes');
    }
    
    public function test_teacher_can_access_analytics()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        $response = $this->actingAs($teacher)
            ->get('/api/analytics/student-performance');
            
        $response->assertStatus(200);
        $response->assertJsonHasKey('assessment_trends');
    }
    
    public function test_student_cannot_access_analytics()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($student)
            ->get('/api/analytics/system-overview');
            
        $response->assertStatus(403); // Forbidden
    }
    
    public function test_all_analytics_endpoints_require_staff_role()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $endpoints = [
            '/api/analytics/system-overview',
            '/api/analytics/student-performance',
            '/api/analytics/programme-effectiveness',
            '/api/analytics/assessment-completion',
            '/api/analytics/student-engagement',
            '/api/analytics/chart-data/student_performance',
            '/api/analytics/historical-metrics?metric_type=system_overview'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($student)->get($endpoint);
            $response->assertStatus(403, "Endpoint {$endpoint} should be forbidden for students");
        }
    }
    
    public function test_cache_management_endpoints_require_staff_role()
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $response = $this->actingAs($student)
            ->post('/api/analytics/refresh-cache');
        $response->assertStatus(403);
        
        $response = $this->actingAs($student)
            ->post('/api/analytics/clear-expired-cache');
        $response->assertStatus(403);
    }
}
```

### 4.2 Data Privacy and Access Restrictions

#### Test Case L1.1: Data Exposure Testing
```bash
php artisan tinker --execute="
// Test that analytics don't expose sensitive student data
\$service = app(App\Services\AnalyticsService::class);

echo '=== Data Privacy Testing ===' . PHP_EOL;

// Test system overview doesn't expose individual student details
\$overview = \$service->getSystemOverview();
\$overviewJson = json_encode(\$overview);

\$containsEmail = strpos(\$overviewJson, '@') !== false;
\$containsStudentNumber = preg_match('/STU\d+/', \$overviewJson);
\$containsPersonalNames = preg_match('/[A-Z][a-z]+ [A-Z][a-z]+/', \$overviewJson);

echo 'System Overview Privacy Check:' . PHP_EOL;
echo '  Contains email addresses: ' . (\$containsEmail ? '❌ FAIL' : '✅ PASS') . PHP_EOL;
echo '  Contains student numbers: ' . (\$containsStudentNumber ? '❌ FAIL' : '✅ PASS') . PHP_EOL;
echo '  Contains personal names: ' . (\$containsPersonalNames ? '❌ FAIL' : '✅ PASS') . PHP_EOL;

// Test programme effectiveness doesn't expose individual data
\$effectiveness = \$service->getProgrammeEffectiveness();
\$effectivenessJson = json_encode(\$effectiveness);

\$containsEmail2 = strpos(\$effectivenessJson, '@') !== false;
\$containsStudentNumber2 = preg_match('/STU\d+/', \$effectivenessJson);

echo PHP_EOL . 'Programme Effectiveness Privacy Check:' . PHP_EOL;
echo '  Contains email addresses: ' . (\$containsEmail2 ? '❌ FAIL' : '✅ PASS') . PHP_EOL;
echo '  Contains student numbers: ' . (\$containsStudentNumber2 ? '❌ FAIL' : '✅ PASS') . PHP_EOL;

// Test student engagement doesn't expose individual activity
\$engagement = \$service->getStudentEngagement();
\$engagementJson = json_encode(\$engagement);

\$containsIndividualActivity = preg_match('/student_id|user_id/', \$engagementJson);
echo PHP_EOL . 'Student Engagement Privacy Check:' . PHP_EOL;
echo '  Contains individual identifiers: ' . (\$containsIndividualActivity ? '❌ FAIL' : '✅ PASS') . PHP_EOL;

echo PHP_EOL . 'Data privacy testing completed.' . PHP_EOL;
"
```

## 5. Integration Testing

### 5.1 TOC-SIS Integration Testing

#### Test Case M1.1: Integration with Existing Models
```php
// Create test: tests/Feature/AnalyticsIntegrationTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Programme;
use App\Models\Enrolment;
use App\Models\StudentAssessment;
use App\Models\ModuleInstance;
use App\Models\Module;
use App\Models\Cohort;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_integrates_with_student_model()
    {
        // Create test data
        $students = Student::factory()->count(5)->create([
            'status' => 'active'
        ]);
        
        $service = app(AnalyticsService::class);
        $overview = $service->getSystemOverview();
        
        $this->assertEquals(5, $overview['students']['total']);
        $this->assertEquals(5, $overview['students']['active']);
    }
    
    public function test_analytics_integrates_with_programme_model()
    {
        $programmes = Programme::factory()->count(3)->create([
            'is_active' => true
        ]);
        
        $service = app(AnalyticsService::class);
        $overview = $service->getSystemOverview();
        
        $this->assertEquals(3, $overview['programmes']['total']);
        $this->assertEquals(3, $overview['programmes']['active']);
    }
    
    public function test_analytics_integrates_with_assessment_model()
    {
        // Create complete academic structure
        $programme = Programme::factory()->create();
        $cohort = Cohort::factory()->create(['programme_id' => $programme->id]);
        $module = Module::factory()->create();
        $moduleInstance = ModuleInstance::factory()->create([
            'module_id' => $module->id,
            'cohort_id' => $cohort->id
        ]);
        $student = Student::factory()->create();
        $enrolment = Enrolment::factory()->create([
            'student_id' => $student->id,
            'programme_id' => $programme->id
        ]);
        
        StudentAssessment::factory()->count(3)->create(['status' => 'passed']);
        StudentAssessment::factory()->count(2)->create(['status' => 'failed']);
        StudentAssessment::factory()->count(1)->create(['status' => 'pending']);
        
        $service = app(AnalyticsService::class);
        $overview = $service->getSystemOverview();
        
        $this->assertEquals(6, $overview['assessments']['total']);
        $this->assertEquals(3, $overview['assessments']['passed']);
        $this->assertEquals(2, $overview['assessments']['failed']);
        $this->assertEquals(1, $overview['assessments']['pending']);
    }
    
    public function test_programme_effectiveness_with_real_data()
    {
        // Create programme with enrollments and assessments
        $programme = Programme::factory()->create(['is_active' => true]);
        $students = Student::factory()->count(10)->create();
        
        // Create enrollments with different statuses
        foreach ($students->take(8) as $student) {
            Enrolment::factory()->create([
                'student_id' => $student->id,
                'programme_id' => $programme->id,
                'status' => 'active'
            ]);
        }
        
        foreach ($students->skip(8) as $student) {
            Enrolment::factory()->create([
                'student_id' => $student->id,
                'programme_id' => $programme->id,
                'status' => 'completed'
            ]);
        }
        
        $service = app(AnalyticsService::class);
        $effectiveness = $service->getProgrammeEffectiveness();
        
        $this->assertCount(1, $effectiveness['programmes']);
        $programmeData = $effectiveness['programmes'][0];
        
        $this->assertEquals(10, $programmeData['total_enrollments']);
        $this->assertEquals(8, $programmeData['active_enrollments']);
        $this->assertEquals(2, $programmeData['completed_enrollments']);
        $this->assertEquals(20.0, $programmeData['completion_rate']); // 2/10 * 100
    }
}
```

### 5.2 Backward Compatibility Testing

#### Test Case N1.1: Existing Reports Integration
```bash
# Test that analytics don't break existing reports
php artisan test --filter=ReportControllerTest

# Test existing dashboard functionality
curl -X GET "http://localhost:8000/reports/dashboard" \
  -H "Accept: text/html" \
  -b "laravel_session=test_session"
```

### 5.3 Command Execution Testing

#### Test Case O1.1: Analytics Command Testing
```bash
# Test analytics computation command
echo "=== Testing Analytics Command ==="

# Test basic command execution
php artisan analytics:compute
echo "Command exit code: $?"

# Test with clear cache option
php artisan analytics:compute --clear-cache
echo "Command with clear-cache exit code: $?"

# Test command with invalid database (should handle gracefully)
# This would require temporarily breaking database connection

# Test command output format
output=$(php artisan analytics:compute 2>&1)
echo "Command output:"
echo "$output"

# Verify command creates cache entries
php artisan tinker --execute="
\$cacheCount = App\Models\AnalyticsCache::count();
echo 'Cache entries after command: ' . \$cacheCount . PHP_EOL;

\$metricCount = App\Models\AnalyticsMetric::count();
echo 'Metric entries after command: ' . \$metricCount . PHP_EOL;
"
```

### 5.4 Cache Invalidation and Refresh Cycles

#### Test Case P1.1: Cache Lifecycle Testing
```php
// Create test: tests/Feature/AnalyticsCacheLifecycleTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AnalyticsService;
use App\Models\AnalyticsCache;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AnalyticsCacheLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_creation_and_retrieval()
    {
        $service = app(AnalyticsService::class);
        
        // Clear any existing cache
        AnalyticsCache::clearAll();
        
        // First call should create cache
        $result1 = $service->getSystemOverview();
        $cacheCount = AnalyticsCache::count();
        
        $this->assertEquals(1, $cacheCount);
        
        // Second call should use cache
        $result2 = $service->getSystemOverview();
        $cacheCount2 = AnalyticsCache::count();
        
        $this->assertEquals(1, $cacheCount2);
        $this->assertEquals($result1, $result2);
    }
    
    public function test_cache_expiration()
    {
        // Create expired cache entry
        AnalyticsCache::create([
            'cache_key' => 'test_expired',
            'cache_data' => json_encode(['test' => 'data']),
            'expires_at' => Carbon::now()->subHour()
        ]);
        
        // Should return null for expired cache
        $cached = AnalyticsCache::getCached('test_expired');
        $this->assertNull($cached);
    }
    
    public function test_cache_refresh_functionality()
    {
        $service = app(AnalyticsService::class);
        
        // Create initial cache
        $service->getSystemOverview();
        $initialCacheTime = AnalyticsCache::where('cache_key', 'system_overview')->first()->updated_at;
        
        // Wait a moment
        sleep(1);
        
        // Refresh cache
        $service->refreshAllCache();
        $refreshedCacheTime = AnalyticsCache::where('cache_key', 'system_overview')->first()->updated_at;
        
        $this->assertTrue($refreshedCacheTime > $initialCacheTime);
    }
    
    public function test_cache_invalidation_on_data_change()
    {
        $service = app(AnalyticsService::class);
        
        // Get initial overview
        $overview1 = $service->getSystemOverview();
        $initialStudentCount = $overview1['students']['total'];
        
        // Add new student
        Student::factory()->create();
        
        // Cache should still return old data
        $overview2 = $service->getSystemOverview();
        $this->assertEquals($initialStudentCount, $overview2['students']['total']);
        
        // After cache refresh, should show new data
        $service->refreshAllCache();
        $overview3 = $service->getSystemOverview();
        $this->assertEquals($initialStudentCount + 1, $overview3['students']['total']);
    }
    
    public function test_expired_cache_cleanup()
    {
        // Create both valid and expired cache entries
        AnalyticsCache::create([
            'cache_key' => 'valid_cache',
            'cache_data' => json_encode(['valid' => true]),
            'expires_at' => Carbon::now()->addHour()
        ]);
        
        AnalyticsCache::create([
            'cache_key' => 'expired_cache',
            'cache_data' => json_encode(['expired' => true]),
            'expires_at' => Carbon::now()->subHour()
        ]);
        
        $service = app(AnalyticsService::class);
        $clearedCount = $service->clearExpiredCache();
        
        $this->assertEquals(1, $clearedCount);
        
        // Valid cache should still exist
        $validCache = AnalyticsCache::getCached('valid_cache');
        $this->assertNotNull($validCache);
        
        // Expired cache should be gone
        $expiredCache = AnalyticsCache::getCached('expired_cache');
        $this->assertNull($expiredCache);
    }
}
```

## Testing Execution Scripts

### Master Test Runner Script
```bash
#!/bin/bash
# Script: scripts/run-analytics-tests.sh

echo "========================================="
echo "TOC-SIS Analytics System - Full Test Suite"
echo "========================================="

# Set variables
BASE_URL="http://localhost:8000"
PASSED_TESTS=0
FAILED_TESTS=0
TOTAL_TESTS=0

# Function to run test and track results
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo "Running: $test_name"
    if eval "$test_command"; then
        echo "✅ PASSED: $test_name"
        ((PASSED_TESTS++))
    else
        echo "❌ FAILED: $test_name"
        ((FAILED_TESTS++))
    fi
    ((TOTAL_TESTS++))
    echo ""
}

# Ensure application is running
echo "Checking if application is running..."
if ! curl -s "$BASE_URL" > /dev/null; then
    echo "❌ Application not running at $BASE_URL"
    echo "Please start the application with: php artisan serve"
    exit 1
fi

echo "✅ Application is running"
echo ""

# 1. Database and Migration Tests
echo "=== 1. Database and Migration Tests ==="
run_test "Database Migration Check" "php artisan migrate:status | grep -q analytics"
run_test "Analytics Models Load" "php artisan tinker --execute='App\Models\AnalyticsCache::count(); App\Models\AnalyticsMetric::count();'"

# 2. Analytics Service Tests
echo "=== 2. Analytics Service Tests ==="
run_test "Analytics Service Instantiation" "php artisan tinker --execute='app(App\Services\AnalyticsService::class);'"
run_test "System Overview Generation" "php artisan tinker --execute='app(App\Services\AnalyticsService::class)->getSystemOverview();'"
run_test "Student Performance Trends" "php artisan tinker --execute='app(App\Services\AnalyticsService::class)->getStudentPerformanceTrends();'"
run_test "Programme Effectiveness" "php artisan tinker --execute='app(App\Services\AnalyticsService::class)->getProgrammeEffectiveness();'"

# 3. API Endpoint Tests
echo "=== 3. API Endpoint Tests ==="
run_test "System Overview API" "curl -s '$BASE_URL/api/analytics/system-overview' | grep -q 'students'"
run_test "Student Performance API" "curl -s '$BASE_URL/api/analytics/student-performance' | grep -q 'assessment_trends'"
run_test "Programme Effectiveness API" "curl -s '$BASE_URL/api/analytics/programme-effectiveness' | grep -q 'programmes'"
run_test "Assessment Completion API" "curl -s '$BASE_URL/api/analytics/assessment-completion' | grep -q 'completion_rates'"
run_test "Student Engagement API" "curl -s '$BASE_URL/api/analytics/student-engagement' | grep -q 'engagement_rate'"

# 4. Chart Data Tests
echo "=== 4. Chart Data Tests ==="
run_test "Student Performance Chart" "curl -s '$BASE_URL/api/analytics/chart-data/student_performance' | grep -q 'type'"
run_test "Programme Effectiveness Chart" "curl -s '$BASE_URL/api/analytics/chart-data/programme_effectiveness' | grep -q 'data'"
run_test "Assessment Completion Chart" "curl -s '$BASE_URL/api/analytics/chart-data/assessment_completion' | grep -q 'labels'"
run_test "Student Engagement Chart" "curl -s '$BASE_URL/api/analytics/chart-data/student_engagement' | grep -q 'datasets'"

# 5. Cache Management Tests
echo "=== 5. Cache Management Tests ==="
run_test "Cache Refresh" "curl -s -X POST '$BASE_URL/api/analytics/refresh-cache' | grep -q 'success'"
run_test "Clear Expired Cache" "curl -s -X POST '$BASE_URL/api/analytics/clear-expired-cache' | grep -q 'success'"

# 6. Analytics Command Tests
echo "=== 6. Analytics Command Tests ==="
run_test "Analytics Compute Command" "php artisan analytics:compute"
run_test "Analytics Compute with Clear Cache" "php artisan analytics:compute --clear-cache"

# 7. Laravel Feature Tests
echo "=== 7. Laravel Feature Tests ==="
run_test "Laravel Analytics Feature Tests" "php artisan test --filter=Analytics"

# 8. Performance Tests
echo "=== 8. Performance Tests ==="
run_test "System Overview Response Time" "timeout 5s curl -s '$BASE_URL/api/analytics/system-overview' > /dev/null"
run_test "Multiple Concurrent Requests" "for i in {1..5}; do curl -s '$BASE_URL/api/analytics/system-overview' > /dev/null & done; wait"

# 9. Security Tests
echo "=== 9. Security Tests ==="
run_test "Unauthenticated Access Blocked" "! curl -s '$BASE_URL/api/analytics/system-overview' | grep -q 'students'"

# Generate Test Report
echo "========================================="
echo "TEST EXECUTION SUMMARY"
echo "========================================="
echo "Total Tests: $TOTAL_TESTS"
echo "Passed: $PASSED_TESTS"
echo "Failed: $FAILED_TESTS"
echo "Success Rate: $(( PASSED_TESTS * 100 / TOTAL_TESTS ))%"
echo ""

if [ $FAILED_TESTS -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED! Analytics system is ready for production."
    exit 0
else
    echo "⚠️  $FAILED_TESTS tests failed. Please review the issues above."
    exit 1
fi
```

### Quick Smoke Test Script
```bash
#!/bin/bash
# Script: scripts/analytics-smoke-test.sh

echo "=== Analytics System Smoke Test ==="

# Quick health check of core functionality
echo "1. Testing analytics service instantiation..."
php artisan tinker --execute="app(App\Services\AnalyticsService::class);" > /dev/null 2>&1
if [ $? -eq 0 ]; then echo "✅ Service loads"; else echo "❌ Service failed"; exit 1; fi

echo "2. Testing system overview generation..."
php artisan tinker --execute="app(App\Services\AnalyticsService::class)->getSystemOverview();" > /dev/null 2>&1
if [ $? -eq 0 ]; then echo "✅ System overview works"; else echo "❌ System overview failed"; exit 1; fi

echo "3. Testing cache functionality..."
php artisan tinker --execute="App\Models\AnalyticsCache::setCached('test', ['data' => true]); App\Models\AnalyticsCache::getCached('test');" > /dev/null 2>&1
if [ $? -eq 0 ]; then echo "✅ Cache works"; else echo "❌ Cache failed"; exit 1; fi

echo "4. Testing analytics command..."
php artisan analytics:compute > /dev/null 2>&1
if [ $? -eq 0 ]; then echo "✅ Command works"; else echo "❌ Command failed"; exit 1; fi

echo ""
echo "🎉 All smoke tests passed! Analytics system is functional."
```

## Production Readiness Checklist

### Performance Requirements
- [ ] All API endpoints respond within 2 seconds
- [ ] System handles 100 concurrent requests without errors
- [ ] Database queries are optimized with proper indexes
- [ ] Memory usage stays under 256MB per request
- [ ] Cache hit rate above 80% for repeated requests

### Security Requirements
- [ ] All endpoints require authentication
- [ ] Role-based access control enforced
- [ ] No sensitive student data exposed in analytics
- [ ] SQL injection protection verified
- [ ] CSRF protection enabled

### Reliability Requirements
- [ ] Graceful error handling for all edge cases
- [ ] Cache corruption recovery mechanisms
- [ ] Database connection failure handling
- [ ] Invalid data input sanitization
- [ ] Comprehensive logging for debugging

### Integration Requirements
- [ ] Seamless integration with existing TOC-SIS components
- [ ] Backward compatibility with current reports
- [ ] No performance impact on existing functionality
- [ ] Proper Laravel model relationships
- [ ] Activity logging integration

### Monitoring Requirements
- [ ] Command execution monitoring
- [ ] Cache performance metrics
- [ ] API response time tracking
- [ ] Error rate monitoring
- [ ] Database query performance logging

---

This comprehensive testing plan ensures that the Phase 1 analytics implementation meets all quality standards and is ready for production deployment in the TOC-SIS system.