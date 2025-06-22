# TOC-SIS Comprehensive Workflow Testing Scenarios

## Overview
This document provides detailed, realistic testing workflows for the TOC Student Information System (TOC-SIS) based on the 4-level Programme-Module architecture. These scenarios simulate real-world academic workflows from initial student enquiry through to graduation.

## Architecture Summary
- **Programme**: Static blueprint for full awards
- **ProgrammeInstance**: Live delivery container with intake dates
- **Module**: Static blueprint for study units with assessment components
- **ModuleInstance**: Live class delivery with tutor assignment
- **Enrolment**: Two-path system (Programme vs Module) student enrolments
- **StudentGradeRecord**: Individual assessment component grades

---

## 1. COMPLETE STUDENT JOURNEY WORKFLOWS

### 1.1 New Student Onboarding - Programme Route (Synchronous)

**Scenario**: Emma Wilson enquires about the BA in Business Management programme and completes the full onboarding process.

**Actors**: 
- Student Services Staff (Manager role)
- Student (Student role)
- Tutor (Teacher role)

**Prerequisites**:
- BA Business Management programme exists
- September 2024 intake programme instance exists
- Business Strategy module instance linked to programme curriculum
- John Smith assigned as tutor

**Workflow Steps**:

1. **Initial Enquiry** (Student Services)
   - Create student record with status 'enquiry'
   - Record contact details and programme interest
   - **Expected System Response**: Student record created with unique student number
   - **Success Criteria**: Student appears in enquiries list with 'enquiry' status

2. **Programme Information Provision** (Student Services)
   - View available programme instances
   - Send programme information and entry requirements
   - **Expected System Response**: Programme details displayed with intake dates
   - **Success Criteria**: Student receives comprehensive programme information

3. **Application Processing** (Student Services)
   - Update student status from 'enquiry' to 'enrolled'
   - Prepare for enrolment in programme instance
   - **Expected System Response**: Student status updated in database
   - **Success Criteria**: Student appears in enrolled students list

4. **Programme Enrolment** (Student Services)
   - Navigate to student record
   - Click "Enrol" → "Enrol in a Programme"
   - Select "BA Business Management - September 2024 Intake"
   - Set enrolment date as 15th August 2024
   - **Expected System Response**: 
     - Enrolment record created with type 'programme'
     - Student status updated to 'active'
     - Grade records auto-created for all assessment components
   - **Success Criteria**: 
     - Student linked to programme instance
     - Assessment slots created for Business Strategy module
     - Student can access their dashboard

5. **Student First Login** (Student)
   - Login via Azure AD integration
   - View personal dashboard
   - **Expected System Response**: Dashboard shows enrolled programme and modules
   - **Success Criteria**: Student sees "BA Business Management" and linked modules

6. **Academic Progression Begins** (Tutor)
   - View module instance dashboard
   - See enrolled students list
   - **Expected System Response**: Emma Wilson appears in Business Strategy class list
   - **Success Criteria**: Tutor can access student records and grade sheets

**Performance Expectations**:
- Enrolment process completes within 5 seconds
- Grade record creation for typical programme (5 modules, 10 assessments) within 2 seconds
- Dashboard loads within 3 seconds

**Failure Scenarios**:
- Duplicate enrolment attempt → System prevents with clear error message
- Programme instance past intake date → System warns but allows manager override
- Database transaction failure → All changes rolled back, clear error logged

---

### 1.2 Standalone Module Enrolment - Asynchronous Route

**Scenario**: Michael O'Connor, a working professional, enrolls in Employment Law as a standalone module for CPD.

**Actors**:
- Student Services Staff
- Student
- Tutor

**Prerequisites**:
- Employment Law module configured with `allows_standalone_enrolment = true`
- Module instance available with async delivery style
- Assessment strategy defined (Case Study Analysis - 100%)

**Workflow Steps**:

1. **CPD Enquiry** (Student Services)
   - Student calls asking about continuing professional development options
   - Create student record with status 'enquiry'
   - **Expected System Response**: Student record created with CPD flag
   - **Success Criteria**: Student appears in enquiries with module interest noted

2. **Module Availability Check** (Student Services)
   - Search available standalone modules
   - Filter by allows_standalone_enrolment = true
   - View Employment Law module details and assessment strategy
   - **Expected System Response**: List shows Employment Law with October 2024 start date
   - **Success Criteria**: Module appears in standalone enrolments list

3. **Standalone Module Enrolment** (Student Services)
   - Navigate to student record
   - Click "Enrol" → "Enrol in a Standalone Module"
   - Select "Employment Law - October 2024 (Async)"
   - Set enrolment date as 20th September 2024
   - **Expected System Response**:
     - Enrolment record created with type 'module'
     - programme_instance_id remains null
     - Grade record created for "Case Study Analysis"
   - **Success Criteria**: 
     - Student enrolled in single module only
     - No programme linkage created
     - Assessment slot available for grading

