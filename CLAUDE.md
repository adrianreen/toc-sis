# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## TOC Student Information System (TOC-SIS)

This is a comprehensive Laravel-based Student Information System for The Open College (TOC), managing academic programmes, student enrolments, assessments, and academic progress tracking.

## Development Commands

### Laravel/PHP Commands
```bash
# Development server with all services
composer run dev

# Run tests
composer run test
# OR directly: php artisan test

# Code style (if Pint is configured)
./vendor/bin/pint

# Database operations
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Queue management (required for background jobs)
php artisan queue:work

# Scheduled commands (for development)
php artisan schedule:work

# Manual command execution
php artisan assessment:sync-students
php artisan assessment:release-scheduled
php artisan notifications:assessment-reminders --days=3,7  # NOTE: May not be needed if students submit via Moodle
php artisan notifications:process-scheduled
```

### Frontend Commands
```bash
# Development build with hot reload
npm run dev

# Production build
npm run build
```

## Core Architecture

### Authentication & Authorization
- **Azure AD Integration**: Primary authentication via Microsoft Azure AD
- **Role-Based Access Control**: 4 roles (`manager`, `student_services`, `teacher`, `student`)
- **Domain Mapping**: Automatic role assignment based on Azure AD groups
- **Student Linking**: Users linked to student records via email matching

### Academic Hierarchy
```
Programme → Cohort → ModuleInstance ← Module
    ↓         ↓           ↓
   Student → Enrolment → StudentModuleEnrolment → StudentAssessment
```

### Assessment Visibility System
- **Critical Feature**: Assessment results are hidden by default
- **Scheduled Release**: Automated grade publication based on `release_date`
- **Manual Override**: Staff can show/hide individual results immediately
- **Audit Trail**: All visibility changes are logged for compliance

### Notification System
- **Automated Notifications**: Assessment deadlines, grade releases, approval requests
- **Email Integration**: Configurable email delivery with preference management
- **In-App Notifications**: Real-time notification bell with unread count
- **Admin Announcements**: System-wide messaging capability
- **Scheduled Delivery**: Future notification scheduling support

### Key Models & Relationships
- **Programme**: Academic courses with different enrolment types (cohort-based, rolling)
- **ModuleInstance**: Specific delivery of a module to a cohort with teacher assignment
- **StudentAssessment**: Individual assessment attempts with sophisticated grading and visibility controls
- **Deferral/Extension/RepeatAssessment**: Academic exception management
- **Notification**: User notifications with delivery tracking and read status
- **NotificationPreference**: Per-user notification settings by type

## Service Layer Pattern
- **EnrolmentService**: Handles complex enrollment workflows with transactional integrity
- **NotificationService**: Manages all notification delivery and user preferences
- Automatic assessment creation upon module enrollment
- Business logic encapsulation for multi-step academic processes

## Route Structure
- **Student Routes** (`/my-*`): Simplified direct access for students
- **Administrative Routes**: Role-based access for staff functions
- **Nested Resources**: Assessment components nested under modules
- **Bulk Operations**: Grading and visibility management for multiple students

## Database Considerations
- **SQLite**: Uses SQLite for development (database.sqlite)
- **Activity Logging**: Comprehensive audit trail via Spatie ActivityLog
- **Soft Deletes**: Important academic records should use soft deletes
- **Foreign Key Constraints**: Strict relational integrity

## Security Notes
- **Student Data Protection**: Results visibility system prevents premature grade disclosure
- **Role Isolation**: Strict middleware enforcement on all routes
- **CSRF Protection**: All forms must include CSRF tokens
- **Azure Group Mapping**: Role assignment based on Azure AD group membership

## Testing
- **Feature Tests**: Focus on role-based access and academic workflows
- **Assessment Visibility**: Critical to test visibility rules for student data protection
- **Transactional Integrity**: Test enrollment and grading workflows

## Production Deployment

### Cron Setup (Required for Production)
The notification system requires a single cron entry. Add this to your server's crontab:

