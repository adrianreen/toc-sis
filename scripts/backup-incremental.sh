#!/bin/bash

# TOC-SIS Incremental Backup Script
# Hourly backup of critical data that has changed

set -e

# Configuration
DB_NAME="${DB_NAME:-toc_sis}"
DB_USER="${BACKUP_DB_USER:-backup_user}"
DB_PASS="${BACKUP_DB_PASS:-backup_password}"
BACKUP_BASE_DIR="${BACKUP_DIR:-/backups}"
DATE=$(date +%Y%m%d_%H%M%S)
HOUR=$(date +%H)

# Critical tables that need frequent backup
CRITICAL_TABLES=(
    "student_grade_records"
    "enrolments"
    "students"
    "users"
    "notifications"
    "email_logs"
    "activity_log"
)

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Logging
LOG_FILE="/var/log/toc-sis-incremental-backup.log"
exec 1> >(tee -a ${LOG_FILE})
exec 2> >(tee -a ${LOG_FILE} >&2)

log_info() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

log_warn() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARN: $1${NC}"
}

log_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
}

# Create incremental backup directory
create_directories() {
    local incremental_dir="${BACKUP_BASE_DIR}/incremental/$(date +%Y%m%d)"
    mkdir -p ${incremental_dir}
    echo ${incremental_dir}
}

# Backup critical tables with recent changes
backup_critical_tables() {
    local backup_dir="$1"
    local changes_found=false
    
    log_info "Starting incremental backup of critical tables..."
    
    for table in "${CRITICAL_TABLES[@]}"; do
        log_info "Processing table: ${table}"
        
        # Check if table exists
        if ! mysql -u${DB_USER} -p${DB_PASS} -e "DESCRIBE ${DB_NAME}.${table};" >/dev/null 2>&1; then
            log_warn "Table ${table} does not exist, skipping"
            continue
        fi
        
        # Check if table has updated_at column
        local has_updated_at=$(mysql -u${DB_USER} -p${DB_PASS} -N -e "
            SELECT COUNT(*) 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA='${DB_NAME}' 
            AND TABLE_NAME='${table}' 
            AND COLUMN_NAME='updated_at';"
        )
        
        local backup_file="${backup_dir}/${table}_${DATE}.sql.gz"
        
        if [ "${has_updated_at}" -eq 1 ]; then
            # Backup records modified in the last hour
            local where_clause="updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            
            # Check if there are recent changes
            local change_count=$(mysql -u${DB_USER} -p${DB_PASS} -N -e "
                SELECT COUNT(*) FROM ${DB_NAME}.${table} WHERE ${where_clause};"
            )
            
            if [ "${change_count}" -gt 0 ]; then
                log_info "Found ${change_count} recent changes in ${table}"
                changes_found=true
                
                # Create incremental backup
                mysqldump --user=${DB_USER} --password=${DB_PASS} \
                         --single-transaction --complete-insert \
                         --where="${where_clause}" \
                         ${DB_NAME} ${table} | gzip > ${backup_file}
                
                log_info "Incremental backup created: ${backup_file}"
            else
                log_info "No recent changes in ${table}"
            fi
        else
            # For tables without updated_at, check created_at or backup all
            local has_created_at=$(mysql -u${DB_USER} -p${DB_PASS} -N -e "
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA='${DB_NAME}' 
                AND TABLE_NAME='${table}' 
                AND COLUMN_NAME='created_at';"
            )
            
            if [ "${has_created_at}" -eq 1 ]; then
                local where_clause="created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
                local change_count=$(mysql -u${DB_USER} -p${DB_PASS} -N -e "
                    SELECT COUNT(*) FROM ${DB_NAME}.${table} WHERE ${where_clause};"
                )
                
                if [ "${change_count}" -gt 0 ]; then
                    log_info "Found ${change_count} new records in ${table}"
                    changes_found=true
                    
                    mysqldump --user=${DB_USER} --password=${DB_PASS} \
                             --single-transaction --complete-insert \
                             --where="${where_clause}" \
                             ${DB_NAME} ${table} | gzip > ${backup_file}
                    
                    log_info "Incremental backup created: ${backup_file}"
                else
                    log_info "No new records in ${table}"
                fi
            else
                log_warn "Table ${table} has no timestamp columns, skipping incremental backup"
            fi
        fi
    done
    
    if [ "${changes_found}" = true ]; then
        log_info "Incremental backup completed with changes"
        return 0
    else
        log_info "No changes found, no incremental backup needed"
        return 1
    fi
}

# Upload incrementals to cloud storage
upload_incrementals() {
    local backup_dir="$1"
    
    if command -v aws >/dev/null 2>&1 && [ -n "${AWS_S3_BUCKET}" ]; then
        log_info "Uploading incremental backups to S3..."
        
        # Upload all files in the backup directory
        if aws s3 sync ${backup_dir}/ s3://${AWS_S3_BUCKET}/incremental/$(date +%Y%m%d)/ \
                     --storage-class STANDARD_IA; then
            log_info "Incremental backups uploaded to S3"
        else
            log_error "Failed to upload incremental backups to S3"
        fi
    else
        log_info "Cloud upload not configured"
    fi
}

# Cleanup old incremental backups
cleanup_old_incrementals() {
    local incremental_base="${BACKUP_BASE_DIR}/incremental"
    
    # Keep 7 days of incremental backups
    find ${incremental_base} -type d -name "????????" -mtime +7 -exec rm -rf {} \; 2>/dev/null || true
    
    log_info "Cleaned up incremental backups older than 7 days"
}

# Generate incremental backup report
generate_incremental_report() {
    local backup_dir="$1"
    local report_file="${BACKUP_BASE_DIR}/logs/incremental_report_${DATE}.txt"
    
    cat > ${report_file} << EOF
TOC-SIS Incremental Backup Report
=================================
Date: $(date)
Hour: ${HOUR}
Backup Directory: ${backup_dir}

Files Created:
$(ls -la ${backup_dir}/*.sql.gz 2>/dev/null || echo "No backup files created")

Disk Usage:
$(du -sh ${backup_dir} 2>/dev/null || echo "Directory not found")

Database Activity Summary:
$(for table in "${CRITICAL_TABLES[@]}"; do
    count=$(mysql -u${DB_USER} -p${DB_PASS} -N -e "
        SELECT COUNT(*) FROM ${DB_NAME}.${table} 
        WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);" 2>/dev/null || echo "0")
    echo "${table}: ${count} changes"
done)
EOF

    log_info "Incremental backup report generated: ${report_file}"
}

# Main execution
main() {
    log_info "Starting TOC-SIS incremental backup process..."
    
    # Create backup directory for this date
    local backup_dir=$(create_directories)
    
    # Perform incremental backup
    if backup_critical_tables "${backup_dir}"; then
        # Upload to cloud if configured
        upload_incrementals "${backup_dir}"
        
        # Generate report
        generate_incremental_report "${backup_dir}"
        
        log_info "Incremental backup process completed with changes"
    else
        # Remove empty backup directory if no changes
        rmdir "${backup_dir}" 2>/dev/null || true
        log_info "Incremental backup process completed - no changes found"
    fi
    
    # Cleanup old backups
    cleanup_old_incrementals
}

# Error handling
trap 'log_error "Incremental backup script failed at line $LINENO"; exit 1' ERR

# Run main function
main "$@"