4. **Self-Paced Learning Begins** (Student)
   - Access module materials on scheduled start date
   - View assessment requirements
   - **Expected System Response**: Module content and deadlines displayed
   - **Success Criteria**: Student has clear assessment timeline

5. **Assessment Submission** (Student)
   - Submit case study analysis via Moodle (external system)
   - **Expected System Response**: Submission logged with timestamp
   - **Success Criteria**: Submission available for tutor grading

6. **Grading Process** (Tutor)
   - Access student grade records for Employment Law
   - Enter grade for "Case Study Analysis" (e.g., 78/100)
   - Add feedback comments
   - Set release date for results
   - **Expected System Response**: Grade stored with visibility controls
   - **Success Criteria**: Grade hidden from student until release date

7. **Results Release** (System/Tutor)
   - Automated release on scheduled date OR manual release
   - Student notification sent via email
   - **Expected System Response**: Grade becomes visible to student
   - **Success Criteria**: Student sees grade and feedback in dashboard

**Performance Expectations**:
- Module enrolment completes within 3 seconds
- Grade entry saves within 1 second
- Results release processes within 5 seconds

---

### 1.3 Academic Exception Handling - Deferral Workflow

**Scenario**: Emma Wilson (from scenario 1.1) faces personal circumstances requiring deferral from September 2024 to January 2025 intake.

**Actors**:
- Student
- Student Services Manager
- Academic Manager

**Prerequisites**:
- Emma enrolled in BA Business Management September 2024
- January 2025 programme instance exists for same programme
- Deferral request system available

**Workflow Steps**:

1. **Deferral Request Initiation** (Student)
   - Submit deferral request through student portal
   - Provide supporting documentation
   - **Expected System Response**: Deferral request logged with status 'pending'
   - **Success Criteria**: Request appears in staff approval queue

2. **Initial Assessment** (Student Services)
   - Review deferral request and documentation
   - Check availability in target intake (January 2025)
   - **Expected System Response**: System shows seat availability in Jan 2025 intake
   - **Success Criteria**: Clear recommendation for approval/rejection

3. **Academic Approval** (Academic Manager)
   - Review deferral request with academic rationale
   - Approve deferral to January 2025 intake
   - **Expected System Response**: Approval logged with manager details
   - **Success Criteria**: Status updated to 'approved'

4. **Deferral Processing** (Student Services)
   - Execute deferral transfer using EnrolmentService
   - Original enrolment updated to point to January 2025 instance
   - Old grade records removed, new ones created
   - **Expected System Response**:
     - Student status updated to 'active' 
     - New assessment slots created for January 2025 curriculum
     - Old grade records archived/deleted
   - **Success Criteria**: Student linked to new programme instance

5. **Student Notification** (System)
   - Automated email confirming deferral approval
   - New intake details and important dates
   - **Expected System Response**: Email sent with programme details
   - **Success Criteria**: Student receives confirmation with action items

6. **Re-engagement** (Student Services)
   - Contact student before new intake starts
   - Confirm continued interest and attendance
   - **Expected System Response**: Student record shows active status
   - **Success Criteria**: Student prepared for January 2025 start

**Complexity Factors**:
- Multiple programme instances to coordinate
- Grade record migration between intakes
- Notification timing and content
- Seat availability management

---

## 2. ADMINISTRATIVE WORKFLOWS

### 2.1 Academic Year Setup - Complete Programme Delivery Planning

**Scenario**: Academic Manager Sarah Jones sets up the 2025-2026 academic year with multiple programme deliveries.

**Actors**:
- Academic Manager
- Programme Coordinator
- Finance Team
- Systems Administrator

**Prerequisites**:
- Programme blueprints exist (BA Business, Diploma Marketing, etc.)
- Staff assignments available
- Academic calendar approved

**Workflow Steps**:

1. **Strategic Planning Phase** (Academic Manager)
   - Review previous year enrolment data
   - Plan programme instance deliveries for 2025-2026
   - **Expected System Response**: Historical data accessible via analytics
   - **Success Criteria**: Clear delivery plan with target numbers

2. **Programme Instance Creation** (Academic Manager)
   
   **BA Business Management Instances**:
   - Create "September 2025 Intake" (Sync)
     - intake_start_date: 2025-09-01
     - intake_end_date: 2028-06-30
     - default_delivery_style: 'sync'
   - Create "January 2026 Intake" (Sync)
     - intake_start_date: 2026-01-15
     - intake_end_date: 2028-12-15 
     - default_delivery_style: 'sync'
   
   **Digital Marketing Diploma**:
   - Create "2025 Rolling Enrolment" (Async)
     - intake_start_date: 2025-01-01
     - intake_end_date: 2025-12-31
     - default_delivery_style: 'async'
   
   - **Expected System Response**: Programme instances created with unique IDs
   - **Success Criteria**: Instances appear in enrollment options

