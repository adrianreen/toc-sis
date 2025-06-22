# TOC-SIS Workflow Testing System

This comprehensive testing system validates the complete 4-level Programme-Module architecture and all critical workflows in the TOC Student Information System.

## Quick Start

```bash
# Run complete workflow testing suite
./scripts/run-workflow-tests.sh

# Quick validation only
./scripts/run-workflow-tests.sh --quick

# Generate fresh test data and run full tests
./scripts/run-workflow-tests.sh --full --reset-data
```

## Testing Scripts Overview

### 🎯 Main Orchestrator
- **`run-workflow-tests.sh`** - Complete testing orchestrator that runs all test suites
  - Manages test execution order and dependencies
  - Generates comprehensive reports
  - Handles parallel execution and cleanup

### 🔍 Validation Scripts
- **`workflow-validation.sh`** - Validates system integrity and architecture
  - Database schema validation
  - 4-level architecture integrity
  - Two-path enrolment system validation
  - Role-based access control testing

### 🤖 Automation Scripts
- **`workflow-automation.sh`** - Automated workflow scenario testing
  - Student onboarding workflows
  - Standalone module enrolment
  - Assessment management cycles
  - Academic year setup procedures

### 📊 Data Generation
- **`generate-test-data.sh`** - Creates realistic test data
  - Diverse student populations
  - Comprehensive programme structures
  - Realistic assessment strategies
  - Edge case and problematic data

### ⚡ Performance Testing
- **`analytics-performance-test.sh`** - System performance validation
  - Database query performance
  - API endpoint response times
  - Concurrent user simulation
  - Memory usage monitoring

## Test Suite Options

### Full Test Suite (`--full`)
Complete comprehensive testing including:
- ✅ Prerequisites checking
- ✅ Environment preparation
- ✅ Database schema validation
- ✅ Architecture integrity testing
- ✅ Comprehensive workflow validation
- ✅ Automated workflow scenarios
- ✅ Performance testing
- ✅ Integration testing
- ✅ Stress testing
- ✅ Report generation

### Quick Test Suite (`--quick`)
Essential validation testing:
- ✅ Prerequisites checking
- ✅ Environment preparation
- ✅ Schema validation
- ✅ Workflow validation
- ✅ Workflow automation
- ✅ Basic reporting

### Validation Only (`--validation-only`)
System integrity focused:
- ✅ Schema validation
- ✅ Architecture validation
- ✅ Workflow validation

### Performance Only (`--performance-only`)
Performance and stress testing:
- ✅ Performance benchmarks
- ✅ Stress testing
- ✅ Load simulation

## Configuration Options

### Test Data Sizes
```bash
# Small dataset (50 students, 3 programmes)
./scripts/run-workflow-tests.sh --size small

# Medium dataset (200 students, 5 programmes) - Default
./scripts/run-workflow-tests.sh --size medium

# Large dataset (500 students, 8 programmes)
./scripts/run-workflow-tests.sh --size large

# Bulk dataset (1000+ students, 12 programmes)
./scripts/run-workflow-tests.sh --size bulk
```

### Execution Modes
```bash
# Reset all test data before testing
./scripts/run-workflow-tests.sh --reset-data

# Run tests in parallel (experimental)
./scripts/run-workflow-tests.sh --parallel

# Cleanup test data after completion
./scripts/run-workflow-tests.sh --cleanup

# Skip report generation
./scripts/run-workflow-tests.sh --no-reports
```

## Individual Script Usage

### Workflow Validation
```bash
# Complete validation
./scripts/workflow-validation.sh

# Schema only
./scripts/workflow-validation.sh --schema-only

# Architecture only
./scripts/workflow-validation.sh --architecture-only

# Performance only
./scripts/workflow-validation.sh --performance-only
```

### Workflow Automation
```bash
# All scenarios
./scripts/workflow-automation.sh

# Student journey scenarios only
./scripts/workflow-automation.sh --scenario-1

# Administrative scenarios only
./scripts/workflow-automation.sh --scenario-2

# Multi-role scenarios only
./scripts/workflow-automation.sh --scenario-3

# Performance testing only
./scripts/workflow-automation.sh --performance-only
```

