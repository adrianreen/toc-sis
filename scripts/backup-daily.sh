#!/bin/bash

# TOC-SIS Daily Backup Script
# Comprehensive backup of database, application, and files

set -e

# Configuration
DB_NAME="${DB_NAME:-toc_sis}"
DB_USER="${BACKUP_DB_USER:-backup_user}"
DB_PASS="${BACKUP_DB_PASS:-backup_password}"
BACKUP_BASE_DIR="${BACKUP_DIR:-/backups}"
APP_DIR="${APP_DIR:-/var/www/toc-sis}"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS="${RETENTION_DAYS:-30}"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Logging
LOG_FILE="/var/log/toc-sis-backup.log"
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

# Create backup directories
create_directories() {
    log_info "Creating backup directories..."
    mkdir -p ${BACKUP_BASE_DIR}/{database,application,files,logs}
}

# Database backup
backup_database() {
    log_info "Starting database backup..."
    
    local db_backup_dir="${BACKUP_BASE_DIR}/database"
    local backup_file="${db_backup_dir}/toc_sis_${DATE}.sql.gz"
    
    # Check if database exists
    if ! mysql -u${DB_USER} -p${DB_PASS} -e "USE ${DB_NAME};" 2>/dev/null; then
        log_error "Database ${DB_NAME} not accessible"
        return 1
    fi
    
    # Create database dump with compression
    if mysqldump --user=${DB_USER} --password=${DB_PASS} \
                 --single-transaction --routines --triggers \
                 --complete-insert --extended-insert \
                 ${DB_NAME} | gzip > ${backup_file}; then
        log_info "Database backup completed: ${backup_file}"
        
        # Verify backup integrity
        if gunzip -t ${backup_file}; then
            log_info "Database backup integrity verified"
        else
            log_error "Database backup integrity check failed"
            return 1
        fi
    else
        log_error "Database backup failed"
        return 1
    fi
    
    # Cleanup old backups
    find ${db_backup_dir} -name "toc_sis_*.sql.gz" -mtime +${RETENTION_DAYS} -delete
    log_info "Cleaned up database backups older than ${RETENTION_DAYS} days"
}

# Application code backup
backup_application() {
    log_info "Starting application backup..."
    
    local app_backup_dir="${BACKUP_BASE_DIR}/application"
    local backup_file="${app_backup_dir}/toc-sis-app_${DATE}.tar.gz"
    
    # Create application tarball excluding unnecessary files
    if tar --exclude='storage/logs/*' \
           --exclude='storage/framework/cache/*' \
           --exclude='storage/framework/sessions/*' \
           --exclude='storage/framework/views/*' \
           --exclude='storage/app/public/temp/*' \
           --exclude='node_modules' \
           --exclude='.git' \
           --exclude='*.log' \
           -czf ${backup_file} \
           -C $(dirname ${APP_DIR}) $(basename ${APP_DIR}); then
        log_info "Application backup completed: ${backup_file}"
    else
        log_error "Application backup failed"
        return 1
    fi
    
    # Cleanup old backups
    find ${app_backup_dir} -name "toc-sis-app_*.tar.gz" -mtime +${RETENTION_DAYS} -delete
}

# Files and uploads backup
backup_files() {
    log_info "Starting files backup..."
    
    local files_backup_dir="${BACKUP_BASE_DIR}/files"
    local storage_dir="${APP_DIR}/storage/app"
    local backup_file="${files_backup_dir}/storage_${DATE}.tar.gz"
    
    if [ -d "${storage_dir}" ]; then
        # Sync files to backup location first
        rsync -av --delete ${storage_dir}/ ${files_backup_dir}/current/
        
        # Create compressed archive
        if tar -czf ${backup_file} -C ${storage_dir} .; then
            log_info "Files backup completed: ${backup_file}"
        else
            log_error "Files backup failed"
            return 1
        fi
        
        # Cleanup old archives
        find ${files_backup_dir} -name "storage_*.tar.gz" -mtime +${RETENTION_DAYS} -delete
    else
        log_warn "Storage directory not found: ${storage_dir}"
    fi
}

