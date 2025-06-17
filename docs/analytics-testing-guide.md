# TOC-SIS Analytics System - Testing Guide

## Overview

This guide provides comprehensive instructions for testing the Phase 1 analytics implementation in the TOC Student Information System. The testing framework ensures the analytics system meets quality, performance, and security standards before production deployment.

## Quick Start

### Prerequisites

1. **Laravel Application Running**
   ```bash
   php artisan serve
   ```

2. **Database Migrated**
   ```bash
   php artisan migrate
   ```

3. **Test Environment Configured**
   ```bash
   cp .env .env.testing
   # Edit .env.testing for test database settings
   ```

### Running Tests

#### 1. Quick Smoke Test (2-3 minutes)
```bash
./scripts/analytics-smoke-test.sh
```
**Purpose**: Rapid health check to verify basic functionality works.

#### 2. Full Test Suite (10-15 minutes)
```bash
./scripts/run-analytics-tests.sh
```
**Purpose**: Comprehensive testing of all functionality, security, and performance.

#### 3. Performance Testing (5-10 minutes)
```bash
./scripts/analytics-performance-test.sh
```
**Purpose**: Detailed performance analysis and benchmarking.

## Test Categories

### 1. Functional Tests

#### Laravel Feature Tests
```bash
# Run all analytics tests
php artisan test --filter=Analytics

# Run specific test classes
php artisan test tests/Feature/AnalyticsApiTest.php
php artisan test tests/Feature/AnalyticsSecurityTest.php
php artisan test tests/Feature/AnalyticsCacheTest.php
```

#### Manual API Testing
```bash
# Test system overview
curl -X GET "http://localhost:8000/api/analytics/system-overview"

# Test with parameters
curl -X GET "http://localhost:8000/api/analytics/student-performance?period_type=weekly&months=6"

# Test chart data
curl -X GET "http://localhost:8000/api/analytics/chart-data/student_performance"
```

### 2. Data Accuracy Tests

#### Verify Analytics Calculations
```bash
php artisan tinker --execute="
// Create known test data
App\Models\Student::factory()->count(10)->create(['status' => 'active']);
App\Models\Programme::factory()->count(5)->create(['is_active' => true]);

// Clear cache and get fresh analytics
App\Models\AnalyticsCache::clearAll();
\$service = app(App\Services\AnalyticsService::class);
\$overview = \$service->getSystemOverview();

// Verify counts match
echo 'Students Total: ' . \$overview['students']['total'] . PHP_EOL;
echo 'Programmes Active: ' . \$overview['programmes']['active'] . PHP_EOL;
"
```

### 3. Performance Tests

#### Database Query Performance
```bash
# Enable query logging and measure execution time
php artisan tinker --execute="
DB::enableQueryLog();
\$start = microtime(true);
app(App\Services\AnalyticsService::class)->getSystemOverview();
\$end = microtime(true);
\$queries = count(DB::getQueryLog());
\$time = (\$end - \$start) * 1000;
echo \"Queries: \$queries, Time: \" . round(\$time, 2) . \"ms\" . PHP_EOL;
"
```

#### API Response Time Testing
```bash
# Using curl to measure response times
time curl -s "http://localhost:8000/api/analytics/system-overview" > /dev/null

# Using ab (Apache Bench) for load testing
ab -n 100 -c 10 http://localhost:8000/api/analytics/system-overview
```

### 4. Security Tests

#### Role-Based Access Control
```bash
# Test with different user roles (requires authentication setup)
php artisan test tests/Feature/AnalyticsSecurityTest.php::test_student_cannot_access_any_analytics_endpoints
```

#### Input Validation
```bash
# Test SQL injection protection
curl -X GET "http://localhost:8000/api/analytics/student-performance?period_type='; DROP TABLE students; --"

# Test XSS protection
curl -X GET "http://localhost:8000/api/analytics/historical-metrics?metric_type=<script>alert('xss')</script>"
```

### 5. Cache Testing

#### Cache Functionality
```bash
php artisan tinker --execute="
// Test cache creation and retrieval
App\Models\AnalyticsCache::clearAll();
\$service = app(App\Services\AnalyticsService::class);

// First call (cache miss)
\$start = microtime(true);
\$result1 = \$service->getSystemOverview();
\$end = microtime(true);
\$time1 = (\$end - \$start) * 1000;

// Second call (cache hit)
\$start = microtime(true);
\$result2 = \$service->getSystemOverview();
\$end = microtime(true);
\$time2 = (\$end - \$start) * 1000;

echo \"Cache miss: \" . round(\$time1, 2) . \"ms\" . PHP_EOL;
echo \"Cache hit: \" . round(\$time2, 2) . \"ms\" . PHP_EOL;
echo \"Improvement: \" . round(((\$time1 - \$time2) / \$time1) * 100, 1) . \"%\" . PHP_EOL;
"
```

### 6. Integration Tests

#### Analytics Command Testing
```bash
# Test analytics compute command
php artisan analytics:compute

# Test with options
php artisan analytics:compute --clear-cache

# Verify command creates expected data
php artisan tinker --execute="
echo 'Cache entries: ' . App\Models\AnalyticsCache::count() . PHP_EOL;
echo 'Metric entries: ' . App\Models\AnalyticsMetric::count() . PHP_EOL;
"
```

## Test Environments

### Development Testing
```bash
# Use default environment
php artisan test

# Run with specific environment
php artisan test --env=testing
```