### Test Data Generation
```bash
# Generate medium dataset
./scripts/generate-test-data.sh

# Generate with specific size
./scripts/generate-test-data.sh --size large

# Reset database and generate fresh data
./scripts/generate-test-data.sh --reset

# Generate without problematic edge cases
./scripts/generate-test-data.sh --no-problematic

# Generate students only
./scripts/generate-test-data.sh --students-only
```

## Report Generation

### Automated Reports
The testing system automatically generates comprehensive reports in:
- `storage/logs/` - Individual test logs
- `storage/reports/workflow-testing/` - Comprehensive markdown reports

### Report Contents
- **Test Suite Results** - Pass/fail status with timing
- **System Statistics** - Database counts and metrics
- **Performance Metrics** - Query times and memory usage
- **Recommendations** - Action items based on results
- **Error Details** - Specific failure information

## Workflow Scenarios Tested

### 1. Student Journey Workflows
#### 1.1 New Student Onboarding - Programme Route
- Initial enquiry processing
- Student record creation
- Programme enrolment with automatic grade record creation
- Status progression (enquiry → enrolled → active)
- Dashboard access validation

#### 1.2 Standalone Module Enrolment - Asynchronous Route
- CPD student enquiry
- Standalone module availability checking
- Module enrolment without programme linkage
- Assessment component grade record creation
- Self-paced learning workflow

#### 1.3 Academic Exception Handling
- Deferral request processing
- Programme instance transfer
- Grade record migration
- Notification workflows

### 2. Administrative Workflows
#### 2.1 Academic Year Setup
- Programme instance creation for new academic year
- Module instance scheduling
- Curriculum linking via pivot tables
- Resource allocation and validation

#### 2.2 Bulk Enrolment Management
- High-volume student processing
- Bulk programme enrolments
- Grade record batch creation
- Performance monitoring

### 3. Multi-Role Collaboration Workflows
#### 3.1 Assessment Management - End-to-End Grading
- Assessment preparation and briefing
- Student submission processing
- Grading workflow with visibility controls
- External examiner review simulation
- Results release automation

#### 3.2 Student Progression Monitoring
- Academic standards committee review
- Risk categorization and intervention planning
- Progress tracking and reporting

### 4. Integration Workflows
#### 4.1 Azure AD Integration
- User authentication flow testing
- Role mapping validation
- Student account linking

#### 4.2 Email System Integration
- Automated notification delivery
- Template rendering validation
- Bulk email processing

#### 4.3 Notification System
- Grade release notifications
- Assessment deadline reminders
- Administrative approval workflows

## Performance Benchmarks

### Expected Performance Targets
- **Student Dashboard Load**: < 3 seconds
- **Enrolment Processing**: < 2 seconds per student
- **Grade Record Creation**: < 100ms per record
- **Database Queries**: < 1 second for complex queries
- **Email Queue Processing**: 100+ emails/minute

### Stress Testing
- **Concurrent Users**: 100+ simultaneous users
- **Bulk Operations**: 500+ students processed in batch
- **Memory Usage**: < 256MB per request
- **Database Connections**: 50+ concurrent connections

## Validation Checklists

### ✅ Core Architecture Validation
- [x] Programme blueprints with correct structure
- [x] Programme instances with intake management
- [x] Module blueprints with assessment strategies
- [x] Module instances with tutor assignments
- [x] Curriculum linking via pivot tables
- [x] Two-path enrolment system (programme vs module)
- [x] Grade records with assessment components
- [x] Foreign key relationships and constraints

### ✅ Workflow Validation
- [x] Student onboarding workflows
- [x] Enrolment service functionality
- [x] Assessment management cycles
- [x] Grade visibility controls
- [x] Notification delivery systems
- [x] Role-based access control
- [x] Data integrity and validation

### ✅ Performance Validation
- [x] Response time benchmarks
- [x] Concurrent user handling
- [x] Memory usage limits
- [x] Database query optimization
- [x] Email system throughput

## Troubleshooting

### Common Issues

