# SQLite to MySQL-Only Migration Guide

## Executive Summary

This document outlines the complete migration from dual SQLite/MySQL support to MySQL-only in the TOC-SIS application. The current dual-database approach causes systematic test failures and adds unnecessary complexity with zero benefits.

**Current State**: 
- Production/Development: MySQL (working)  
- Testing: SQLite (completely broken - 54/54 tests fail)
- Root Cause: MySQL-specific migration syntax incompatible with SQLite

**Target State**:  
- All environments: MySQL
- Unified database engine across development, testing, and production
- Functional test suite

## Problem Analysis

### Current Issues

1. **Test Suite Failure**: All 54 feature tests fail due to constraint migration incompatibility
2. **Cross-Database Syntax**: Migration `2025_06_22_092733_add_enrolment_constraints.php` uses MySQL `ALTER TABLE ADD CONSTRAINT` syntax that SQLite doesn't support
3. **Unused SQLite Database**: 0-byte `database.sqlite` file serves no purpose
4. **Maintenance Overhead**: Supporting two database engines with different SQL syntax requirements

### Files Requiring Changes

**Configuration Files**:
- `config/database.php` - Default connection set to 'sqlite'
- `config/queue.php` - Queue database references SQLite
- `phpunit.xml` - Test environment forces SQLite
- `.env.example` - Default example uses SQLite
- `.github/workflows/toc-sis-testing.yml` - GitHub Actions configured for SQLite

**Application Code**:
- `app/Console/Commands/PerformanceMonitor.php` - Hardcoded SQLite database size calculation

**Database Files**:
- `database/database.sqlite` - Empty 0-byte file

## Migration Plan

### Phase 1: Configuration Updates

#### 1.1 Update Default Database Connection
**File**: `config/database.php`
**Change**: Line 19
```php
// BEFORE
'default' => env('DB_CONNECTION', 'sqlite'),

// AFTER  
'default' => env('DB_CONNECTION', 'mysql'),
```

#### 1.2 Update Testing Configuration
**File**: `phpunit.xml`
**Change**: Lines 25-26
```xml
<!-- BEFORE -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>

<!-- AFTER -->
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="toc_sis_test"/>
```

#### 1.3 Update Example Environment
**File**: `.env.example`
**Change**: Lines 29-32
```bash
# BEFORE
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database/database.sqlite

# AFTER
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=toc_sis
```

### Phase 2: Application Code Updates

#### 2.1 Fix Performance Monitor Command
**File**: `app/Console/Commands/PerformanceMonitor.php`
**Method**: `getDatabaseSize()` (lines 338-343)

**BEFORE**:
```php
private function getDatabaseSize()
{
    // This is SQLite specific - adjust for other databases
    $dbPath = database_path('database.sqlite');
    return file_exists($dbPath) ? round(filesize($dbPath) / 1024 / 1024, 2).' MB' : 'Unknown';
}
```

**AFTER**:
```php
private function getDatabaseSize()
{
    try {
        $databaseName = config('database.connections.mysql.database');
        $result = DB::selectOne("
            SELECT 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
            FROM information_schema.tables 
            WHERE table_schema = ?
        ", [$databaseName]);
        
        return ($result ? $result->size_mb : 0) . ' MB';
    } catch (\Exception $e) {
        return 'Unknown';
    }
}
```

### Phase 3: CI/CD Pipeline Updates

#### 3.1 Update GitHub Actions Workflow
**File**: `.github/workflows/toc-sis-testing.yml`

**Changes Required**:
1. Replace SQLite service with MySQL service
2. Update PHP extensions (remove `sqlite`, `pdo_sqlite`)  
3. Update database configuration
4. Remove SQLite database creation steps

**Key sections to update**:

**Environment Variables** (line 35):
```yaml
# BEFORE
env:
  DB_CONNECTION: sqlite

# AFTER  
env:
  DB_CONNECTION: mysql
  DB_HOST: 127.0.0.1
  DB_PORT: 3306
  DB_DATABASE: toc_sis_test
  DB_USERNAME: root
  DB_PASSWORD: root
```

**Services** (add MySQL service):
```yaml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ALLOW_EMPTY_PASSWORD: yes
      MYSQL_DATABASE: toc_sis_test
    ports:
      - 3306:3306
    options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
```

**PHP Extensions** (remove SQLite references):
```yaml
# BEFORE
extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv

# AFTER
extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, pdo_mysql, bcmath, soap, intl, gd, exif, iconv
```

