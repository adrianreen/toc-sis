# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## TOC Student Information System (TOC-SIS)

This is a comprehensive Laravel-based Student Information System for The Open College (TOC), managing academic programmes, student enrolments, assessments, and academic progress tracking.

## 🔧 DEVELOPMENT PHASE - EARLY STAGE

**IMPORTANT**: This application is in **early development phase**:
- **No live deployment** - app is not in production
- **No real data** - using test/seed data only
- **Single developer** - rapid iteration and schema changes
- **Constant migration rewrites** - database structure frequently changing
- **Seeder adjustments** - data structures being refined

**TESTING STRATEGY**: 
- **Current Focus**: Core functionality and architecture stability
- **Tests Deferred**: Comprehensive test suites will be implemented when structure stabilizes
- **CI Pipelines**: Not prioritized until closer to production readiness
- **Rationale**: Early stage rapid development where tests would slow iteration speed

**Once stable and production-ready, full testing and CI/CD will be implemented.**

## 🚨 CRITICAL UI/UX STANDARDS - ALWAYS FOLLOW

**NEVER create buttons that are white text on white background or similar low-contrast issues!**
**ALWAYS add hover cursor changes (cursor-pointer) to clickable elements!**

### UI Standards Checklist:
- ✅ **Button Contrast**: Always use high-contrast color combinations (e.g., `bg-toc-600 text-white` or `bg-blue-600 text-white`)
- ✅ **Hover States**: ALL clickable elements must have `hover:` states and `cursor-pointer`
- ✅ **Visual Feedback**: Buttons must have `transition-colors` for smooth hover effects
- ✅ **Accessibility**: Ensure text meets WCAG contrast requirements
- ✅ **Consistency**: Use established color patterns from the design system

**Example of correct button styling:**
```html
<button class="bg-toc-600 hover:bg-toc-700 text-white px-4 py-2 rounded-lg transition-colors cursor-pointer">
    Click Me
</button>
```

**NEVER use unclear color combinations like:**
- `text-white bg-white` (invisible text)
- `text-gray-400 bg-gray-300` (poor contrast)
- Missing hover states on clickable elements
- Missing `cursor-pointer` on interactive elements

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

## Core Architecture - 4 Level Programme-Module System

### 1. Core Philosophy
The system is built on a clear separation between a static Blueprint (the "what") and a live Instance (the "when and who"). This applies to both Programmes and Modules. The system supports two primary delivery styles:
- **Synchronous (Sync)**: Group-based cohorts with fixed schedules
- **Asynchronous (Async)**: Rolling, self-paced enrolments

### 2. Entity Definitions

#### I. Programme (Static Blueprint)
- **Purpose**: Static library of information about a full award - the master recipe
- **Key Attributes**:
  - Title (e.g., "BA in Business Management")
  - Awarding Body & NFQ Level
  - Total Credits for the full award
  - General description and learning outcomes

#### II. Programme Instance (Live Container)
- **Purpose**: The live, scheduled container for a Programme that students enrol into
- **Key Attributes**:
  - Link to parent Programme blueprint
  - Label (e.g., "September 2024 Intake" or "2024 Rolling Enrolment")
  - Intake Dates (Start/End): Mandatory for Sync, wider window for Async
  - Default Delivery Style (enum: Sync, Async)
  - Curriculum (via pivot table to Module Instance IDs)

#### III. Module (Static Blueprint)
- **Purpose**: Static library of information for a single unit of study
- **Key Attributes**:
  - Title (e.g., "Introduction to Marketing")
  - Module Code & Credit Value
  - Assessment Strategy: Array of Assessment Component objects with:
    - ComponentName (e.g., "Final Exam")
    - Weighting (%)
    - IsMustPass (boolean)
    - ComponentPassMark (nullable, can override module-level pass mark)
  - Allows Standalone Enrolment (boolean): Enables Minor Awards
  - AsyncInstanceCadence (enum: Monthly, Quarterly, Bi-Annually, Annually)

#### IV. Module Instance (Live Class)
- **Purpose**: The live, scheduled run of a Module - the concrete "class" a student interacts with
- **Key Attributes**:
  - Link to parent Module blueprint
  - Assigned Tutor (link to Staff record)
  - Start Date and target End Date
  - Programme Instance ID (nullable): Links to parent programme or null for standalone
  - Delivery Style (enum: Sync, Async): Can override programme default