```bash
# Edit crontab
sudo crontab -e

# Add this line (replace /path/to/project with your actual path)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Tasks (Configured in AppServiceProvider)
- **Assessment Reminders**: Daily at 9:00 AM
- **Scheduled Notifications**: Every 15 minutes  
- **Grade Releases**: Every hour

### Development vs Production
- **Development**: Use `php artisan schedule:work` to run scheduler
- **Production**: Use cron entry above for automatic execution

### Email Configuration (CRITICAL for Production)
**Current Status**: Email system is fully built but configured for development only.

#### Development Configuration (Current)
```bash
MAIL_MAILER=log  # Emails logged to files, not actually sent
```

#### Production Configuration (REQUIRED)
**BEFORE DEPLOYMENT**: Must configure real email delivery or students won't receive emails.

**Option 1: Professional Email Service (Recommended)**
```bash
# Example with Mailgun (free 5,000 emails/month)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.yourdomain.com
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@theopencollege.com
MAIL_FROM_NAME="The Open College"
```

**Option 2: Other Professional Services**
- **SendGrid**: High deliverability, excellent analytics
- **Amazon SES**: Cost-effective for high volume
- **Postmark**: Specialized for transactional emails

**Option 3: SMTP Server (Advanced)**
```bash
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

#### Email Features Ready for Production
- ✅ **Professional Email Templates**: Branded, responsive design
- ✅ **Transcript Attachments**: Automatic PDF generation and attachment
- ✅ **Email Queue System**: Background processing configured
- ✅ **Audit Logging**: Complete delivery tracking via EmailLog
- ✅ **User Interface**: Student email actions in sidebar
- ✅ **Template Management**: Admin interface for email templates
- ✅ **Variable System**: 20+ dynamic variables for personalization

#### Post-Deployment Email Setup
1. **Choose email service** and obtain credentials
2. **Update .env file** with real mail driver settings
3. **Test email delivery** with sample student
4. **Configure DNS records** (SPF, DKIM) for deliverability
5. **Monitor email delivery** via EmailLog admin interface

**⚠️ WARNING**: Without proper email configuration, the following features will not work:
- Student result notifications with transcripts
- Welcome emails for new students
- Assessment deadline reminders
- Extension/deferral approval notifications
- All email templates and compose functionality

#### Email Template Management Views (Low Priority)
**Status**: Backend functionality complete, frontend views need implementation.

**Missing Views** (not critical for core functionality):
- `/admin/email-templates` - Template list view  
- `/admin/email-templates/create` - Create new template form
- `/admin/email-templates/{id}` - View template details
- `/admin/email-templates/{id}/edit` - Edit template form  
- `/admin/email-templates/{id}/preview` - Preview template with sample data
- Template duplication functionality

**Current Workaround**: 
- Email templates can be managed via database seeder or direct database access
- Core email sending functionality works with existing default templates
- Admin can access via navbar → Administration → Email Templates (routes exist but views incomplete)

**Implementation Priority**: Low - email system is fully functional without these admin interfaces

## Critical Development Rules
- **Never expose ungraded/unreleased assessments** to students via any route
- **Always use transactions** for multi-step academic operations (enrollment, grading)
- **Log all grade changes** using ActivityLog for audit compliance
- **Respect role boundaries** - students should never access administrative functions
- **Test visibility rules** thoroughly when modifying assessment-related code
- **Error handling patterns**: Service layers should log and re-throw exceptions, controllers should catch and provide user feedback, notifications should never fail silently

## Notification System Testing

### Tested Notifications ✅
- **Grade Released**: When staff makes grades visible to students
- **Manual Announcements**: Admin-created system-wide messages

### Future Testing (When Workflows Available) 📋
```bash
# Extension Approved Notification
php artisan tinker --execute="
\$user = \App\Models\User::find(8);
\$service = app(\App\Services\NotificationService::class);
\$service->notifyExtensionApproved(\$user, 'Assessment Name', new DateTime('2025-06-15'));
"

# Deferral Approved Notification  
php artisan tinker --execute="
\$user = \App\Models\User::find(8);
\$service = app(\App\Services\NotificationService::class);
\$service->notifyDeferralApproved(\$user, 'Programme Name');
"

# Approval Required (for staff)
php artisan tinker --execute="
\$staffUser = \App\Models\User::where('role', 'manager')->first();
\$service = app(\App\Services\NotificationService::class);
\$service->notifyApprovalRequired(\$staffUser, 'Extension', 'Student Name', '/extensions');
"
```