### Production-like Testing
```bash
# Set production-like configuration
export APP_ENV=testing
export APP_DEBUG=false
export CACHE_DRIVER=redis  # If using Redis
export DB_CONNECTION=mysql  # If using MySQL

# Run full test suite
./scripts/run-analytics-tests.sh
```

## Expected Performance Benchmarks

### Response Time Targets
- **System Overview**: < 2 seconds
- **Student Performance**: < 2 seconds  
- **Programme Effectiveness**: < 3 seconds
- **Assessment Completion**: < 2 seconds
- **Student Engagement**: < 2 seconds
- **Chart Data**: < 2 seconds
- **Cache Hits**: < 100ms

### Memory Usage Targets
- **Per Request**: < 256MB
- **Command Execution**: < 512MB
- **Concurrent Requests**: Stable under load

### Concurrency Targets
- **Success Rate**: > 95% with 10 concurrent users
- **Response Time**: < 3 seconds under load
- **Error Rate**: < 1% under normal load

## Troubleshooting Common Issues

### 1. Tests Failing Due to Authentication
```bash
# Ensure routes are properly configured for testing
# Check middleware settings in tests
$this->withoutMiddleware();  # In test methods if needed
```

### 2. Cache-Related Test Failures
```bash
# Clear all cache before tests
php artisan tinker --execute="App\Models\AnalyticsCache::clearAll();"

# Reset test database
php artisan migrate:fresh --env=testing
```

### 3. Performance Tests Timing Out
```bash
# Increase timeout values
export TEST_TIMEOUT=60

# Reduce test data size
# Optimize database queries
# Check server resources
```

### 4. API Endpoints Not Accessible
```bash
# Verify Laravel application is running
curl -I http://localhost:8000

# Check route definitions
php artisan route:list | grep analytics

# Verify middleware configuration
```

### 5. Database Connection Issues
```bash
# Test database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check migration status
php artisan migrate:status

# Reset database if needed
php artisan migrate:fresh --seed
```

## Test Data Management

### Creating Test Data
```bash
php artisan tinker --execute="
// Create comprehensive test dataset
\$programmes = App\Models\Programme::factory()->count(10)->create();
\$students = App\Models\Student::factory()->count(100)->create();
\$cohorts = App\Models\Cohort::factory()->count(5)->create();

// Create enrollments
foreach (\$students->take(80) as \$student) {
    App\Models\Enrolment::factory()->create([
        'student_id' => \$student->id,
        'programme_id' => \$programmes->random()->id,
        'status' => collect(['active', 'completed', 'deferred'])->random()
    ]);
}

echo 'Test data created successfully' . PHP_EOL;
"
```

### Cleaning Test Data
```bash
# Clear test data
php artisan migrate:fresh

# Or selectively clean
php artisan tinker --execute="
App\Models\AnalyticsCache::clearAll();
App\Models\AnalyticsMetric::truncate();
"
```

## Continuous Integration Setup

### GitHub Actions Example
```yaml
name: Analytics Tests

on: [push, pull_request]

jobs:
  analytics-tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
    
    - name: Install Dependencies
      run: composer install
    
    - name: Run Analytics Tests
      run: |
        php artisan migrate --env=testing
        php artisan test --filter=Analytics --env=testing
    
    - name: Run Performance Tests
      run: ./scripts/analytics-performance-test.sh --quick
```

## Production Deployment Checklist

### Pre-Deployment Testing
- [ ] All Laravel feature tests pass
- [ ] All performance benchmarks met
- [ ] Security tests pass
- [ ] Cache functionality verified
- [ ] Role-based access control tested
- [ ] Database queries optimized
- [ ] Memory usage within limits

### Post-Deployment Monitoring
- [ ] API response times monitored
- [ ] Cache hit rates tracked
- [ ] Database query performance logged
- [ ] Error rates monitored
- [ ] User access patterns verified

## Advanced Testing Scenarios

### Load Testing with Artillery
```bash
# Install Artillery
npm install -g artillery

# Create test configuration
cat > artillery-analytics.yml << EOF
config:
  target: 'http://localhost:8000'
  phases:
    - duration: 60
      arrivalRate: 5
scenarios:
  - name: "Analytics API Load Test"
    requests:
      - get:
          url: "/api/analytics/system-overview"
      - get:
          url: "/api/analytics/student-performance"
EOF

# Run load test
artillery run artillery-analytics.yml
```

### Database Stress Testing
```bash
php artisan tinker --execute="
// Create large dataset for stress testing
\$batchSize = 1000;
for (\$i = 0; \$i < 5; \$i++) {
    App\Models\Student::factory()->count(\$batchSize)->create();
    echo 'Created batch ' . (\$i + 1) . ' of ' . \$batchSize . ' students' . PHP_EOL;
}

// Test performance with large dataset
\$start = microtime(true);
\$service = app(App\Services\AnalyticsService::class);
\$overview = \$service->getSystemOverview();
\$end = microtime(true);
\$time = (\$end - \$start) * 1000;

echo 'Analytics with ' . \$overview['students']['total'] . ' students: ' . round(\$time, 2) . 'ms' . PHP_EOL;
"
```

## Conclusion

This testing framework provides comprehensive coverage of the analytics system functionality, performance, security, and reliability. Regular execution of these tests ensures the analytics system maintains high quality and performance standards as the application evolves.

For questions or issues with testing, refer to the main analytics documentation or contact the development team.