**Database Setup Steps** (replace SQLite creation):
```yaml
# REMOVE these steps
- name: Create SQLite database
  run: touch database/database.sqlite

# ADD this step
- name: Wait for MySQL
  run: |
    while ! mysqladmin ping -h127.0.0.1 --silent; do
      echo 'waiting for mysql...'
      sleep 1
    done
```

### Phase 4: Database Setup

#### 4.1 Create Test Database
```sql
CREATE DATABASE IF NOT EXISTS toc_sis_test;
GRANT ALL PRIVILEGES ON toc_sis_test.* TO 'tocsis_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

#### 4.2 Verify Database Access
```bash
mysql -u tocsis_user -p'Xa32p!gfY29w' -h 127.0.0.1 -e "SHOW DATABASES;"
```

### Phase 5: Cleanup

#### 5.1 Remove SQLite Files
```bash
rm -f /var/www/toc-sis/database/database.sqlite
```

#### 5.2 Update .gitignore (if needed)
Ensure SQLite files are not tracked:
```
*.sqlite
*.sqlite3
```

## Implementation Steps

### Step 1: Pre-Migration Verification
```bash
# Verify current state
php artisan migrate:status
php artisan test tests/Feature/ExampleTest.php
mysql -u tocsis_user -p'Xa32p!gfY29w' -h 127.0.0.1 toc_sis -e "SELECT COUNT(*) FROM users;"
```

### Step 2: Create Test Database
```bash
mysql -u tocsis_user -p'Xa32p!gfY29w' -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS toc_sis_test;"
```

### Step 3: Apply Configuration Changes
1. Update `config/database.php` - change default to 'mysql'
2. Update `phpunit.xml` - change to MySQL with `toc_sis_test`  
3. Update `app/Console/Commands/PerformanceMonitor.php` - fix database size method

### Step 4: Test Migration Setup
```bash
# Test migrations on test database  
php artisan migrate --database=mysql --env=testing
```

### Step 5: Run Test Suite
```bash
php artisan test
```

### Step 6: Update CI/CD Pipeline
1. Update `.github/workflows/toc-sis-testing.yml`
2. Test workflow by creating a test branch and pushing

### Step 7: Cleanup
```bash
rm -f database/database.sqlite
```

## Verification Procedures

### Database Connection Test
```bash
php artisan tinker --execute="
echo 'Production DB: '; 
echo \App\Models\User::count() . ' users\n';
DB::connection('mysql')->statement('USE toc_sis_test');
echo 'Test DB connected successfully\n';
"
```

### Migration Compatibility Test  
```bash
# This should now work without errors
php artisan test tests/Feature/AnalyticsApiTest.php --filter="system_overview_endpoint_returns_valid_structure"
```

### Full Test Suite Validation
```bash
php artisan test --testsuite=Feature
```

## Expected Outcomes

**Before Migration**:
- ❌ 54/54 feature tests fail
- ❌ SQLite constraint syntax errors
- ❌ Dual database maintenance overhead
- ❌ Production-test environment mismatch

**After Migration**:
- ✅ All feature tests should pass (constraint migration compatible)
- ✅ Single database engine (MySQL) across all environments  
- ✅ Production parity in testing
- ✅ Simplified configuration and maintenance
- ✅ CI/CD pipeline functionality restored

## Risk Assessment

**Low Risk Migration**:
- No production data affected (production already uses MySQL)
- No functionality changes (same features, same database engine)
- Reversible changes (can restore SQLite config if needed)
- Test database is isolated from production

**Potential Issues**:
- CI/CD may need MySQL service configuration
- Test database permissions need verification  
- Performance monitoring command needs testing

## Rollback Plan

If issues arise, restore original configuration:

1. **Restore phpunit.xml**:
   ```xml
   <env name="DB_CONNECTION" value="sqlite"/>
   <env name="DB_DATABASE" value=":memory:"/>
   ```

2. **Restore config/database.php**:
   ```php
   'default' => env('DB_CONNECTION', 'sqlite'),
   ```

3. **Recreate SQLite file**:
   ```bash
   touch database/database.sqlite
   ```

**Note**: Rollback returns to broken test state but doesn't affect production.

## Success Metrics

1. **Test Suite Recovery**: 0 failing tests (down from 54 failing)
2. **Migration Success**: All migrations run without errors in test environment  
3. **CI/CD Functionality**: GitHub Actions workflows complete successfully
4. **Production Stability**: No impact on production database or functionality

## Conclusion

This migration eliminates a fundamental architectural inconsistency that prevents proper testing and deployment validation. The change is low-risk, high-reward, and addresses the root cause of systematic test failures.

**Recommendation**: Execute this migration immediately to restore test suite functionality and establish proper development workflow.