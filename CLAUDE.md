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

### Original Design Document (For Reference) 
Student Information System (SIS) Design Document v2.0
Document Version: 2.0
Created: May 2025
Institution: The Open College
Project Lead: Education Technologist
1. Executive Summary
1.1 Project Purpose
This document outlines the design for a comprehensive Student Information System (SIS) to replace the
current fragmented Excel-based tracking system. The primary goal is to unify student lifecycle
management across 10+ separate tracking systems into a single, efficient platform.
1.2 Current Problem Statement
The institution currently manages student data through 9+ separate Excel files including:
Student enquiries & enrolments (by cohort)
Deferral requests & results tracking
Repeat assessments & extensions
Exemptions & module repeat grades
Transfer management between cohorts
Pain Points:
No unified student view across systems
Manual cross-referencing between multiple files
Data inconsistency and human error risk
Difficult student journey tracking across deferrals/transfers
Separate sheets for every cohort in every tracking system
1.3 Project Scope
The scope of this SIS project encompasses comprehensive student lifecycle management with the
following key components:
Authentication and Authorization

OpenID Connect (Azure AD) Integration: Secure login via institutional Azure AD with role
assignment based on Azure AD security group membership
Role-Based Access Control (RBAC): Differentiated permissions for Curriculum Manager, Student
Services, Teacher/Instructor, and Student roles
Configurable Role Mapping: Azure AD security groups mapped via configuration table with
precedence logic
Student Management
Curriculum Manager/Student Services: Complete CRUD operations for student demographic and
contact information
Unified Student Profile: Single source of truth replacing fragmented Excel tracking
Student Self-Service: Read-only access to personal information, enrolled modules, results, and
institutional policies
Programme and Module Management
Programme Structure Definition: Support for cohort-based, academic term, and rolling enrollment
patterns
Module Management: Exclusively managed by Curriculum Managers with teacher assignment
capabilities
Academic Term Management: Support for traditional academic calendar structures
Advanced Student Lifecycle Management
Comprehensive Enrollment Tracking: Student module enrollment with status management (Active,
Deferred, Completed, Cancelled)
Deferral Management: Student-initiated deferrals with flexible return cohort selection
Repeat Assessment Tracking: Individual module component failures with resubmission deadline
management
Extension Management: Module-level deadline extensions with tutor coordination
Exemption Tracking: Module-level exemptions with comprehensive documentation
Transfer Management: Seamless cohort transfers with complete audit trails
Teacher/Instructor Capabilities
Assigned Module Access: View students enrolled in specific modules for current/selected academic
terms
Results Management: Enter and edit results for students in assigned modules
Dynamic Assignment: Support for mid-module tutor changes with handover documentation
Module-Specific Communication: Post announcements to assigned modules
Progress Tracking: Monitor student progress and assessment completion

Student Course Registration
Self-Service Registration: Students can view available modules for given terms and register during
open periods
Registration Workflow: Configurable registration windows and approval processes
Prerequisite Management: Support for module dependencies and progression requirements
Communication and Notification System
Templated Email Module: Core system for creating, managing, and automatically sending
templated emails based on system events
Multi-Level Announcements: Curriculum Managers can post system-wide announcements;
Teachers can post module-specific announcements
Automated Notifications: System-generated notifications for key events (enrollment confirmations,
deadline reminders, status changes)
Comprehensive Auditing and Reporting
Activity Logging: Complete audit trail using laravel-activitylog for all system activities and data
changes
Student Journey Tracking: Comprehensive history of student progression, status changes, and
academic milestones
Administrative Reporting: Basic reporting and listing capabilities for institutional oversight
Compliance Documentation: Audit trails supporting QQI certification and institutional compliance
requirements
Integration Infrastructure
Microsoft 365 Integration (Future): Automated student account creation via Microsoft Graph API
Moodle LMS Integration (Low Priority): Course enrollment synchronization and student data
sharing
Stripe Payment Integration (Low Priority): Database schema and service placeholders for future
payment processing
QHub Integration (Future): Automated submission of student certification data to QQI portal
Technical Requirements and Constraints

