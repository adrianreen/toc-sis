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

## Critical Development Rules
- **Never expose ungraded/unreleased assessments** to students via any route
- **Always use transactions** for multi-step academic operations (enrollment, grading)
- **Log all grade changes** using ActivityLog for audit compliance
- **Respect role boundaries** - students should never access administrative functions
- **Test visibility rules** thoroughly when modifying assessment-related code

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