3. **Module Instance Planning** (Programme Coordinator)
   
   **For BA Business September 2025**:
   - Create Business Strategy module instance
     - start_date: 2025-09-15
     - target_end_date: 2025-12-15
     - tutor_id: John Smith
     - delivery_style: 'sync'
   
   - Create Marketing Fundamentals module instance
     - start_date: 2025-10-01
     - target_end_date: 2026-01-15
     - tutor_id: Sarah Jones
     - delivery_style: 'sync'
   
   **For Digital Marketing Rolling**:
   - Create monthly instances of core modules
   - Stagger start dates for rolling enrollment
   
   - **Expected System Response**: Module instances created and scheduled
   - **Success Criteria**: Academic calendar populated with module deliveries

4. **Curriculum Linking** (Academic Manager)
   - Link module instances to programme instances via pivot table
   - BA Business Sept 2025 → Business Strategy + Marketing Fundamentals
   - Digital Marketing 2025 → all diploma module instances
   - **Expected System Response**: Curriculum relationships established
   - **Success Criteria**: Students enrolling in programmes get correct modules

5. **Resource Planning** (Programme Coordinator)
   - Assign tutors to module instances
   - Verify assessment strategies are current
   - Plan assessment release schedules
   - **Expected System Response**: Staff assignments logged
   - **Success Criteria**: All modules have assigned tutors

6. **Validation Testing** (Academic Manager)
   - Test enrolment workflows with dummy student
   - Verify grade record creation
   - Test notification systems
   - **Expected System Response**: All systems functional
   - **Success Criteria**: Ready for live student enrolments

**Performance Expectations**:
- Programme instance creation: <2 seconds each
- Module instance bulk creation: <10 seconds for 20 instances
- Curriculum linking: <1 second per relationship

**Bulk Operations Considerations**:
- Create 50+ module instances for full academic year
- Link 200+ curriculum relationships
- Import 500+ continuing students
- Process 1000+ new applications

---

### 2.2 Bulk Enrolment Management - September Intake Processing

**Scenario**: Process 150 new students for September 2025 BA Business Management intake.

**Actors**:
- Student Services Team (3 staff members)
- Systems Administrator
- Academic Manager

**Prerequisites**:
- 150 approved student applications
- BA Business Management September 2025 instance ready
- Bulk processing tools available

**Workflow Steps**:

1. **Data Preparation** (Systems Administrator)
   - Export approved applications from CRM
   - Validate student data completeness
   - Format for bulk import
   - **Expected System Response**: Clean dataset ready for processing
   - **Success Criteria**: All 150 records validated and formatted

2. **Bulk Student Creation** (Student Services Manager)
   - Import student records via CSV or API
   - Auto-generate student numbers
   - Set initial status as 'enrolled'
   - **Expected System Response**: 150 student records created
   - **Success Criteria**: No duplicate student numbers, all mandatory fields populated

3. **Bulk Programme Enrolment** (Student Services Team)
   - **Option A**: Sequential Processing (for safety)
     - Process in batches of 25 students
     - Use EnrolmentService for each student
     - Monitor for failures and retry
   
   - **Option B**: Bulk API Processing (for speed)
     - Single API call with all 150 students
     - Transactional processing with rollback capability
   
   - **Expected System Response**: 
     - 150 programme enrolments created
     - ~600 grade records generated (4 assessments per student)
     - All students linked to correct programme instance
   
   - **Success Criteria**: 
     - Zero enrolment failures
     - All students appear in BA Business September 2025 class lists
     - Grade sheets populated for all tutors

4. **Verification Phase** (Student Services Team)
   - **Enrollment Verification**:
     - Query count: SELECT COUNT(*) FROM enrolments WHERE programme_instance_id = [Sept2025]
     - Expected result: 150
   
   - **Grade Record Verification**:
     - Query count: SELECT COUNT(*) FROM student_grade_records WHERE module_instance_id IN [curriculum_modules]
     - Expected result: 600 (150 students × 4 assessments)
   
   - **Student Status Verification**:
     - Query: SELECT status, COUNT(*) FROM students WHERE id IN [new_students] GROUP BY status
     - Expected result: All 'active'

5. **Welcome Communications** (Student Services Team)
   - Bulk email generation with student-specific details
   - Include student numbers, login instructions, programme details
   - Schedule delivery for optimal timing
   - **Expected System Response**: 150 personalized emails queued
   - **Success Criteria**: All students receive welcome communications

6. **System Performance Monitoring** (Systems Administrator)
   - Monitor database performance during bulk operations
   - Check queue processing for notifications
   - Verify no system degradation
   - **Expected System Response**: System remains responsive
   - **Success Criteria**: Normal response times maintained

