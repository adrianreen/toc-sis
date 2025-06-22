# TOC-SIS Comprehensive Backup Strategy

## Overview

Academic data is critical and requires robust backup strategies to ensure data protection, compliance, and business continuity. This document outlines a comprehensive backup strategy for the TOC-SIS system.

## Critical Data Categories

### 1. **CRITICAL** - Academic Records (Tier 1)
- **Student Grade Records** - Assessment results and academic progress
- **Enrolments** - Student programme and module registrations
- **Transcripts** - Official academic transcripts and results
- **Extensions/Deferrals** - Academic exception records
- **Assessment Components** - Module assessment configurations

**Recovery Requirement**: Maximum 1 hour data loss acceptable

### 2. **ESSENTIAL** - Student Information (Tier 2)
- **Student Records** - Personal and contact information
- **User Accounts** - Authentication and role data
- **Programme/Module Definitions** - Academic structure data
- **Email Communications** - Official correspondence logs

**Recovery Requirement**: Maximum 4 hours data loss acceptable

### 3. **IMPORTANT** - System Configuration (Tier 3)
- **Email Templates** - Communication templates
- **Notification Preferences** - User notification settings
- **System Logs** - Application and audit logs
- **File Uploads** - Supporting documents and attachments

**Recovery Requirement**: Maximum 24 hours data loss acceptable

## Backup Strategy Components

### 1. Database Backups

#### Real-Time Replication (Tier 1)
```bash
# MySQL Master-Slave replication for real-time backup
# Primary database server with read replica

# Enable binary logging on primary
log-bin=mysql-bin
server-id=1
binlog-format=row

# Configure slave server
server-id=2
read-only=1
relay-log=relay-bin
```

#### Automated Daily Backups
```bash
#!/bin/bash
# /etc/cron.daily/toc-sis-backup

DB_NAME="toc_sis"
DB_USER="backup_user"
DB_PASS="secure_password"
BACKUP_DIR="/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)

# Create compressed database dump
mysqldump --user=${DB_USER} --password=${DB_PASS} \
          --single-transaction --routines --triggers \
          ${DB_NAME} | gzip > ${BACKUP_DIR}/toc_sis_${DATE}.sql.gz

# Keep 30 days of daily backups
find ${BACKUP_DIR} -name "toc_sis_*.sql.gz" -mtime +30 -delete

# Upload to cloud storage
aws s3 cp ${BACKUP_DIR}/toc_sis_${DATE}.sql.gz \
          s3://toc-sis-backups/database/daily/
```

#### Hourly Incremental Backups (Critical Data)
```bash
#!/bin/bash
# /etc/cron.hourly/toc-sis-incremental

# Backup critical tables only (student_grade_records, enrolments, etc.)
CRITICAL_TABLES="student_grade_records enrolments students users programmes modules"
BACKUP_DIR="/backups/incremental"
DATE=$(date +%Y%m%d_%H%M%S)

for table in $CRITICAL_TABLES; do
    mysqldump --user=${DB_USER} --password=${DB_PASS} \
              --single-transaction --where="updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)" \
              ${DB_NAME} ${table} | gzip > ${BACKUP_DIR}/${table}_${DATE}.sql.gz
done

# Upload incrementals to cloud
aws s3 sync ${BACKUP_DIR}/ s3://toc-sis-backups/incremental/
```

### 2. File System Backups

#### Application Code Backup
```bash
#!/bin/bash
# Daily application backup excluding logs and cache

BACKUP_DIR="/backups/application"
APP_DIR="/var/www/toc-sis"
DATE=$(date +%Y%m%d)

# Create tarball excluding unnecessary files
tar --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='node_modules' \
    --exclude='.git' \
    -czf ${BACKUP_DIR}/toc-sis-app_${DATE}.tar.gz \
    ${APP_DIR}

# Upload to cloud storage
aws s3 cp ${BACKUP_DIR}/toc-sis-app_${DATE}.tar.gz \
          s3://toc-sis-backups/application/
```