### Notes
- **Assessment deadline reminders** may not be needed since students submit via Moodle
- **Core notification system** is fully functional for grade releases and announcements
- **Extension/Deferral workflows** need to be implemented before testing those notification types

## CSV/Excel Import Implementation Plan

### Overview
Future implementation of bulk student import functionality to complement the existing "Add New Student" workflow.

### Critical Challenges Identified

#### 1. Email Uniqueness Constraint
- **Issue**: `email` field has unique constraint across students table
- **Impact**: Import fails if duplicates exist within file or against existing records
- **Solution**: Pre-process file for duplicate detection, offer merge/skip options

#### 2. Student Number Generation
- **Issue**: `Student::generateStudentNumber()` creates sequential YYYY### format
- **Impact**: Bulk imports could create gaps/conflicts with concurrent imports
- **Solution**: Use database transactions and proper locking mechanisms

#### 3. User Account Linking
- **Issue**: Students can link to User accounts via `student_id` field
- **Impact**: Import needs to handle automatic user account creation
- **Solution**: Add optional "create_user_account" column or separate workflow

### Implementation Requirements

#### Dependencies Needed
```bash
# Required package for CSV/Excel processing
composer require maatwebsite/excel
```

#### CSV Template Structure
```csv
first_name,last_name,email,phone,address,city,county,eircode,date_of_birth,status,notes
Emma,Murphy,emma.murphy@student.ie,0851234567,123 Main Street,Dublin,Dublin,D01 X123,1990-05-15,enquiry,New student enquiry
```

#### Validation Rules to Apply
```php
'first_name' => 'required|string|max:255',
'last_name' => 'required|string|max:255', 
'email' => 'required|email|unique:students,email',
'date_of_birth' => 'nullable|date|before:today',
'status' => 'required|in:enquiry,enrolled,active,deferred,completed,cancelled'
```

### Technical Considerations

#### Memory Management
- **Challenge**: Large CSV files (1000+ students) exceed PHP memory limits
- **Solution**: Use Laravel Excel's `chunk()` method for batch processing

#### Activity Logging Overhead
- **Challenge**: Spatie ActivityLog creates audit records for every student
- **Solution**: Optimize logging or batch log entries for large imports

#### File Upload Security
- **Requirements**: 
  - File type validation (CSV, XLSX only)
  - Size limits (recommend 10MB max)
  - Temporary file storage and cleanup

### Recommended Implementation Phases

#### Phase 1: Basic CSV Import
1. Install Laravel Excel package
2. Create upload form component (button placeholder already exists)
3. Implement basic validation and import
4. Add error handling and reporting

#### Phase 2: Enhanced Features  
1. Excel (XLSX) support
2. Preview functionality before import
3. Batch processing for large files
4. Progress tracking with background queues

#### Phase 3: Advanced Features
1. User account creation integration
2. Enrollment workflow automation via `EnrolmentService`
3. Duplicate detection and merging
4. Import history and rollback capability

### User Experience Flow
**Recommended Workflow**: Upload → Preview → Validate → Import → Results

### Key Risks & Mitigations
- **Data corruption**: Use database transactions
- **Performance issues**: Implement chunked processing  
- **Memory exhaustion**: Set PHP limits, use streaming
- **Duplicate students**: Pre-validation and merge options
- **Invalid data**: Comprehensive validation with clear error messages

### Integration Points
- **EnrolmentService**: Use for complex enrollment workflows
- **Activity Logging**: Maintain audit compliance
- **Role-Based Access**: Restrict import to appropriate roles (manager, student_services)
- **Notification System**: Consider notifications for import completion

### Testing Strategy
- Start with small imports (10-50 students)
- Test various CSV formats and edge cases
- Validate memory usage with large datasets
- Ensure transactional integrity
- Test error recovery scenarios