**Performance Benchmarks**:
- Student creation: 150 records in <30 seconds
- Bulk enrolment: 150 students in <2 minutes
- Grade record creation: 600 records in <1 minute
- Email queue processing: 150 emails in <5 minutes

**Error Handling**:
- Partial failure recovery with detailed error logging
- Rollback capability for failed transactions
- Manual intervention procedures for edge cases
- Duplicate detection and resolution

---

## 3. MULTI-ROLE COLLABORATION WORKFLOWS

### 3.1 Assessment Management - End-to-End Grading Cycle

**Scenario**: Complete assessment cycle for Business Strategy module with 25 enrolled students, involving multiple staff roles and complex grading requirements.

**Actors**:
- Module Tutor (John Smith - Teacher role)
- External Examiner (Dr. Mary O'Brien - External reviewer)
- Programme Manager (Sarah Jones - Manager role)
- Student Services Staff (Admin support)
- Students (25 enrolled learners)

**Prerequisites**:
- 25 students enrolled in Business Strategy module instance
- Assessment strategy: Strategic Analysis Essay (40%) + Final Examination (60%)
- External examiner appointed
- Grade release scheduled for specific date

**Workflow Steps**:

1. **Assessment Preparation Phase** (Module Tutor)
   - Review enrolled student list (25 students)
   - Prepare assessment briefs and marking rubrics
   - Set submission deadlines and release dates
   - **Expected System Response**: Grade sheets show all 25 students with empty assessment slots
   - **Success Criteria**: 50 grade records available (25 students × 2 assessments)

2. **Student Assessment Submission** (Students via Moodle)
   - All 25 students submit Strategic Analysis Essays
   - 23 students complete Final Examination (2 absences)
   - **Expected System Response**: Submission status tracked in external system
   - **Success Criteria**: Submission data available for grading workflow

3. **Initial Grading Phase** (Module Tutor)
   - Grade Strategic Analysis Essays for all 25 students
   - Enter grades in TOC-SIS grade records
   - Sample entries:
     - Student 1: Essay 78/100, Exam [pending]
     - Student 2: Essay 65/100, Exam [pending]
     - Student 3: Essay 82/100, Exam [pending]
   - Add detailed feedback for each submission
   - **Expected System Response**: 25 essay grades recorded, visibility=false
   - **Success Criteria**: All essay grades entered and saved

4. **Final Examination Grading** (Module Tutor)
   - Grade final examinations for 23 students
   - Handle absent students (defer exams or special arrangements)
   - Mark must-pass components according to module rules
   - Sample grades:
     - Student 1: Essay 78/100, Exam 72/100
     - Student 2: Essay 65/100, Exam 58/100  
     - Student 3: Essay 82/100, Exam 85/100
   - **Expected System Response**: 23 exam grades recorded, 2 marked as absent
   - **Success Criteria**: All available grades entered with appropriate status

5. **Grade Calculation and Review** (Module Tutor)
   - Calculate weighted final grades using system tools
   - Review borderline cases and must-pass requirements
   - Identify students requiring attention:
     - Student 2: Failed exam (58% vs 40% pass mark) - must-pass component
     - Students needing repeat examination opportunities
   - **Expected System Response**: System calculates final grades with pass/fail indicators
   - **Success Criteria**: All final grades calculated according to assessment strategy

6. **External Examiner Review** (External Examiner + Programme Manager)
   - Programme Manager provides secure access to grade records
   - External examiner reviews sample of assessments
   - Validates grading standards and consistency
   - Approves final grade recommendations
   - **Expected System Response**: External review logged in system
   - **Success Criteria**: External examiner approval documented

7. **Grade Moderation Meeting** (Module Tutor + Programme Manager)
   - Review all borderline cases
   - Confirm repeat assessment requirements
   - Make final decisions on grade adjustments
   - Document rationale for all decisions
   - **Expected System Response**: Grade adjustments logged with justification
   - **Success Criteria**: Final grades approved for release

8. **Results Processing** (Programme Manager)
   - Set grade visibility to true for successful students
   - Schedule results release date
   - Prepare repeat assessment arrangements for failed students
   - **Expected System Response**: 23 students have grades ready for release, 2 marked for repeat
   - **Success Criteria**: Results ready for automated release

9. **Automated Results Release** (System)
   - Release grades to students on scheduled date
   - Send notification emails with transcript attachments
   - Update student progress records
   - **Expected System Response**: 23 students receive grade notifications
   - **Success Criteria**: All notifications delivered successfully

10. **Post-Results Support** (Student Services + Module Tutor)
    - Handle student queries about grades
    - Process appeals if submitted
    - Coordinate repeat assessments for failed students
    - **Expected System Response**: Query tracking system active
    - **Success Criteria**: All student communications handled promptly

**Complexity Factors**:
- 50 individual grade records to manage
- Multiple assessment components with different weighting
- Must-pass requirements enforcement
- External examiner coordination
- Absent student handling
- Repeat assessment coordination

**Performance Expectations**:
- Grade entry: <2 seconds per grade record
- Bulk grade calculation: <5 seconds for 25 students
- Results release: <10 seconds for 23 students
- Email notifications: <30 seconds for all students

---

### 3.2 Student Progression Monitoring - Academic Standards Committee Review

**Scenario**: Quarterly academic standards review involving 200+ students across multiple programmes, identifying at-risk students and intervention requirements.

**Actors**:
- Academic Standards Committee (3 managers)
- Programme Managers (5 staff)
- Student Services Team (support)
- Data Analyst (reporting)
- Students (intervention recipients)

**Prerequisites**:
- 200+ students across various programmes and progression stages
- Established academic standards and intervention thresholds
- Reporting tools and analytics dashboard available

**Workflow Steps**:

1. **Data Collection Phase** (Data Analyst)
   - Generate comprehensive student progress reports
   - Query all active enrolments and current grade records
   - Calculate module completion rates and GPA equivalents
   - Identify students with concerning patterns:
     - Multiple failed assessments
     - Extended periods without progression
     - Attendance issues (if tracked)
   - **Expected System Response**: Comprehensive dataset with 200+ student records
   - **Success Criteria**: All active students included with current status

2. **Risk Categorization** (Data Analyst + Academic Standards Committee)
   - **High Risk** (Immediate intervention required):
     - Students with 2+ failed modules
     - Students inactive >6 months
     - Students requesting multiple deferrals
   - **Medium Risk** (Monitoring required):
     - Students with 1 failed module
     - Students with extended completion times
     - Students with pattern of late submissions
   - **Low Risk** (Normal progression):
     - Students meeting expected timelines
     - Students with passing grades
   - **Expected System Response**: Students categorized by risk level
   - **Success Criteria**: Clear intervention priorities established

3. **Programme-Level Analysis** (Programme Managers)
   - **BA Business Management Review**:
     - 45 students across 3 cohorts
     - 8 students identified as high risk
     - 12 students medium risk
     - Module completion rates by cohort
   
   - **Digital Marketing Diploma Review**:
     - 60 students in rolling enrolment
     - 5 students high risk  
     - 18 students medium risk
     - Async delivery effectiveness analysis
   
   - **Expected System Response**: Programme-specific analytics available
   - **Success Criteria**: Detailed understanding of programme performance

4. **Individual Student Case Review** (Academic Standards Committee)
   - **High Risk Student Example - Student ID 1247**:
     - Enrolled: BA Business Management Sept 2024
     - Status: Failed Business Strategy (Sep 2024), Failed Marketing Fundamentals (Jan 2025)
     - Current situation: Deferred from current modules
     - Recommendation: Academic counseling + repeat assessments
   
   - **Medium Risk Student Example - Student ID 1089**:
     - Enrolled: Digital Marketing Diploma Rolling
     - Status: Slow progression, completed 2/4 modules in 8 months
     - Current situation: Personal circumstances affecting study
     - Recommendation: Flexible scheduling + support services
   
   - **Expected System Response**: Individual student histories accessible
   - **Success Criteria**: Tailored intervention plans for each student

5. **Intervention Planning** (Programme Managers + Student Services)
   - **Academic Support Interventions**:
     - Mandatory study skills workshops for high-risk students
     - Peer mentoring programme enrollment
     - Additional tutorial sessions
   
   - **Administrative Interventions**:
     - Extended deadline accommodations
     - Module deferral arrangements
     - Fee adjustment considerations
   
   - **Counseling Support**:
     - Student welfare referrals
     - Career guidance sessions
     - Mental health support coordination
   
   - **Expected System Response**: Intervention plans logged in student records
   - **Success Criteria**: Every at-risk student has specific support plan

6. **Implementation Coordination** (Student Services Team)
   - Contact all identified at-risk students
   - Schedule intervention meetings and support sessions
   - Coordinate with external support services
   - Track intervention engagement and effectiveness
   - **Expected System Response**: Student contact log maintained
   - **Success Criteria**: All students contacted within 5 working days

7. **Progress Monitoring** (Programme Managers)
   - Monthly check-ins with high-risk students
   - Quarterly review of medium-risk students
   - Track improvement in assessment performance
   - Adjust intervention strategies based on outcomes
   - **Expected System Response**: Progress tracking data available
   - **Success Criteria**: Measurable improvement in student outcomes

8. **Committee Reporting** (Academic Standards Committee)
   - Quarterly committee meeting with findings
   - Review effectiveness of interventions
   - Policy recommendations based on patterns
   - Resource allocation requests for support services
   - **Expected System Response**: Committee reports generated
   - **Success Criteria**: Evidence-based policy decisions

**Analytics Requirements**:
- Real-time dashboard showing student risk levels
- Historical trend analysis for early warning indicators
- Intervention effectiveness tracking
- Programme comparison metrics
- Predictive modeling for future at-risk identification

**Success Metrics**:
- Reduction in student withdrawal rates
- Improved module completion rates
- Faster response time to student difficulties
- Increased student satisfaction with support services

---

## 4. EDGE CASE & STRESS TEST SCENARIOS

### 4.1 High Volume Concurrent Operations - Results Release Day

**Scenario**: Simultaneous results release for 500+ students across multiple programmes during peak system usage.

**Actors**:
- System (automated processes)
- Students (500+ concurrent users)
- Student Services Staff (helpdesk support)
- Systems Administrator (monitoring)

**Prerequisites**:
- 500+ students with grades ready for release
- Email system configured for high volume
- System monitoring tools active
- Helpdesk staff on standby

**Test Scenarios**:

1. **Concurrent Grade Release Processing**
   - **Load**: 500 students, 2000+ grade records
   - **Timing**: All grades released simultaneously at 9:00 AM
   - **Expected System Response**: 
     - All grade visibility updates processed within 2 minutes
     - Email queue populated with 500+ notifications
     - No database deadlocks or transaction failures
   - **Performance Targets**:
     - Database operations: <100ms average response time
     - Email queue processing: 50 emails/minute sustained
     - System availability: 99.9% uptime maintained

2. **Concurrent Student Portal Access**
   - **Load**: 400+ students logging in simultaneously
   - **Peak timing**: 9:05 AM (5 minutes after results release)
   - **Expected System Response**:
     - Authentication system handles concurrent logins
     - Dashboard loads within 5 seconds for each user
     - Grade display accurate and consistent
   - **Stress Factors**:
     - Azure AD authentication load
     - Database connection pool management
     - Session management scalability

3. **Email System Stress Test**
   - **Volume**: 500+ personalized emails with PDF attachments
   - **Content**: Grade transcripts with detailed assessment breakdown
   - **Expected System Response**:
     - All emails queued within 5 minutes
     - Delivery completion within 30 minutes
     - Zero email failures or bounces (for valid addresses)
   - **Monitoring Requirements**:
     - Email delivery status tracking
     - Queue depth monitoring
     - SMTP server performance metrics

**Failure Recovery Scenarios**:

1. **Database Connection Pool Exhaustion**
   - **Trigger**: Too many concurrent grade visibility updates
   - **Expected Behavior**: Graceful queue management, no data corruption
   - **Recovery**: Automatic connection recycling, transparent to users

2. **Email System Overload**
   - **Trigger**: Email service rate limiting or failures
   - **Expected Behavior**: Retry mechanism with exponential backoff
   - **Recovery**: Failed emails re-queued, delivery status tracked

3. **Authentication System Failure**
   - **Trigger**: Azure AD service disruption
   - **Expected Behavior**: Clear error messages, local authentication fallback
   - **Recovery**: Service restoration notification, cached session handling

---

### 4.2 Data Integrity Under Concurrent Modifications

**Scenario**: Multiple staff members simultaneously updating student records, grades, and enrolments.

**Test Conditions**:
- 3 staff members editing same student record
- 2 tutors grading same module assessments
- 1 manager processing enrolment changes
- Concurrent database transactions

**Race Condition Tests**:

1. **Concurrent Grade Entry**
   - **Setup**: Two tutors enter different assessment grades for same student
   - **Test**: Tutor A enters Essay grade, Tutor B enters Exam grade simultaneously
   - **Expected Result**: Both grades saved correctly, no overwrites
   - **Validation**: Student_grade_records table maintains data integrity

2. **Concurrent Enrolment Modifications**
   - **Setup**: Manager withdraws student while system processes deferral
   - **Test**: Withdrawal and deferral transactions execute simultaneously
   - **Expected Result**: Database transactions properly isolated
   - **Validation**: Only one operation succeeds, proper error handling

3. **Grade Visibility Race Conditions**
   - **Setup**: Automated release triggers while tutor manually updates visibility
   - **Test**: System and manual visibility changes conflict
   - **Expected Result**: Most recent change wins, audit trail maintained
   - **Validation**: Consistent visibility state, no partial updates

**Database Transaction Testing**:
- Row-level locking effectiveness
- Deadlock detection and resolution
- Transaction rollback completeness
- Referential integrity maintenance

---

### 4.3 System Recovery and Data Migration Scenarios

**Scenario**: Legacy system migration and disaster recovery procedures.

**Migration Test Cases**:

1. **Large-Scale Data Import**
   - **Source**: Legacy SIS with 2000+ student records
   - **Complexity**: Multiple programme types, historical grades, complex relationships
   - **Test Requirements**:
     - Data transformation accuracy
     - Relationship preservation
     - Performance under load
     - Rollback capability

2. **Partial System Failure Recovery**
   - **Scenario**: Database corruption during peak usage
   - **Test Procedures**:
     - Point-in-time recovery capabilities
     - Data consistency validation
     - Service restoration time
     - User communication protocols

3. **Version Upgrade Testing**
   - **Context**: Major system upgrade with schema changes
   - **Validation Requirements**:
     - Zero data loss during upgrade
     - Backward compatibility testing
     - Feature rollback procedures
     - User training requirements

---

## 5. INTEGRATION WORKFLOWS

### 5.1 Azure AD Integration - Complete Authentication Cycle

**Scenario**: Full Azure AD integration testing covering authentication, role assignment, and user provisioning.

**Test Components**:

1. **User Authentication Flow**
   - **New User**: First-time login via Azure AD
   - **Existing User**: Regular authentication with role verification
   - **Failed Authentication**: Invalid credentials and account lockouts
   - **Expected Behaviors**:
     - Seamless SSO experience
     - Automatic role assignment based on AD groups
     - Proper error handling for authentication failures

2. **Role Mapping Validation**
   - **Test Groups**:
     - @theopencollege.edu domain → Student role
     - TOC-SIS-Teachers group → Teacher role
     - TOC-SIS-Managers group → Manager role
     - TOC-SIS-StudentServices group → Student Services role
   - **Validation**:
     - Correct role assignment on first login
     - Role changes reflected after AD group updates
     - Access control enforcement based on roles

3. **Student Account Linking**
   - **Process**: Link Azure AD accounts to student records via email matching
   - **Test Cases**:
     - Successful email match and account linking
     - Multiple student records with same email handling
     - Unmatched email address procedures
   - **Expected Results**:
     - Students can access their academic records
     - Proper privacy controls enforced
     - Clear messaging for unlinked accounts

---

### 5.2 Email System Integration - Comprehensive Communication Testing

**Scenario**: End-to-end email system testing with various notification types and delivery methods.

**Test Categories**:

1. **Automated Notifications**
   - **Grade Release Notifications**: Bulk delivery to 100+ students
   - **Assessment Reminders**: Scheduled delivery with deadline tracking
   - **Approval Notifications**: Staff workflow notifications
   - **Performance Metrics**:
     - Delivery success rate >98%
     - Average delivery time <2 minutes
     - Template rendering accuracy 100%

2. **Manual Communications**
   - **Individual Student Emails**: Personalized communication from staff
   - **Bulk Announcements**: Programme-wide updates and news
   - **Emergency Communications**: Critical updates with priority delivery
   - **Features Testing**:
     - Rich text editing and formatting
     - File attachments (transcripts, certificates)
     - Email tracking and read receipts

3. **Email Template System**
   - **Template Management**: Create, edit, and version control
   - **Variable Substitution**: Student names, grades, dates, programme details
   - **Multi-language Support**: English and Irish language options
   - **Validation Requirements**:
     - Template syntax validation
     - Variable substitution accuracy
     - Responsive design for mobile devices

---

### 5.3 Moodle Integration - Course and Enrollment Synchronization

**Scenario**: Bidirectional integration with Moodle LMS for course delivery and assessment submission.

**Integration Points**:

1. **Course Creation Synchronization**
   - **Trigger**: New module instance created in TOC-SIS
   - **Process**: Automatic Moodle course creation with:
     - Course name matching module title
     - Course category based on programme
     - Enrollment key configuration
     - Assessment setup based on module strategy
   - **Validation**: Course accessible and properly configured

2. **Student Enrollment Synchronization**
   - **Trigger**: Student enrolled in module instance
   - **Process**: Automatic Moodle enrollment with:
     - Student role assignment
     - Group membership based on cohort
     - Access permissions configuration
   - **Bidirectional Sync**: Handle enrollment changes from both systems

3. **Grade Passback Integration**
   - **Process**: Assessment submissions in Moodle trigger grade updates in TOC-SIS
   - **Data Flow**:
     - Moodle submission → TOC-SIS student_grade_records
     - Grade validation and approval workflow
     - Results visibility control in TOC-SIS
   - **Error Handling**: Failed grade passback retry mechanisms

---

## 6. VALIDATION CHECKLISTS AND PERFORMANCE BENCHMARKS

### 6.1 Functional Validation Checklist

**Student Management**:
- [ ] Student record creation with unique student number generation
- [ ] Student status progression (enquiry → enrolled → active → graduated)
- [ ] Email address validation and duplicate checking
- [ ] Student search and filtering functionality
- [ ] Student record merge capabilities for duplicates

**Enrolment Processing**:
- [ ] Programme enrolment with automatic grade record creation
- [ ] Standalone module enrolment validation
- [ ] Duplicate enrolment prevention
- [ ] Enrolment date validation and business rules
- [ ] Withdrawal and deferral processing

**Grade Management**:
- [ ] Grade entry with validation rules
- [ ] Assessment component grade recording
- [ ] Grade calculation based on weighting
- [ ] Grade visibility controls and scheduled release
- [ ] Grade audit trail and change logging

**Assessment Strategy**:
- [ ] Module assessment component definition
- [ ] Must-pass component enforcement
- [ ] Weighted grade calculation accuracy
- [ ] Pass/fail determination logic
- [ ] Repeat assessment workflow

**Notification System**:
- [ ] Email template rendering with variables
- [ ] Bulk email queue processing
- [ ] Notification scheduling and delivery
- [ ] Email delivery status tracking
- [ ] User notification preferences

### 6.2 Performance Benchmarks

**Database Operations**:
- Student record creation: <500ms
- Enrolment processing: <2 seconds
- Grade record creation (bulk): <100ms per record
- Complex queries (student progress): <1 second
- Bulk operations (100+ records): <30 seconds

**User Interface Response Times**:
- Dashboard loading: <3 seconds
- Student search results: <2 seconds
- Grade entry form: <1 second
- Report generation: <10 seconds
- Navigation between pages: <1 second

**System Scalability**:
- Concurrent users: 100+ without degradation
- Database connections: 50+ concurrent
- Email queue processing: 100+ emails/minute
- File upload/download: 10MB files in <30 seconds
- Backup operations: No impact on user experience

**Integration Performance**:
- Azure AD authentication: <2 seconds
- Email delivery initiation: <5 seconds
- Moodle synchronization: <10 seconds
- API response times: <500ms
- External service failover: <30 seconds

---

## 7. TEST DATA GENERATION SCRIPTS

### 7.1 Realistic Academic Programme Structure

**Programme Hierarchy**:
- 5 Programmes (various NFQ levels 6-8)
- 15 Programme Instances (multiple intakes per programme)
- 25 Modules (diverse assessment strategies)
- 75 Module Instances (covering 2 academic years)
- 500 Students (various progression stages)
- 1200 Enrolments (mix of programme and standalone)
- 4800 Grade Records (realistic assessment load)

**Assessment Strategies**:
- Single Assessment: 100% final project
- Two Components: 40% coursework + 60% exam
- Three Components: 30% essay + 30% presentation + 40% exam
- Must-Pass Components: Final exam must pass regardless of overall grade
- Portfolio-Based: Multiple submissions throughout term

### 7.2 Student Progression Scenarios

**Typical Student Journeys**:
- High Achiever: Consistent 70%+ grades, on-time completion
- Average Student: 50-65% grades, occasional extensions
- Struggling Student: Multiple failures, deferrals, repeat assessments
- Part-Time Professional: Slow progression, high grades
- Withdrawn Student: Started but discontinued studies

**Timeline Distributions**:
- Programme completion: 2-4 years (depending on deferrals)
- Module completion: 3-6 months (depending on delivery style)
- Assessment submission: Throughout delivery period
- Grade release: 2-4 weeks after submission deadlines

---

## 8. IMPLEMENTATION PRIORITIES

### Phase 1: Core Functionality (Essential)
1. Student journey workflows (1.1, 1.2)
2. Basic administrative workflows (2.1)
3. Grade management workflow (3.1)
4. Functional validation checklist (6.1)

### Phase 2: Advanced Features (Important)
1. Bulk operations workflow (2.2)
2. Academic standards workflow (3.2)
3. Integration workflows (5.1, 5.2, 5.3)
4. Performance benchmarks (6.2)

### Phase 3: Stress Testing (Optimal)
1. High volume scenarios (4.1, 4.2)
2. System recovery scenarios (4.3)
3. Advanced integration testing
4. Comprehensive performance optimization

---

## CONCLUSION

These workflow scenarios provide comprehensive testing coverage for the TOC-SIS system, ensuring robust functionality across all user roles and system integration points. The scenarios balance realistic academic requirements with technical validation needs, providing clear success criteria and performance expectations.

The workflows are designed to be executed independently or as integrated test suites, supporting both manual testing procedures and automated testing framework development. Each scenario includes detailed validation steps and expected outcomes, enabling systematic verification of system functionality and performance under various conditions.

**Key Success Factors**:
- Complete end-to-end workflow coverage
- Realistic academic business scenarios
- Clear performance expectations
- Comprehensive error handling validation
- Integration testing across all external systems
- Scalability testing under realistic load conditions

These scenarios will ensure the TOC-SIS system meets the complex requirements of modern academic administration while maintaining high performance and reliability standards.