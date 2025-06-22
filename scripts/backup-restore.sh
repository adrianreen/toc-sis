#!/bin/bash

# TOC-SIS Backup Restoration Script
# Restore database and files from backup

set -e

# Configuration
DB_NAME="${DB_NAME:-toc_sis}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
BACKUP_BASE_DIR="${BACKUP_DIR:-/backups}"
APP_DIR="${APP_DIR:-/var/www/toc-sis}"

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

log_warn() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARN: $1${NC}"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
}

log_step() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] STEP: $1${NC}"
}

# Display usage information
usage() {
    cat << EOF
TOC-SIS Backup Restoration Script

Usage: $0 [OPTIONS] COMMAND

Commands:
    list-backups              List available backups
    restore-database BACKUP   Restore database from backup file
    restore-files BACKUP      Restore files from backup
    restore-full BACKUP       Restore both database and files
    test-restore BACKUP       Test restore to temporary database
    download-from-s3 DATE     Download backup from S3 for given date

Options:
    -h, --help               Show this help message
    -n, --dry-run           Show what would be done without executing
    -f, --force             Skip confirmation prompts
    -t, --target-db NAME    Use different database name for restore
    -v, --verbose           Verbose output

Examples:
    $0 list-backups
    $0 restore-database /backups/database/toc_sis_20240620_020000.sql.gz
    $0 test-restore /backups/database/toc_sis_20240620_020000.sql.gz
    $0 download-from-s3 20240620
    $0 restore-full /backups/database/toc_sis_20240620_020000.sql.gz

Environment Variables:
    DB_NAME                Database name (default: toc_sis)
    DB_USER                Database user (default: root)
    DB_PASS                Database password
    BACKUP_DIR             Backup directory (default: /backups)
    APP_DIR                Application directory (default: /var/www/toc-sis)
    AWS_S3_BUCKET          S3 bucket for cloud backups
EOF
}

# List available backups
list_backups() {
    log_step "Listing available backups..."
    
    echo "Database Backups:"
    echo "=================="
    if [ -d "${BACKUP_BASE_DIR}/database" ]; then
        ls -lah ${BACKUP_BASE_DIR}/database/toc_sis_*.sql.gz 2>/dev/null | \
        awk '{print $9, $5, $6, $7, $8}' | \
        column -t || echo "No database backups found"
    else
        echo "No database backup directory found"
    fi
    
    echo ""
    echo "Application Backups:"
    echo "==================="
    if [ -d "${BACKUP_BASE_DIR}/application" ]; then
        ls -lah ${BACKUP_BASE_DIR}/application/toc-sis-app_*.tar.gz 2>/dev/null | \
        awk '{print $9, $5, $6, $7, $8}' | \
        column -t || echo "No application backups found"
    else
        echo "No application backup directory found"
    fi
    
    echo ""
    echo "File Backups:"
    echo "============="
    if [ -d "${BACKUP_BASE_DIR}/files" ]; then
        ls -lah ${BACKUP_BASE_DIR}/files/storage_*.tar.gz 2>/dev/null | \
        awk '{print $9, $5, $6, $7, $8}' | \
        column -t || echo "No file backups found"
    else
        echo "No file backup directory found"
    fi
}

# Download backup from S3
download_from_s3() {
    local date="$1"
    
    if [ -z "$date" ]; then
        log_error "Date parameter required (format: YYYYMMDD)"
        return 1
    fi
    
    if [ -z "${AWS_S3_BUCKET}" ]; then
        log_error "AWS_S3_BUCKET environment variable not set"
        return 1
    fi
    
    log_step "Downloading backups from S3 for date: $date"
    
    # Create download directory
    local download_dir="${BACKUP_BASE_DIR}/downloaded/${date}"
    mkdir -p ${download_dir}
    
    # Download database backup
    log_info "Downloading database backup..."
    aws s3 cp s3://${AWS_S3_BUCKET}/database/daily/ ${download_dir}/ \
             --recursive --exclude "*" --include "*${date}*"
    
    # Download application backup  
    log_info "Downloading application backup..."
    aws s3 cp s3://${AWS_S3_BUCKET}/application/ ${download_dir}/ \
             --recursive --exclude "*" --include "*${date}*"
    
    # Download file backup
    log_info "Downloading file backup..."
    aws s3 cp s3://${AWS_S3_BUCKET}/files/storage_${date}.tar.gz ${download_dir}/ || true
    
    log_info "Downloaded backups to: ${download_dir}"
    ls -la ${download_dir}
}

