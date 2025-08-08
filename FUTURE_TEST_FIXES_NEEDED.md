# Future Test Suite Fixes Required

## Status After SQLite→MySQL Migration

**✅ FIXED**: Database configuration cross-compatibility issues
- Eliminated `SQLSTATE[HY000]: General error: 1 near "CONSTRAINT": syntax error`
- Unified MySQL across development/testing/production
- Tests can now run and execute business logic

**❌ REMAINING**: 34/55 tests still failing (62% failure rate)

## Category 1: Data Validation Issues

### Issue: Column Truncation Errors
**Error**: `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'status' at row 1`
**Files Affected**: `tests/Feature/ArchitectureValidationTest.php`
**Root Cause**: Test factories generating data that doesn't match database column constraints

**Required Fixes**:
1. Review Student model factory - `status` column values
2. Check enum definitions in migration vs factory values
3. Ensure test data generation respects database constraints

```php
// Likely issue in database/factories/StudentFactory.php
'status' => $this->faker->randomElement(['active', 'inactive', 'graduated']),
// vs database enum: ['active', 'withdrawn', 'deferred', 'completed']
```

## Category 2: Foreign Key Constraint Violations

### Issue: Referential Integrity
**Error**: `Cannot add or update a child row: a foreign key constraint fails`
**Files Affected**: `tests/Feature/ArchitectureValidationTest.php` (multiple test methods)
**Root Cause**: Tests creating child records with non-existent parent IDs

**Required Fixes**:
1. Fix test data setup to create proper parent-child relationships
2. Ensure Programme → ProgrammeInstance → ModuleInstance hierarchy in test data
3. Review curriculum linkage test data (`programme_instance_curriculum` table)

**Example Fix Needed**:
```php
// Current (broken)
$moduleInstance = ModuleInstance::create(['programme_instance_id' => 999]);

// Fixed
$programmeInstance = ProgrammeInstance::factory()->create();
$moduleInstance = ModuleInstance::factory()->create([
    'programme_instance_id' => $programmeInstance->id
]);
```

## Category 3: API Response Structure Issues

### Issue: Missing Response Keys
**Error**: `Failed asserting that an array has the key 'assessments'`
**Files Affected**: `tests/Feature/AnalyticsApiTest.php`
**Root Cause**: Analytics service not returning expected data structure

**Required Fixes**:
1. Review `AnalyticsService` class implementation
2. Check if assessment data is being populated in analytics responses
3. Verify API controllers return expected JSON structure

**Investigation Needed**:
```php
// Check app/Services/AnalyticsService.php
public function getSystemOverview()
{
    // Missing 'assessments' key in returned array
}
```

## Category 4: Cache Management Issues

### Issue: Cache Endpoint Failures  
**Error**: Various cache-related test failures
**Files Affected**: `tests/Feature/AnalyticsCacheTest.php`
**Root Cause**: Cache implementation not matching test expectations

**Required Fixes**:
1. Review cache invalidation logic
2. Check cache key generation and storage
3. Verify cache expiration handling

## Category 5: Authentication/Authorization Issues

### Issue: Role-Based Access Control
**Error**: Various authorization failures
**Files Affected**: `tests/Feature/AnalyticsSecurityTest.php`
**Root Cause**: Test users not properly set up with required roles/permissions

**Required Fixes**:
1. Review user factory role assignment
2. Check middleware role checking logic
3. Ensure test users have proper permissions for test scenarios

## Category 6: Command Exit Code Issues

### Issue: Artisan Commands Failing
**Error**: `Expected status code 0 but received 1`
**Files Affected**: `tests/Feature/ArchitectureValidationTest.php`
**Root Cause**: Architecture validation command encountering errors

**Required Fixes**:
1. Debug `architecture:validate` command execution
2. Check for underlying data issues causing command failures
3. Review command error handling and exit codes

## Priority Order for Fixes

### HIGH PRIORITY (Blocking Multiple Tests)
1. **Data Factory Issues** - Fix student status enum mismatch
2. **Foreign Key Setup** - Fix test data relationship creation
3. **Analytics Service** - Fix missing response structure

### MEDIUM PRIORITY (Specific Feature Issues)  
4. **Cache Implementation** - Fix cache-related test failures
5. **Command Execution** - Debug architecture validation command
6. **Authorization Setup** - Fix role-based test scenarios

### LOW PRIORITY (Edge Cases)
7. **API Validation** - Fine-tune input parameter validation tests
8. **Performance Tests** - Optimize analytics performance test scenarios

## Testing Strategy Moving Forward

### Step 1: Fix One Category at a Time
Focus on data validation issues first (highest impact on test count)

### Step 2: Verify Each Fix
Run specific test classes after each fix:
```bash
# After fixing data factories
php artisan test tests/Feature/ArchitectureValidationTest.php

# After fixing analytics service  
php artisan test tests/Feature/AnalyticsApiTest.php

# After fixing cache issues
php artisan test tests/Feature/AnalyticsCacheTest.php
```

### Step 3: Track Progress
Document pass/fail rates after each category fix to measure progress.

## Current Working Tests (Don't Break These)

**Passing Tests** (21/55):
- Basic authentication flows
- Some analytics endpoints
- Cache creation/retrieval basics
- Rate limiting functionality
- CORS header validation
- SQL injection protection (basic)

## Expected Outcomes After All Fixes

**Realistic Target**: 80-90% test pass rate (45-50/55 tests passing)
**Timeline Estimate**: 2-3 weeks for systematic fixes
**Validation**: Each fix should incrementally improve pass rate

## Notes

- The SQLite→MySQL migration resolved the foundational issue
- Remaining failures are legitimate application bugs/test issues  
- Each category represents different types of problems requiring different solutions
- Progress can be measured incrementally as each category gets fixed