#### Critical File Storage Backup
```bash
#!/bin/bash
# Backup uploaded files and documents

STORAGE_DIR="/var/www/toc-sis/storage/app"
BACKUP_DIR="/backups/files"
DATE=$(date +%Y%m%d)

# Sync files to backup location
rsync -av --delete ${STORAGE_DIR}/ ${BACKUP_DIR}/

# Create compressed archive
tar -czf ${BACKUP_DIR}/storage_${DATE}.tar.gz -C ${STORAGE_DIR} .

# Upload to cloud
aws s3 sync ${BACKUP_DIR}/ s3://toc-sis-backups/files/
```

### 3. Cloud Storage Strategy

#### Multi-Region Storage
- **Primary**: Local server storage for immediate access
- **Secondary**: AWS S3 in primary region (eu-west-1)
- **Tertiary**: AWS S3 Cross-Region Replication to eu-central-1
- **Archive**: AWS Glacier for long-term retention

#### S3 Bucket Configuration
```json
{
  "Rules": [
    {
      "ID": "TOC-SIS-Backup-Lifecycle",
      "Status": "Enabled",
      "Transitions": [
        {
          "Days": 30,
          "StorageClass": "STANDARD_IA"
        },
        {
          "Days": 90,
          "StorageClass": "GLACIER"
        },
        {
          "Days": 365,
          "StorageClass": "DEEP_ARCHIVE"
        }
      ]
    }
  ]
}
```

### 4. Monitoring and Alerting

#### Backup Monitoring Script
```bash
#!/bin/bash
# Check backup completion and integrity

BACKUP_DIR="/backups"
LOG_FILE="/var/log/toc-sis-backup.log"
ALERT_EMAIL="admin@theopencollege.com"

# Check if daily backup completed
LATEST_BACKUP=$(ls -t ${BACKUP_DIR}/database/toc_sis_*.sql.gz | head -1)
BACKUP_AGE=$(find ${BACKUP_DIR}/database -name "toc_sis_*.sql.gz" -mtime -1 | wc -l)

if [ ${BACKUP_AGE} -eq 0 ]; then
    echo "$(date): ERROR - No recent database backup found" >> ${LOG_FILE}
    echo "URGENT: TOC-SIS database backup failed" | \
         mail -s "Backup Alert" ${ALERT_EMAIL}
fi

# Test backup integrity
if ! gunzip -t ${LATEST_BACKUP}; then
    echo "$(date): ERROR - Backup integrity check failed" >> ${LOG_FILE}
    echo "URGENT: TOC-SIS backup file corrupted" | \
         mail -s "Backup Integrity Alert" ${ALERT_EMAIL}
fi
```

### 5. Disaster Recovery Procedures

#### Automated Recovery Scripts
```bash
#!/bin/bash
# Emergency database restore script

BACKUP_SOURCE="$1"  # S3 path or local file
RESTORE_DB="toc_sis_restore"

if [ -z "$BACKUP_SOURCE" ]; then
    echo "Usage: $0 <backup_source>"
    exit 1
fi

# Download backup if from S3
if [[ $BACKUP_SOURCE == s3://* ]]; then
    aws s3 cp $BACKUP_SOURCE /tmp/restore_backup.sql.gz
    BACKUP_FILE="/tmp/restore_backup.sql.gz"
else
    BACKUP_FILE="$BACKUP_SOURCE"
fi

# Create restore database
mysql -e "CREATE DATABASE IF NOT EXISTS ${RESTORE_DB};"

# Restore from backup
gunzip -c ${BACKUP_FILE} | mysql ${RESTORE_DB}

echo "Database restored to ${RESTORE_DB}"
echo "Verify data integrity before switching to restored database"
```

## Implementation Commands

### Setup Backup System
```bash
# Create backup directories
sudo mkdir -p /backups/{database,application,files,incremental}
sudo chown www-data:www-data /backups -R

# Install backup scripts
sudo cp scripts/backup-* /usr/local/bin/
sudo chmod +x /usr/local/bin/backup-*

# Setup cron jobs
sudo crontab -e
# Add lines:
# 0 2 * * * /usr/local/bin/backup-daily.sh
# 0 * * * * /usr/local/bin/backup-incremental.sh
# */15 * * * * /usr/local/bin/backup-monitor.sh
```