#### V. Curriculum Linker Mechanism
- **Purpose**: Connects Module Instances to Programme Instances
- **Implementation**: `programme_instance_curriculum` pivot table
- **Structure**: programme_instance_id + module_instance_id with foreign key constraints

### 3. The Enrolment Model: Two-Path System

#### Enrolment Entity Structure
- **StudentID**: Link to student record
- **EnrolmentType** (enum: Programme, Module): Defines the enrolment path
- **ProgrammeInstanceID**: Populated if type is Programme
- **ModuleInstanceID**: Populated if type is Module (standalone)

#### Enrolment Workflows

**Programme Enrolment:**
1. Admin selects student and clicks "Enrol"
2. Admin chooses "Enrol in a Programme"
3. System displays available Programme Instances of type Sync
4. Admin selects instance
5. Enrolment record created linking student to Programme Instance
6. System automatically knows Module Instances via curriculum linkage

**Standalone Module Enrolment:**
1. Admin selects student and clicks "Enrol"
2. Admin chooses "Enrol in a Standalone Module"
3. System displays Module Instances where parent Module allows standalone enrolment
4. Admin selects specific instance
5. Enrolment record created linking student directly to Module Instance (programme link null)

### 4. Student Grade Record System
- **Purpose**: Replaces old StudentAssessment model
- **Key Attributes**:
  - StudentID
  - ModuleInstanceID (the class they took)
  - AssessmentComponentID (specific piece of work from Module blueprint)
  - Grade/Mark
  - SubmissionDate, GradedByStaffID, etc.

### Authentication & Authorization
- **Azure AD Integration**: Primary authentication via Microsoft Azure AD
- **Role-Based Access Control**: 4 roles (`manager`, `student_services`, `teacher`, `student`)
- **Domain Mapping**: Automatic role assignment based on Azure AD groups
- **Student Linking**: Users linked to student records via email matching

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

### Key Models & Relationships (New Architecture)
- **Programme**: Static blueprint for full awards
- **ProgrammeInstance**: Live delivery container with intake dates
- **Module**: Static blueprint for study units with assessment components
- **ModuleInstance**: Live class delivery with tutor assignment
- **Enrolment**: Two-path system (Programme vs Module) student enrolments
- **StudentGradeRecord**: Individual assessment component grades
- **Deferral/Extension/RepeatAssessment**: Academic exception management (retained)
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
- **Pivot Tables**: programme_instance_curriculum for curriculum linkage

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

## Todo List Integration
This document tracks the critical nuclear-level restructure to the new 4-level Programme-Module architecture:

### Completed ✅
- Clean database - drop all existing tables and start fresh
- Delete all old model files (Programme, Cohort, ModuleInstance, etc.)
- Delete old migration files completely
- Update CLAUDE.md to remove old architecture and document new 4-level system

### In Progress 🔄
- Create new Programme model and migration
- Create new ProgrammeInstance model and migration  
- Create new Module model with Assessment Components and AsyncInstanceCadence
- Create new ModuleInstance model and migration
- Create programme_instance_curriculum pivot table
- Create new Enrolment model with two-path system
- Create new StudentGradeRecord model and migration
- Delete old controllers, services, and tests related to legacy architecture

### Architecture Implementation Status
**Status**: Nuclear-level critical restructure in progress
**Approach**: Greenfield - complete removal of legacy architecture
**Priority**: Highest - entire project depends on this success

## Future Development Roadmap

### 📋 Planned Enhancements (Future Work)

#### Comprehensive Student Profile System
**Priority**: High (Future Release)
**Description**: Implement a comprehensive, tabbed student profile interface to replace the current basic profile system.

**Proposed Features**:
- **Personal Information Tab**: Contact details, emergency contacts, demographics
- **Academic Progress Tab**: Complete academic history, GPA calculations, achievement timeline
- **Documents Tab**: Integrated document management with categorization and status tracking
- **Communications Tab**: Email history, notification preferences, contact log
- **Support Services Tab**: Extension requests, deferral history, support case tracking
- **Settings Tab**: Privacy preferences, notification settings, accessibility options

**Technical Requirements**:
- Modern tabbed interface using Alpine.js or similar
- Responsive design for mobile/tablet/desktop
- Real-time data updates without page refreshes
- Role-based content visibility (student vs staff views)
- Integration with existing Student, StudentDocument, and Notification systems
- Export functionality for academic records