#### Test Data Generation Fails
```bash
# Check database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';"

# Reset database if corrupted
./scripts/generate-test-data.sh --reset

# Check disk space
df -h
```

#### Workflow Validation Failures
```bash
# Run specific validation
./scripts/workflow-validation.sh --schema-only

# Check Laravel application
php artisan --version

# Verify environment
php artisan config:show
```

#### Performance Issues
```bash
# Check system resources
top
free -h

# Optimize database
php artisan optimize

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### Getting Help

1. **Check Prerequisites**: Ensure all dependencies are installed
2. **Review Logs**: Check `storage/logs/` for detailed error information
3. **Run Individual Tests**: Isolate issues by running specific test components
4. **Validate Environment**: Ensure Laravel and database are properly configured

## Environment Variables

```bash
# Test configuration
export TEST_SUITE=full                    # full, quick, validation-only, performance-only
export DATA_SIZE=medium                   # small, medium, large, bulk
export RESET_DATA=false                   # true/false
export PARALLEL_MODE=false                # true/false (experimental)
export GENERATE_REPORTS=true              # true/false
export CLEANUP_AFTER=false                # true/false

# Application configuration
export BASE_URL=http://localhost:8000     # Laravel application URL
export TEST_USER_EMAIL=test@example.com   # Test user email
export NOTIFICATION_TEST=false            # Enable notification testing
```

## Continuous Integration

### For CI/CD Pipelines
```bash
# Quick validation for CI
./scripts/run-workflow-tests.sh --quick --no-reports

# Full testing for deployment validation
./scripts/run-workflow-tests.sh --full --reset-data --cleanup
```

### Exit Codes
- `0`: All tests passed
- `1`: Critical failures detected
- `2`: Warnings detected but no failures

## Best Practices

### Before Production Deployment
1. Run full test suite with fresh data: `./scripts/run-workflow-tests.sh --full --reset-data`
2. Review all generated reports for warnings
3. Validate performance benchmarks meet requirements
4. Test with production-like data volumes
5. Verify all integrations are functional

### Regular Maintenance
1. Run quick tests after code changes: `./scripts/run-workflow-tests.sh --quick`
2. Weekly full validation: `./scripts/run-workflow-tests.sh --full`
3. Monthly performance testing with large datasets
4. Quarterly comprehensive testing with bulk data

### Development Workflow
1. Generate test data: `./scripts/generate-test-data.sh --size small`
2. Validate changes: `./scripts/workflow-validation.sh`
3. Test specific workflows: `./scripts/workflow-automation.sh --scenario-1`
4. Performance check: `./scripts/workflow-validation.sh --performance-only`

## Integration with WORKFLOW_TESTING_SCENARIOS.md

This testing system directly implements and automates the scenarios documented in `WORKFLOW_TESTING_SCENARIOS.md`:

- **Scenario 1.1**: Automated in `workflow-automation.sh` as `test_scenario_1_1_student_onboarding()`
- **Scenario 1.2**: Automated in `workflow-automation.sh` as `test_scenario_1_2_standalone_module()`
- **Scenario 2.1**: Automated in `workflow-automation.sh` as `test_scenario_2_1_academic_year_setup()`
- **Scenario 3.1**: Automated in `workflow-automation.sh` as `test_scenario_3_1_assessment_management()`

Each automated test validates the manual workflow procedures and ensures system functionality matches the documented requirements.

## Contributing

When adding new workflow scenarios:

1. Document the scenario in `WORKFLOW_TESTING_SCENARIOS.md`
2. Implement automation in `workflow-automation.sh`
3. Add validation checks in `workflow-validation.sh`
4. Update test data generation if needed
5. Add performance benchmarks if applicable
6. Update this README with new scenarios

## Support

For questions or issues with the workflow testing system:

1. Check this README for common solutions
2. Review the detailed scenario documentation
3. Examine generated reports for specific error details
4. Run individual test components to isolate issues
5. Verify system prerequisites and environment configuration

The workflow testing system is designed to provide comprehensive validation of the TOC-SIS 4-level Programme-Module architecture and ensure reliable academic administration functionality.