# Validate backup file
validate_backup() {
    local backup_file="$1"
    
    if [ ! -f "$backup_file" ]; then
        log_error "Backup file not found: $backup_file"
        return 1
    fi
    
    # Check if it's a gzipped file
    if [[ "$backup_file" == *.gz ]]; then
        log_info "Checking backup file integrity..."
        if ! gunzip -t "$backup_file"; then
            log_error "Backup file is corrupted: $backup_file"
            return 1
        fi
        log_info "Backup file integrity check passed"
    fi
    
    return 0
}

# Create database backup before restore
create_pre_restore_backup() {
    local target_db="$1"
    local backup_file="${BACKUP_BASE_DIR}/pre-restore/pre_restore_${target_db}_$(date +%Y%m%d_%H%M%S).sql.gz"
    
    log_step "Creating pre-restore backup of ${target_db}..."
    
    mkdir -p $(dirname ${backup_file})
    
    if mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -e "USE ${target_db};" 2>/dev/null; then
        mysqldump --user=${DB_USER} ${DB_PASS:+--password=${DB_PASS}} \
                 --single-transaction --routines --triggers \
                 ${target_db} | gzip > ${backup_file}
        log_info "Pre-restore backup created: ${backup_file}"
    else
        log_info "Target database ${target_db} does not exist, skipping pre-restore backup"
    fi
}

# Restore database from backup
restore_database() {
    local backup_file="$1"
    local target_db="${2:-${DB_NAME}}"
    local create_backup="${3:-true}"
    
    log_step "Restoring database from: $backup_file"
    log_info "Target database: $target_db"
    
    # Validate backup file
    validate_backup "$backup_file"
    
    # Create pre-restore backup if requested
    if [ "$create_backup" = "true" ]; then
        create_pre_restore_backup "$target_db"
    fi
    
    # Confirm operation unless forced
    if [ "$FORCE" != "true" ]; then
        echo -n "Are you sure you want to restore to database '$target_db'? (yes/no): "
        read -r confirmation
        if [ "$confirmation" != "yes" ]; then
            log_warn "Restore operation cancelled"
            return 1
        fi
    fi
    
    # Create target database if it doesn't exist
    log_info "Creating database if not exists: $target_db"
    mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -e "CREATE DATABASE IF NOT EXISTS \`${target_db}\`;"
    
    # Restore from backup
    log_info "Restoring database..."
    if [[ "$backup_file" == *.gz ]]; then
        gunzip -c "$backup_file" | mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} "$target_db"
    else
        mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} "$target_db" < "$backup_file"
    fi
    
    # Verify restoration
    local student_count=$(mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -N -e "SELECT COUNT(*) FROM ${target_db}.students;" 2>/dev/null || echo "0")
    local grade_count=$(mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -N -e "SELECT COUNT(*) FROM ${target_db}.student_grade_records;" 2>/dev/null || echo "0")
    
    log_info "Database restore completed!"
    log_info "Verification - Students: ${student_count}, Grade records: ${grade_count}"
}