**Implementation Notes**:
- Replace current `/students/{student}/profile` route with comprehensive tabbed interface
- Maintain backward compatibility during transition
- Consider lazy loading for performance with large datasets
- Implement proper caching for frequently accessed profile data

**Status**: Logged for future development - not currently in scope

#### Policy Management System
**Priority**: Medium (Future Release)
**Description**: Implement a comprehensive policy management system allowing staff to manage and students to access relevant college, programme, and module-specific policies.

**Proposed Features**:

**For Students**:
- **Policy Dashboard**: Access all applicable policies from student dashboard/profile
- **Categorized View**: College-wide, Programme-specific, and Module-specific policies
- **Search & Filter**: Find policies by category, keywords, or relevance
- **Policy Tracking**: Mark policies as read, bookmark important policies
- **Version History**: Access to previous versions when policies are updated
- **Mobile Access**: Responsive design for policy access on all devices

**For Staff/Managers**:
- **Policy Creation**: Rich text editor for creating new policies with attachments
- **Policy Management**: Full CRUD operations (Create, Read, Update, Delete)
- **Scope Assignment**: Define policy scope (College-wide, Programme-specific, Module-specific)
- **Targeted Distribution**: Assign policies to specific programmes/modules using existing architecture
- **Version Control**: Maintain policy revision history with change tracking
- **Publication Control**: Draft/Published status with scheduled publication dates
- **Analytics**: Track policy views, downloads, and engagement metrics

**Technical Architecture**:
```php
// Proposed Database Structure
Schema::create('policies', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->longText('content'); // Rich text content
    $table->enum('scope', ['college', 'programme', 'module']);
    $table->enum('status', ['draft', 'published', 'archived']);
    $table->json('attachments')->nullable(); // File attachments
    $table->foreignId('created_by')->constrained('users');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('policy_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('policy_id')->constrained()->onDelete('cascade');
    $table->foreignId('programme_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('module_id')->nullable()->constrained()->onDelete('cascade');
    $table->timestamps();
    // Ensures policies can be assigned to programmes OR modules
    $table->index(['policy_id', 'programme_id']);
    $table->index(['policy_id', 'module_id']);
});

Schema::create('policy_views', function (Blueprint $table) {
    $table->id();
    $table->foreignId('policy_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamp('viewed_at');
    $table->unique(['policy_id', 'user_id']);
});
```

**Integration Points**:
- **Student Dashboard**: New "Policies" quick action card
- **Programme Integration**: Link policies to existing Programme/ProgrammeInstance models
- **Module Integration**: Link policies to existing Module/ModuleInstance models
- **User Roles**: Leverage existing role system (manager, student_services, teacher, student)
- **Notification System**: Integrate with existing NotificationService for policy updates
- **Activity Logging**: Use existing Spatie ActivityLog for policy management audit trail

**Proposed Routes**:
```php
// Student Routes
Route::get('/my-policies', [PolicyController::class, 'studentIndex'])->name('policies.student-index');
Route::get('/policies/{policy}', [PolicyController::class, 'show'])->name('policies.show');

// Staff Routes (Manager/Student Services)
Route::middleware(['role:manager,student_services'])->group(function () {
    Route::resource('admin/policies', PolicyController::class);
    Route::post('admin/policies/{policy}/assign-programme', [PolicyController::class, 'assignProgramme']);
    Route::post('admin/policies/{policy}/assign-module', [PolicyController::class, 'assignModule']);
    Route::get('admin/policies/{policy}/analytics', [PolicyController::class, 'analytics']);
});
```

**User Experience Flow**:
1. **Manager Creates Policy**: Uses rich text editor, sets scope, assigns to programmes/modules
2. **System Determines Visibility**: Based on student enrolments and policy assignments
3. **Student Access**: Views applicable policies from dashboard with clear categorization
4. **Tracking**: System logs views and provides analytics to administrators
5. **Updates**: Students receive notifications when relevant policies are updated

**File Management**:
- **Secure Storage**: Private disk storage for policy attachments
- **Access Control**: Role-based file access through controllers
- **Version Control**: Maintain file versions when policies are updated
- **File Types**: Support PDF, DOC, images for comprehensive policy documentation

**Implementation Phases**:
1. **Phase 1**: Basic CRUD for college-wide policies
2. **Phase 2**: Programme/Module assignment functionality
3. **Phase 3**: Student dashboard integration and notifications
4. **Phase 4**: Analytics, version control, and advanced features

**Status**: Logged for future development - not currently in scope