# System configuration backup
backup_configuration() {
    log_info "Starting configuration backup..."
    
    local config_backup_dir="${BACKUP_BASE_DIR}/configuration"
    local backup_file="${config_backup_dir}/config_${DATE}.tar.gz"
    
    mkdir -p ${config_backup_dir}
    
    # Backup configuration files (excluding sensitive data)
    if tar -czf ${backup_file} \
           --exclude='*.env' \
           ${APP_DIR}/.env.example \
           ${APP_DIR}/config/ \
           ${APP_DIR}/composer.json \
           ${APP_DIR}/composer.lock \
           ${APP_DIR}/package.json \
           ${APP_DIR}/artisan \
           /etc/nginx/sites-available/toc-sis* 2>/dev/null || true; then
        log_info "Configuration backup completed: ${backup_file}"
    else
        log_warn "Some configuration files could not be backed up"
    fi
}

# Upload to cloud storage (if configured)
upload_to_cloud() {
    if command -v aws >/dev/null 2>&1 && [ -n "${AWS_S3_BUCKET}" ]; then
        log_info "Uploading backups to AWS S3..."
        
        # Upload database backup
        if aws s3 cp ${BACKUP_BASE_DIR}/database/toc_sis_${DATE}.sql.gz \
                     s3://${AWS_S3_BUCKET}/database/daily/ --storage-class STANDARD_IA; then
            log_info "Database backup uploaded to S3"
        else
            log_error "Failed to upload database backup to S3"
        fi
        
        # Upload application backup
        if aws s3 cp ${BACKUP_BASE_DIR}/application/toc-sis-app_${DATE}.tar.gz \
                     s3://${AWS_S3_BUCKET}/application/; then
            log_info "Application backup uploaded to S3"
        else
            log_error "Failed to upload application backup to S3"
        fi
        
        # Sync files backup
        if aws s3 sync ${BACKUP_BASE_DIR}/files/current/ \
                       s3://${AWS_S3_BUCKET}/files/ --delete; then
            log_info "Files backup synced to S3"
        else
            log_error "Failed to sync files backup to S3"
        fi
    else
        log_warn "AWS CLI not configured or S3 bucket not specified - skipping cloud upload"
    fi
}

# Generate backup report
generate_report() {
    log_info "Generating backup report..."
    
    local report_file="${BACKUP_BASE_DIR}/logs/backup_report_${DATE}.txt"
    
    cat > ${report_file} << EOF
TOC-SIS Backup Report
====================
Date: $(date)
Backup ID: ${DATE}

Database Backup:
$(ls -lh ${BACKUP_BASE_DIR}/database/toc_sis_${DATE}.sql.gz 2>/dev/null || echo "FAILED")

Application Backup:
$(ls -lh ${BACKUP_BASE_DIR}/application/toc-sis-app_${DATE}.tar.gz 2>/dev/null || echo "FAILED")

Files Backup:
$(ls -lh ${BACKUP_BASE_DIR}/files/storage_${DATE}.tar.gz 2>/dev/null || echo "FAILED")

Disk Usage:
$(df -h ${BACKUP_BASE_DIR})

Storage Summary:
$(du -sh ${BACKUP_BASE_DIR}/*)

Backup Verification:
Database Count: $(mysql -u${DB_USER} -p${DB_PASS} -N -e "SELECT COUNT(*) FROM ${DB_NAME}.students;" 2>/dev/null || echo "N/A")
Grade Records: $(mysql -u${DB_USER} -p${DB_PASS} -N -e "SELECT COUNT(*) FROM ${DB_NAME}.student_grade_records;" 2>/dev/null || echo "N/A")
EOF

    log_info "Backup report generated: ${report_file}"
}

# Send email notification (if configured)
send_notification() {
    if command -v mail >/dev/null 2>&1 && [ -n "${BACKUP_NOTIFICATION_EMAIL}" ]; then
        local subject="TOC-SIS Backup Completed - ${DATE}"
        local report_file="${BACKUP_BASE_DIR}/logs/backup_report_${DATE}.txt"
        
        if [ -f "${report_file}" ]; then
            mail -s "${subject}" ${BACKUP_NOTIFICATION_EMAIL} < ${report_file}
            log_info "Backup notification sent to ${BACKUP_NOTIFICATION_EMAIL}"
        fi
    fi
}

# Main execution
main() {
    log_info "Starting TOC-SIS daily backup process..."
    
    create_directories
    
    # Run backup operations
    backup_database
    backup_application
    backup_files
    backup_configuration
    
    # Upload to cloud if configured
    upload_to_cloud
    
    # Generate report and notification
    generate_report
    send_notification
    
    log_info "TOC-SIS daily backup process completed successfully"
}

# Error handling
trap 'log_error "Backup script failed at line $LINENO"; exit 1' ERR

# Run main function
main "$@"