# Test restore to temporary database
test_restore() {
    local backup_file="$1"
    local test_db="toc_sis_test_restore_$(date +%s)"
    
    log_step "Testing restore with temporary database: $test_db"
    
    # Restore to test database
    restore_database "$backup_file" "$test_db" "false"
    
    # Run basic validation queries
    log_info "Running validation tests..."
    
    local tests_passed=0
    local tests_total=0
    
    # Test 1: Check if critical tables exist
    ((tests_total++))
    if mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -e "DESCRIBE ${test_db}.students;" >/dev/null 2>&1; then
        log_info "✓ Students table exists"
        ((tests_passed++))
    else
        log_error "✗ Students table missing"
    fi
    
    # Test 2: Check data integrity
    ((tests_total++))
    local student_count=$(mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -N -e "SELECT COUNT(*) FROM ${test_db}.students;" 2>/dev/null || echo "0")
    if [ "$student_count" -gt 0 ]; then
        log_info "✓ Student data present (${student_count} records)"
        ((tests_passed++))
    else
        log_error "✗ No student data found"
    fi
    
    # Test 3: Check relationships
    ((tests_total++))
    local orphaned_enrolments=$(mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -N -e "
        SELECT COUNT(*) FROM ${test_db}.enrolments e 
        LEFT JOIN ${test_db}.students s ON e.student_id = s.id 
        WHERE s.id IS NULL;" 2>/dev/null || echo "0")
    if [ "$orphaned_enrolments" -eq 0 ]; then
        log_info "✓ No orphaned enrolment records"
        ((tests_passed++))
    else
        log_error "✗ Found ${orphaned_enrolments} orphaned enrolment records"
    fi
    
    # Cleanup test database
    log_info "Cleaning up test database..."
    mysql -u${DB_USER} ${DB_PASS:+-p${DB_PASS}} -e "DROP DATABASE \`${test_db}\`;"
    
    # Report results
    log_info "Test restore completed: ${tests_passed}/${tests_total} tests passed"
    
    if [ "$tests_passed" -eq "$tests_total" ]; then
        log_info "✓ Backup file appears to be valid and complete"
        return 0
    else
        log_error "✗ Backup file may have issues"
        return 1
    fi
}

# Restore files from backup
restore_files() {
    local backup_file="$1"
    local target_dir="${APP_DIR}/storage/app"
    
    log_step "Restoring files from: $backup_file"
    
    # Validate backup file
    validate_backup "$backup_file"
    
    # Create backup of current files
    if [ -d "$target_dir" ]; then
        local current_backup="${BACKUP_BASE_DIR}/pre-restore/files_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
        mkdir -p $(dirname ${current_backup})
        tar -czf ${current_backup} -C ${target_dir} . || true
        log_info "Current files backed up to: ${current_backup}"
    fi
    
    # Confirm operation
    if [ "$FORCE" != "true" ]; then
        echo -n "Are you sure you want to restore files to '$target_dir'? (yes/no): "
        read -r confirmation
        if [ "$confirmation" != "yes" ]; then
            log_warn "File restore operation cancelled"
            return 1
        fi
    fi
    
    # Create target directory
    mkdir -p "$target_dir"
    
    # Restore files
    log_info "Extracting files..."
    tar -xzf "$backup_file" -C "$target_dir"
    
    # Set proper permissions
    chown -R www-data:www-data "$target_dir" || true
    
    log_info "File restore completed to: $target_dir"
}

# Parse command line arguments
FORCE="false"
DRY_RUN="false"
VERBOSE="false"
TARGET_DB=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            usage
            exit 0
            ;;
        -f|--force)
            FORCE="true"
            shift
            ;;
        -n|--dry-run)
            DRY_RUN="true"
            shift
            ;;
        -v|--verbose)
            VERBOSE="true"
            set -x
            shift
            ;;
        -t|--target-db)
            TARGET_DB="$2"
            shift 2
            ;;
        list-backups)
            list_backups
            exit 0
            ;;
        restore-database)
            if [ -z "$2" ]; then
                log_error "Backup file parameter required"
                exit 1
            fi
            restore_database "$2" "${TARGET_DB:-${DB_NAME}}"
            exit 0
            ;;
        restore-files)
            if [ -z "$2" ]; then
                log_error "Backup file parameter required"
                exit 1
            fi
            restore_files "$2"
            exit 0
            ;;
        restore-full)
            if [ -z "$2" ]; then
                log_error "Backup file parameter required"
                exit 1
            fi
            backup_file="$2"
            restore_database "$backup_file" "${TARGET_DB:-${DB_NAME}}"
            # Try to find corresponding file backup
            backup_date=$(basename "$backup_file" | grep -o '[0-9]\{8\}_[0-9]\{6\}' | head -1)
            file_backup="${BACKUP_BASE_DIR}/files/storage_${backup_date}.tar.gz"
            if [ -f "$file_backup" ]; then
                restore_files "$file_backup"
            else
                log_warn "No corresponding file backup found for date: $backup_date"
            fi
            exit 0
            ;;
        test-restore)
            if [ -z "$2" ]; then
                log_error "Backup file parameter required"
                exit 1
            fi
            test_restore "$2"
            exit 0
            ;;
        download-from-s3)
            if [ -z "$2" ]; then
                log_error "Date parameter required (format: YYYYMMDD)"
                exit 1
            fi
            download_from_s3 "$2"
            exit 0
            ;;
        *)
            log_error "Unknown command: $1"
            usage
            exit 1
            ;;
    esac
done

# Show usage if no command provided
usage
exit 1