### Configure AWS CLI for Cloud Backups
```bash
# Install AWS CLI
sudo apt-get install awscli

# Configure credentials
aws configure
# AWS Access Key ID: [your-access-key]
# AWS Secret Access Key: [your-secret-key]
# Default region name: eu-west-1
# Default output format: json

# Test S3 access
aws s3 ls s3://toc-sis-backups/
```

### Database User for Backups
```sql
-- Create dedicated backup user with minimal privileges
CREATE USER 'backup_user'@'localhost' IDENTIFIED BY 'secure_backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT ON toc_sis.* TO 'backup_user'@'localhost';
GRANT RELOAD, PROCESS ON *.* TO 'backup_user'@'localhost';
FLUSH PRIVILEGES;
```

## Backup Testing and Validation

### Monthly Restore Tests
```bash
#!/bin/bash
# Test backup restoration monthly

TEST_DB="toc_sis_test_restore"
LATEST_BACKUP=$(ls -t /backups/database/toc_sis_*.sql.gz | head -1)

# Create test database
mysql -e "CREATE DATABASE ${TEST_DB};"

# Restore from backup
gunzip -c ${LATEST_BACKUP} | mysql ${TEST_DB}

# Validate critical data
STUDENT_COUNT=$(mysql -N -e "SELECT COUNT(*) FROM ${TEST_DB}.students;")
GRADE_COUNT=$(mysql -N -e "SELECT COUNT(*) FROM ${TEST_DB}.student_grade_records;")

echo "Restore test completed:"
echo "Students: ${STUDENT_COUNT}"
echo "Grade records: ${GRADE_COUNT}"

# Cleanup test database
mysql -e "DROP DATABASE ${TEST_DB};"
```

### Backup Verification Checklist
- [ ] Daily database backups completing successfully
- [ ] Hourly incremental backups for critical tables
- [ ] Cloud storage replication working
- [ ] File system backups including uploaded documents
- [ ] Backup integrity checks passing
- [ ] Monitoring alerts configured and tested
- [ ] Recovery procedures documented and tested
- [ ] Staff trained on recovery procedures

## Compliance and Retention

### Data Retention Policy
- **Student Grade Records**: 7 years minimum (regulatory requirement)
- **Student Personal Data**: 6 years after graduation/withdrawal
- **System Logs**: 2 years for audit purposes
- **Email Communications**: 3 years for official correspondence

### Encryption and Security
- All backups encrypted at rest using AES-256
- Database backups include sensitive data - secure storage required
- Access to backups restricted to authorized personnel only
- Regular security audits of backup systems

## Cost Optimization

### Storage Cost Management
```bash
# Estimate monthly backup costs
# Daily database backups: ~100MB x 30 days = 3GB
# Incremental backups: ~10MB x 24 x 30 = 7.2GB
# Application backups: ~500MB x 30 = 15GB
# Total monthly: ~25GB storage

# AWS S3 pricing (eu-west-1):
# Standard: $0.023/GB = ~$0.58/month
# Standard-IA: $0.0125/GB after 30 days
# Glacier: $0.004/GB after 90 days
```

### Backup Optimization
- Compress all backups (typically 70-80% reduction)
- Use incremental backups for frequent data
- Implement lifecycle policies for automatic archiving
- Monitor and optimize backup sizes regularly

## Support and Maintenance

### Regular Maintenance Tasks
- Weekly backup size monitoring
- Monthly restore testing
- Quarterly disaster recovery drills
- Annual backup strategy review

### Emergency Contacts
- **Database Administrator**: [contact-info]
- **System Administrator**: [contact-info]
- **Cloud Provider Support**: AWS Support
- **Data Protection Officer**: [contact-info]

---

**Implementation Priority**: CRITICAL - Academic data must be protected at all times.

This backup strategy ensures comprehensive protection of TOC-SIS academic data with multiple layers of redundancy and tested recovery procedures.