Simplicity Priority: Technical stack kept simple due to single developer with education technology
background
Mobile Responsiveness: System must function effectively on mobile devices, particularly for student
self-service
Performance Requirements: System must significantly outperform current SuiteCRM solution
Scalability: Architecture designed to handle institutional growth and expanded program offerings
Security: Smart security implementation prioritizing technical simplicity while maintaining data
protection
2. Institution Context
2.1 Organization Profile
General Overview
Founded: 2004
Type: Private provider of flexible online education in Ireland
Specialization: Fully online QQI-accredited Further Education programmes, with one degree
programme in conjunction with Oxford Brookes University
Annual Enrolments: 2,000-3,500 students spread across the full year
Staff Structure: ~20 employees + 40 contractor teachers
Primary LMS: Moodle
Current Tools: BigBlueButton, Microsoft 365, bespoke SuiteCRM student database (no longer fit for
purpose)
Internal Communication: Microsoft Teams
Current System Limitations
The existing bespoke SuiteCRM system has become inadequate for institutional needs:
Poor performance and slow response times
Inflexible data structure unsuitable for diverse enrollment patterns
Limited reporting capabilities
No support for complex student lifecycle management
Inadequate integration with modern institutional tools
Certification Process
After students successfully complete their programmes, they are certified by the institution's internal
certification person who uploads student credentials to QHub (QQI's certification portal). This process
requires accurate tracking of student completion status and results.

2.2 Student and Programme Metrics
The institution serves a diverse student population with varying educational pathways:
Rolling Enrollments (80-90% of students)
Student Volume: Majority of 2,000-3,500 annual enrollments
Programme Type: Short single modules (3 months) or small programmes of 3-4 modules
Certification: QQI certified programmes
Delivery Method: Mostly asynchronous learning
Enrollment Pattern: Year-round enrollment with individual start dates
Academic Structure: No cohorts or academic years - individual progression
Current Authentication: No 365 logins (potential future conversion, especially if SIS proves highly
functional)
Student Lifecycle: Simple progression model with minimal complexity
2.3 Student Enrollment Patterns
2.3 Student Enrollment Patterns
The institution supports three distinct enrollment models, each requiring different administrative
approaches:
Cohort Enrollments (Priority - Childcare Programmes)
Programmes: Early Learning & Care Level 5 (ELC5) and Level 6 (ELC6)
Programme Structure: 4 modules with unique timing:
Placement Module: Runs for 12 months parallel to other modules
Sequential Modules: Three modules running consecutively across three start dates with
approximately 4-month duration each
Intake Schedule: 3 cohorts per year (January, April, September)
Cohort Naming Convention: YYMM format (2501 = January 2025, 2504 = April 2025, 2509 =
September 2025) for chronological sorting
Student Authentication: 365 logins provided
Complexity Level: High - complex lifecycle management with deferrals, repeats, transfers,
cancellations, and re-enrollments
Current Pain Point: Students frequently cancel, defer and return to different cohorts, creating
significant administrative burden in tracking which students belong in which Moodle modules
Academic Term Enrollments

Programme: Health and Social Care (3-year degree programme)
Partnership: Certified by Oxford Brookes University (institution's only degree programme)
Structure: Traditional academic year with defined terms
Duration: 3-year degree programme
Student Authentication: 365 logins provided
Complexity Level: Medium - follows traditional academic progression with defined term structure
Administrative Requirements: Academic term management, traditional grading systems, university
partnership reporting
Rolling Enrollments (Largest Student Population)
Student Volume: 80-90% of total annual enrollments (1,600-3,150 students)
Programme Types:
Single modules (3-month duration)
Small programmes consisting of 3-4 modules
Certification: QQI certified programmes
Learning Delivery: Predominantly asynchronous, allowing flexible pacing
Enrollment Pattern: Year-round enrollment with students starting at any time
Academic Structure: No cohorts or academic years - individual student progression
Student Authentication: Currently no 365 logins (potential future conversion, particularly if SIS
proves highly functional)
Complexity Level: Low to Medium - simpler administrative requirements but high volume
2.4 Current Administrative Challenges
Excel-Based Tracking System Problems
The institution currently manages student data through 9+ separate Excel files, creating significant
operational inefficiencies:
Core Tracking Files:

Student enquiries (separate sheets per cohort and programme)
Student enrollments (separate sheets per cohort and programme)
Deferral requests and results tracking (multiple cohort-specific sheets)
Transfer management between cohorts
Repeat assessments (separate tracking per cohort)
Extensions management (module-level tracking per cohort)
Exemptions tracking
Module repeat grades
Results for cancelled/deferred students
Operational Pain Points:
No unified student view across tracking systems
Manual cross-referencing between multiple files for single student queries
High risk of data inconsistency and human error
Difficult to track complete student journey across deferrals, transfers, and re-enrollments
Separate spreadsheet sheets required for every cohort in every tracking system
Time-intensive manual processes for routine administrative tasks
Difficulty generating comprehensive reports across student populations
Challenges in maintaining data accuracy during staff transitions
Moodle Course Management Complexity
Student Services staff struggle with Moodle course enrollment management:
Manual creation of course lists for each cohort (e.g., ELC501-2501, ELC502-2504)
Difficulty tracking which students should be enrolled in which Moodle course instances
Complex management when students defer and return to different cohorts
Manual enrollment updates when students repeat individual modules with different cohorts
3. System Vision and Goals
3.1 Vision Statement
To create a straightforward, modern, efficient, and intuitive Student Information System that streamlines
student data management, module allocation, result tracking, communication, and student self-service.
The system will provide tailored access for different staff roles and students, leveraging robust
institutional authentication, and preparing for future expansion.

Development Philosophy: Essential to keep the technical implementation as simple as possible given
the single developer's education technology background rather than extensive programming experience.
The system must prioritize functionality and user experience while maintaining technical simplicity and
maintainability.
Mobile Accessibility: The system needs to function effectively on mobile devices, particularly for student
self-service functions, while maintaining full desktop functionality for administrative staff.
3.2 Goals and Objectives
Primary Goals
Accuracy: Ensure all student data, module information, and results are accurate and consistently
maintained across all system interactions
Efficiency: Dramatically reduce manual administrative overhead and empower users with self-service
and role-specific capabilities
Accessibility: Provide secure access to authorized users from any standard web browser with
responsive mobile support
Usability: Offer an intuitive and responsive user interface that requires minimal training for adoption
Scalability: Design a system architecture capable of handling institutional growth in users,
programmes, and data volume
Security: Implement smart security measures prioritizing technical simplicity while maintaining
appropriate data protection
Extensibility: Design system architecture with future integrations (Stripe payments, enhanced
Moodle integration) in mind
Operational Objectives
Eliminate Excel Fragmentation: Replace 9+ separate Excel tracking files with unified digital
workflow
Reduce Data Entry Redundancy: Eliminate duplicate data entry across multiple tracking systems
Improve Student Services Efficiency: Provide unified student view reducing query resolution time
Automate Routine Processes: Implement automated notifications and workflow management
Enhance Reporting Capabilities: Enable comprehensive reporting across student populations and
programmes
Support Institutional Growth: Provide scalable foundation for expanded programme offerings
3.3 Key System Features
Unified Authentication and Authorization

Azure AD Integration: Single sign-on using institutional Azure AD via OpenID Connect
Configurable Role Mapping: Automatic role assignment based on Azure AD security group
membership with precedence logic
Role-Based Access Control: Granular permissions for Manager, Student Services, Teacher, and
Student roles
Comprehensive Student Management
Unified Student Profile: Single source of truth replacing fragmented Excel tracking systems
Advanced Lifecycle Management: Support for complex student journeys including deferrals,
repeats, transfers, and re-enrollment
Automated Student Account Creation: Future capability for Microsoft 365 account generation via
Graph API
Flexible Programme Architecture
Multi-Modal Support: Cohort-based, academic term, and rolling enrollment patterns within single
system
Programme Rule Engine: Configurable business rules for different programme types and
progression requirements
Module Instance Management: Support for programme-specific module delivery (e.g.,
ELC501-2501, ELC502-2504)
Advanced Workflow Management
Deferral Processing: Student-initiated deferrals with administrative approval and flexible return
cohort selection
Assessment Management: Comprehensive tracking of extensions, repeat assessments, and
exemptions
Teacher Assignment: Dynamic tutor assignment with mid-module handover capability
Automated Notifications: System-generated communications for key lifecycle events
Enhanced Communication System
Templated Email Module: Administrative interface for creating and managing automated email
communications
Multi-Level Announcements: System-wide and module-specific announcement capabilities
Student Self-Service: Comprehensive student portal for information access and course registration
Comprehensive Auditing and Compliance

Complete Audit Trail: All system activities logged using Laravel activity logging
Institutional Compliance: Support for QQI certification requirements and institutional reporting
needs
Data Integrity: Comprehensive validation and consistency checking across all system interactions
Integration Foundation
Moodle Coordination: Infrastructure for course enrollment synchronization and student data
sharing
Payment System Preparation: Database schema and service architecture for future Stripe
integration
Microsoft 365 Integration: Foundation for automated account creation and enhanced institutional
tool integration
4. Technical Architecture
3.1 Technology Stack
Proven Foundation (From Prototype)
Framework: Laravel 12.4
Language: PHP 8.2
Database: MySQL 8.x
Frontend: Blade Templates + Tailwind CSS + Alpine.js
Authentication: Azure AD via Laravel Socialite (OpenID Connect)
Server: Ubuntu VPS (Hetzner)
Web Server: Nginx
Version Control: Git
Development Environment
IDE: Visual Studio Code
Deployment: Manual (Git + Artisan)
Debugging: Laravel error pages + Chrome DevTools
3.2 Infrastructure Architecture

3.3 Application Architecture
Core Architecture Pattern: Program-Centric Design
Authentication & Authorization
┌─────────────────────────────────────────┐
│ Hetzner VPS │
│ ┌─────────────────────────────────────┐│
│ │ Ubuntu Server ││
│ │ ┌─────────────┐ ┌───────────────┐ ││
│ │ │ Nginx │ │ Laravel │ ││
│ │ │ Web Server │ │ Application │ ││
│ │ └─────────────┘ └───────────────┘ ││
│ │ ┌─────────────────────────────────┐ ││
│ │ │ MySQL 8.x │ ││
│ │ │ Database │ ││
│ │ └─────────────────────────────────┘ ││
│ └─────────────────────────────────────┘│
└─────────────────────────────────────────┘
│
│ HTTPS/OAuth
▼
┌─────────────────────────────────────────┐
│ Azure AD │
│ (Authentication Provider) │
└─────────────────────────────────────────┘
│
│ Future Integration
▼
┌─────────────────────────────────────────┐
│ Moodle LMS │
│ (Course Management) │
└─────────────────────────────────────────┘
Programs (ELC5, ELC6, Health & Social Care)
├── Program Rules (enrollment_type, progression_rules)
├── Cohorts (2501, 2504, 2509) ‐ for cohort‐
based programs
├── Academic Terms ‐ for term‐based programs
├── Modules (ELC501, ELC502, ELC504, Placement)
├── Module Instances (ELC501‐2501, ELC502‐2504)
└── Student Enrollments (with status, attempt tracking)

Azure AD Integration: OpenID Connect via Laravel Socialite
Role-Based Access Control (RBAC):
Curriculum Manager: Full system access
Student Services: Student CRUD, enrollment management
Teachers/Instructors: Assigned module access, results entry
Students: Self-service view, course registration
4. Core Data Model
4.1 Student Lifecycle Management
Student Status Workflow
Key Entities
Students
Unified student profile with demographic and contact information
New student number format (recommendation: sequential 2025001, 2025002...)
Comprehensive audit trail of all status changes
Programs & Cohorts
Program definitions with enrollment rules
Cohort management with YYMM naming (2501, 2504, 2509)
Module definitions and sequencing rules
Enrollments & Attempts
Student-to-module enrollments with attempt tracking
Status management (Active, Deferred, Completed, Failed, Repeat)
Flexible cohort switching for deferrals and repeats
4.2 Complex Workflow Management
Deferral System
Request Tracking: Student number, cohort, duration, reason
Flexible Return: Students choose return cohort (not automatic)
Audit Trail: Complete history of deferral requests and decisions
Assessment Management
Enquiry → Enrolled → Active → [Deferred/Extended/Repeat] → Completed/Cancelled

Extensions: Module-level deadline extensions with tutor updates
Repeat Assessments: Failed component tracking, resubmission deadlines
Module Repeats: Students can repeat individual modules with different cohorts
Exemptions: Module-level exemptions with documentation
Tutor Assignment
Dynamic Assignment: Usually 1:1 but changeable mid-module
Handover Management: Audit trail of tutor changes
Teacher Dashboard: Current assigned students and modules
5. Functional Requirements
5.1 User Role Capabilities
Curriculum Manager
Full system administration
Program and cohort management
Student oversight and data correction
System-wide announcements
Teacher assignment to modules
Comprehensive reporting
Student Services
Student CRUD operations
Enrollment management and status changes
Deferral request processing
Transfer management between cohorts
Student support and query resolution
Teachers/Instructors
View assigned students and modules
Results entry and grade management
Module-specific announcements
Extension and repeat assessment management
Student progress tracking
Students

Personal information view (read-only)
Enrolled modules and results access
Course registration (during open periods)
Announcement viewing (system and module-specific)
Document access (policies, transcripts)
5.2 Core System Features
Student Lifecycle Management
Unified student profile with complete history
Status workflow management (enquiry through completion)
Comprehensive audit logging using laravel-activitylog
Flexible cohort and program transfers
Academic Management
Program and module structure definition
Cohort creation and management
Results tracking with multiple attempt support
Grade calculation and transcript generation
Communication System
Templated email module for automated notifications
System-wide and module-specific announcements
Student and staff notification management
Workflow Automation
Automated status transitions based on business rules
Email notifications for key events
Deadline and milestone tracking
Integration hooks for future systems
6. Technical Implementation Strategy
6.1 Development Phases
Phase 1: Foundation (Priority - Childcare MVP)
Timeline: 4-6 weeks
Core Features:

Student CRUD with new numbering system
Program and cohort management (ELC5/ELC6)
Basic student lifecycle (Active, Deferred, Completed)
Simple deferral workflow with cohort selection
Teacher assignment and basic results entry
Success Criteria:
Replace main Excel enquiries/enrolments tracking
Handle childcare student deferrals and returns
Provide unified student dashboard for Student Services
Phase 2: Workflow Management
Timeline: 6-8 weeks
Enhanced Features:
Extensions and repeat assessment tracking
Module-level attempt management
Tutor assignment with handover capability
Enhanced reporting and student progress tracking
Email notification system
Success Criteria:
Replace deferral, extension, and repeat assessment Excel files
Automated workflow notifications
Complete childcare program management
Phase 3: Advanced Features
Timeline: 8-10 weeks
Advanced Features:
Academic term program support (Health & Social Care)
Rolling enrollment infrastructure
Advanced reporting and analytics
API foundations for future integrations
Mobile-responsive interface optimization
6.2 Database Design Strategy

Core Principles
Program-centric architecture supporting multiple enrollment patterns
Flexible status management with comprehensive audit trails
Attempt tracking for complex academic progression rules
Future extensibility for additional programs and integrations
Key Design Decisions
Polymorphic relationships for different program types
JSON columns for flexible metadata storage
Comprehensive logging for all data changes
Optimized indexing for common query patterns
6.3 Integration Architecture
Immediate Integrations
Azure AD: Existing OpenID Connect implementation
Email System: Laravel-based templated notifications
Future Integration Roadmap
Microsoft 365: Automated account creation via Graph API
Moodle LMS: Course enrollment synchronization
Stripe Payments: Student fee management
QHub Integration: Automated certification submission
7. Security & Compliance
7.1 Authentication & Authorization
Azure AD integration with institutional single sign-on
Role-based permission system with granular controls
Session management and secure logout procedures
7.2 Data Protection
GDPR-compliant student data handling
Comprehensive audit logging for all data access
Secure data transmission (HTTPS)
Regular backup procedures
7.3 System Security

Input validation and XSS protection
SQL injection prevention via Eloquent ORM
CSRF protection on all forms
Rate limiting for API endpoints
8. Success Metrics & Goals
8.1 Immediate Goals (Phase 1)
Eliminate 5+ Excel tracking files for childcare programs
Reduce manual data entry by 60% for Student Services
Provide unified student view across all systems
Automate deferral tracking and cohort transfer processes
8.2 Long-term Objectives
Complete Excel system replacement across all programs
Automated workflow management with minimal manual intervention
Integrated communication system with stakeholder notifications
Scalable architecture supporting institutional growth
8.3 Key Performance Indicators
Time reduction in student status changes
Error reduction in student data management
User adoption rate across staff roles
System uptime and performance metrics
9. Risk Assessment & Mitigation
9.1 Technical Risks
Risk: Complex data migration from existing systems
Mitigation: Gradual rollout starting with new students
Risk: Integration complexity with legacy systems
Mitigation: Phased integration approach with fallback procedures
9.2 Operational Risks

Risk: Staff resistance to new system
Mitigation: Comprehensive training and gradual feature introduction
Risk: Data loss during transition period
Mitigation: Parallel system operation with comprehensive backups
9.3 Business Continuity
Backup Procedures: Automated daily database backups
Recovery Plan: Documented system restoration procedures
Support Structure: Developer available for critical issues
Documentation: Comprehensive user guides and technical documentation
10. Implementation Roadmap
10.1 Immediate Next Steps
1. Environment Setup: Fresh Laravel 12.4 installation on Hetzner VPS
2. Core Database Design: Program, cohort, and student entity definitions
3. Authentication Implementation: Azure AD integration with role mapping
4. Basic UI Framework: Responsive layout with Tailwind CSS + Alpine.js
10.2 Development Milestones
Week 2: Student and cohort management functionality
Week 4: Basic enrollment and status tracking
Week 6: Deferral workflow implementation
Week 8: Teacher assignment and results entry
Week 10: Email notifications and reporting
10.3 Go-Live Strategy
Pilot Phase: Childcare programs only (2-4 weeks)
Feedback Collection: Staff input and system refinement
Full Deployment: All childcare cohorts with Excel backup
Expansion: Additional programs based on success metrics
Conclusion
Project Viability and Strategic Importance

This Student Information System design addresses critical operational challenges identified through
comprehensive analysis of the current Excel-based tracking system that spans 9+ separate files with
multiple sheets per cohort. The program-centric architecture provides the flexibility needed to support
the institution's diverse enrollment patterns (rolling, cohort-based, and academic term) while maintaining
the technical simplicity essential for successful development and long-term maintenance by an education
technologist.
Technical Feasibility Assessment
The technical approach is grounded in proven success, building upon a functional prototype developed
in just 4 days that successfully implemented Azure AD integration, basic CRUD operations, and
responsive UI components. The selected technology stack (Laravel 12.4, MySQL, Tailwind CSS, Alpine.js)
provides a mature, well-documented foundation suitable for rapid development while maintaining long-
term maintainability.
The phased implementation approach ensures manageable development cycles while delivering
immediate operational value to Student Services staff who currently manage complex childcare program
lifecycles through manual Excel processes. Success with the childcare MVP will establish the foundation
for institution-wide digital transformation of student data management.
Operational Impact and Return on Investment
Immediate Benefits (Phase 1 - Childcare MVP):
Elimination of 5+ Excel tracking files for childcare programs
60% reduction in manual data entry for Student Services staff
Unified student view eliminating cross-referencing between multiple files
Automated deferral tracking with cohort transfer management
Significant reduction in data inconsistency and human error risk
Long-term Institutional Benefits:
Complete replacement of fragmented Excel tracking across all programs
Automated workflow management with minimal manual intervention
Integrated communication system with stakeholder notifications
Scalable architecture supporting institutional growth and program expansion
Foundation for advanced integrations (Microsoft 365, Moodle, payment systems)
Risk Mitigation and Success Factors
Technical Risk Management:

Gradual rollout strategy starting with new students eliminates complex data migration requirements
Parallel system operation during transition period ensures business continuity
Proven technology stack reduces implementation risk
Comprehensive audit logging provides rollback capability if needed
Operational Success Factors:
Clear focus on solving existing pain points rather than introducing new complexity
Staff involvement in requirements definition ensures practical usability
Phased implementation allows for iterative improvement based on user feedback
Comprehensive documentation and training materials support adoption
Next Steps and Implementation Timeline
Immediate Actions (Weeks 1-2):
1. Fresh Laravel 12.4 project setup on existing Hetzner VPS infrastructure
2. Core database schema design implementing program-centric architecture
3. Azure AD integration configuration with role mapping
4. Basic responsive UI framework using Tailwind CSS and Alpine.js
Development Milestones:
Week 4: Student and cohort management with deferral workflow
Week 6: Teacher assignment and basic results entry
Week 8: Email notification system and enhanced reporting
Week 10: Childcare MVP ready for pilot deployment
Success Metrics and Evaluation Criteria:
Quantitative Measures: Time reduction in routine administrative tasks, error reduction in student
data management, user adoption rates
Qualitative Measures: Staff satisfaction with system usability, reduction in manual cross-referencing,
improved data accuracy
Technical Measures: System uptime and performance, successful integration with Azure AD, audit
trail completeness
Strategic Vision
This SIS represents more than a technology upgrade—it's a fundamental transformation of institutional
data management capabilities. By replacing fragmented Excel tracking with unified digital workflows, the
institution positions itself for:

Enhanced Student Experience: Improved service delivery through efficient administrative processes
Operational Excellence: Data-driven decision making supported by comprehensive reporting
Scalable Growth: Technical foundation supporting expanded program offerings and increased
enrollment
Compliance Readiness: Robust audit trails and data management supporting regulatory
requirements
Integration Capability: Platform for future enhancements including payment processing, advanced
LMS integration, and institutional analytics
The combination of clear business requirements, proven technical capability, and institutional
commitment to digital transformation creates optimal conditions for project success. This SIS will serve as
a model for educational technology implementation, demonstrating how targeted solutions can solve
complex operational challenges while maintaining technical simplicity and long-term sustainability.