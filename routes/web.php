<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\AzureController;
use App\Http\Controllers\DeferralController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\EnrolmentController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\ExtensionRequestController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleInstanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ProgrammeInstanceController;
use App\Http\Controllers\RepeatAssessmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentEmailController;
use App\Http\Controllers\StudentGradeRecordController;
use App\Http\Controllers\TranscriptController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Azure AD authentication
Route::get('/login', [AzureController::class, 'redirect'])->name('login');
Route::get('/callback', [AzureController::class, 'callback']);
Route::post('/logout', [AzureController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Dashboard - role-based routing
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'student') {
            // Check if user has a linked student record
            if (! Auth::user()->student) {
                return view('dashboard-student-error', [
                    'user' => Auth::user(),
                    'message' => 'No student record found. Please contact Student Services.',
                ]);
            }

            return view('dashboard-student');
        }

        return view('dashboard');
    })->name('dashboard');

    // API routes for dashboard search
    Route::middleware(['role:manager,student_services,teacher'])->group(function () {
        Route::get('/api/students/search', [StudentController::class, 'search'])->name('students.search');
    });

    // =================================================================
    // STUDENT-ONLY ROUTES - New Architecture
    // =================================================================
    Route::middleware(['role:student'])->group(function () {

        // Student profile (basic access)
        Route::get('/my-profile', function () {
            $student = Auth::user()->student;
            if (! $student) {
                abort(404, 'Student record not found');
            }
            $student->load(['enrolments.programmeInstance.programme', 'enrolments.moduleInstance.module']);

            return view('students.profile', compact('student'));
        })->name('students.profile');

        // Routes requiring active enrollment
        Route::middleware(['require_active_enrollment'])->group(function () {
            // Student's enrolments using new two-path system
            Route::get('/my-enrolments', [EnrolmentController::class, 'myEnrolments'])->name('students.enrolments');

            // Student grades using new StudentGradeRecord system
            Route::get('/my-grades', [StudentGradeRecordController::class, 'myGrades'])->name('students.grades');

            // Student progress with new architecture - only show current enrolments
            Route::get('/my-progress', function () {
                $student = Auth::user()->student;
                if (! $student) {
                    abort(404, 'Student record not found');
                }

                // No need to load grade records here - the view will use getCurrentGradeRecords()
                return view('students.progress', compact('student'));
            })->name('students.progress');
        });

        // Extension Request routes for students
        Route::get('my-extensions', [ExtensionRequestController::class, 'index'])->name('extension-requests.index');
        Route::get('my-extensions/create', [ExtensionRequestController::class, 'create'])->name('extension-requests.create');
        Route::post('my-extensions', [ExtensionRequestController::class, 'store'])->name('extension-requests.store');
        Route::get('my-extensions/{extensionRequest}', [ExtensionRequestController::class, 'show'])->name('extension-requests.show');
        Route::get('my-extensions/{extensionRequest}/medical-certificate', [ExtensionRequestController::class, 'downloadMedicalCertificate'])->name('extension-requests.medical-certificate');
    });

    // =================================================================
    // MANAGER & STUDENT SERVICES ROUTES - Administrative access
    // =================================================================
    Route::middleware(['role:manager,student_services'])->group(function () {
        // Enquiry routes
        Route::resource('enquiries', EnquiryController::class);
        Route::post('enquiries/{enquiry}/convert', [EnquiryController::class, 'convertToStudent'])->name('enquiries.convert');

        Route::resource('students', StudentController::class);

        // Student recycle bin routes
        Route::get('students-recycle-bin', [StudentController::class, 'recycleBin'])->name('students.recycle-bin');
        Route::patch('students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
        Route::delete('students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force-delete');

        // New Two-Path Enrolment System
        Route::get('students/{student}/enrol', [EnrolmentController::class, 'create'])->name('enrolments.create');
        Route::get('students/{student}/enrol/programme', [EnrolmentController::class, 'createProgramme'])->name('enrolments.create-programme');
        Route::get('students/{student}/enrol/module', [EnrolmentController::class, 'createModule'])->name('enrolments.create-module');
        Route::post('students/{student}/enrol/programme', [EnrolmentController::class, 'storeProgramme'])->name('enrolments.store-programme');
        Route::post('students/{student}/enrol/module', [EnrolmentController::class, 'storeModule'])->name('enrolments.store-module');

        // Enrolment management
        Route::get('enrolments', [EnrolmentController::class, 'index'])->name('enrolments.index');
        Route::get('enrolments/{enrolment}', [EnrolmentController::class, 'show'])->name('enrolments.show');
        Route::patch('enrolments/{enrolment}', [EnrolmentController::class, 'update'])->name('enrolments.update');
        Route::post('enrolments/{enrolment}/withdraw', [EnrolmentController::class, 'withdraw'])->name('enrolments.withdraw');

        // Unenroll functionality (admin error correction)
        Route::get('enrolments/{enrolment}/unenroll', [EnrolmentController::class, 'showUnenrollForm'])->name('enrolments.unenroll-form');
        Route::delete('enrolments/{enrolment}/unenroll', [EnrolmentController::class, 'unenroll'])->name('enrolments.unenroll');

        // Programme Enrolment Deferral System
        Route::get('enrolments/{enrolment}/deferral', [EnrolmentController::class, 'deferralForm'])->name('enrolments.deferral-form');
        Route::post('enrolments/{enrolment}/deferral', [EnrolmentController::class, 'processDeferral'])->name('enrolments.process-deferral');

        // Deferral routes
        Route::get('deferrals', [DeferralController::class, 'index'])->name('deferrals.index');
        Route::get('students/{student}/enrolments/{enrolment}/defer', [DeferralController::class, 'create'])->name('deferrals.create');
        Route::post('students/{student}/enrolments/{enrolment}/defer', [DeferralController::class, 'store'])->name('deferrals.store');
        Route::patch('deferrals/{deferral}/approve', [DeferralController::class, 'approve'])->name('deferrals.approve');
        Route::patch('deferrals/{deferral}/reject', [DeferralController::class, 'reject'])->name('deferrals.reject');
    });

    // =================================================================
    // MANAGER, STUDENT SERVICES, AND TEACHERS - Assessment management
    // =================================================================
    Route::middleware(['role:manager,student_services,teacher'])->group(function () {
        // Extension routes (legacy - keeping for compatibility)
        Route::get('extensions', [ExtensionController::class, 'index'])->name('extensions.index');
        Route::get('students/{student}/extensions/create', [ExtensionController::class, 'create'])->name('extensions.create');
        Route::post('students/{student}/extensions', [ExtensionController::class, 'store'])->name('extensions.store');
        Route::patch('extensions/{extension}/approve', [ExtensionController::class, 'approve'])->name('extensions.approve');
        Route::patch('extensions/{extension}/reject', [ExtensionController::class, 'reject'])->name('extensions.reject');

        // Extension Request routes for staff (reviewing student requests)
        Route::get('extension-requests', [ExtensionRequestController::class, 'index'])->name('extension-requests.staff-index');
        Route::get('extension-requests/{extensionRequest}', [ExtensionRequestController::class, 'show'])->name('extension-requests.staff-show');
        Route::get('extension-requests/{extensionRequest}/review', [ExtensionRequestController::class, 'edit'])->name('extension-requests.review');
        Route::put('extension-requests/{extensionRequest}', [ExtensionRequestController::class, 'update'])->name('extension-requests.update');
        Route::get('extension-requests/{extensionRequest}/medical-certificate', [ExtensionRequestController::class, 'downloadMedicalCertificate'])->name('extension-requests.staff-medical-certificate');

        // Repeat Assessment routes
        Route::get('repeat-assessments', [RepeatAssessmentController::class, 'index'])->name('repeat-assessments.index');
        Route::get('repeat-assessments/create', [RepeatAssessmentController::class, 'create'])->name('repeat-assessments.create');
        Route::get('repeat-assessments/{repeatAssessment}', [RepeatAssessmentController::class, 'show'])->name('repeat-assessments.show');
        Route::get('repeat-assessments/{repeatAssessment}/edit', [RepeatAssessmentController::class, 'edit'])->name('repeat-assessments.edit');
        Route::put('repeat-assessments/{repeatAssessment}', [RepeatAssessmentController::class, 'update'])->name('repeat-assessments.update');
        Route::delete('repeat-assessments/{repeatAssessment}', [RepeatAssessmentController::class, 'destroy'])->name('repeat-assessments.destroy');

        // Specific student routes
        Route::get('students/{student}/repeat-assessments/create', [RepeatAssessmentController::class, 'create'])->name('repeat-assessments.create-for-student');
        Route::post('students/{student}/repeat-assessments', [RepeatAssessmentController::class, 'store'])->name('repeat-assessments.store-for-student');
        Route::post('repeat-assessments', [RepeatAssessmentController::class, 'store'])->name('repeat-assessments.store');

        // Workflow management
        Route::patch('repeat-assessments/{repeatAssessment}/approve', [RepeatAssessmentController::class, 'approve'])->name('repeat-assessments.approve');
        Route::patch('repeat-assessments/{repeatAssessment}/reject', [RepeatAssessmentController::class, 'reject'])->name('repeat-assessments.reject');
        Route::patch('repeat-assessments/{repeatAssessment}/complete', [RepeatAssessmentController::class, 'complete'])->name('repeat-assessments.complete');

        // Payment management
        Route::post('repeat-assessments/{repeatAssessment}/payment', [RepeatAssessmentController::class, 'recordPayment'])->name('repeat-assessments.record-payment');
        Route::patch('repeat-assessments/{repeatAssessment}/waive-payment', [RepeatAssessmentController::class, 'waivePayment'])->name('repeat-assessments.waive-payment');

        // Notification management
        Route::post('repeat-assessments/{repeatAssessment}/notification', [RepeatAssessmentController::class, 'sendNotification'])->name('repeat-assessments.send-notification');

        // Moodle integration
        Route::post('repeat-assessments/{repeatAssessment}/moodle-setup', [RepeatAssessmentController::class, 'setupMoodle'])->name('repeat-assessments.setup-moodle');

        // Bulk operations
        Route::post('repeat-assessments/bulk-action', [RepeatAssessmentController::class, 'bulkAction'])->name('repeat-assessments.bulk-action');

        // Auto-population
        Route::post('repeat-assessments/auto-populate', [RepeatAssessmentController::class, 'autoPopulate'])->name('repeat-assessments.auto-populate');

        // API endpoints
        Route::get('api/students/{student}/failed-assessments', [RepeatAssessmentController::class, 'getFailedAssessments'])->name('api.students.failed-assessments');

        // Legacy Assessment routes (DEPRECATED - Use StudentGradeRecord system above)
        // These routes are kept temporarily for transition period
        Route::get('assessments', [StudentGradeRecordController::class, 'index'])->name('assessments.index');
        Route::get('students/{student}/progress', [StudentController::class, 'progress'])->name('students.show-progress');
    });

    // =================================================================
    // MANAGER-ONLY ROUTES - System administration (New Architecture)
    // =================================================================
    Route::middleware(['role:manager'])->group(function () {
        // Programme Blueprint routes
        Route::resource('programmes', ProgrammeController::class);

        // Programme Instance routes - Live programme containers
        Route::resource('programme-instances', ProgrammeInstanceController::class);

        // Programme Instance curriculum management (curriculum linker)
        Route::get('programme-instances/{programmeInstance}/curriculum', [ProgrammeInstanceController::class, 'curriculum'])->name('programme-instances.curriculum');
        Route::post('programme-instances/{programmeInstance}/curriculum/attach', [ProgrammeInstanceController::class, 'attachModule'])->name('programme-instances.curriculum.attach');
        Route::delete('programme-instances/{programmeInstance}/curriculum/{moduleInstance}', [ProgrammeInstanceController::class, 'detachModule'])->name('programme-instances.curriculum.detach');

        // Module Blueprint routes
        Route::resource('modules', ModuleController::class);

        // Module Instance routes - Live module classes
        Route::resource('module-instances', ModuleInstanceController::class);
        Route::get('module-instances/{moduleInstance}/students', [ModuleInstanceController::class, 'students'])->name('module-instances.students');

        // System Health Dashboard
        Route::get('admin/system-health', [App\Http\Controllers\SystemHealthController::class, 'index'])->name('admin.system-health');
        Route::get('admin/system-health/api', [App\Http\Controllers\SystemHealthController::class, 'api'])->name('admin.system-health.api');
        Route::get('module-instances/{moduleInstance}/grading', [ModuleInstanceController::class, 'grading'])->name('module-instances.grading');
        Route::get('module-instances/{moduleInstance}/copy', [ModuleInstanceController::class, 'copy'])->name('module-instances.copy');
        Route::post('module-instances/{moduleInstance}/copy', [ModuleInstanceController::class, 'storeCopy'])->name('module-instances.store-copy');
        Route::post('module-instances/{moduleInstance}/create-next', [ModuleInstanceController::class, 'createNext'])->name('module-instances.create-next');

        // Student Grade Record routes - New grading system
        Route::get('module-instances/{moduleInstance}/grades', [StudentGradeRecordController::class, 'moduleGrading'])->name('grade-records.module-grading');
        Route::get('module-instances/{moduleInstance}/modern-grading', [StudentGradeRecordController::class, 'modernGrading'])->name('grade-records.modern-grading');
        Route::patch('grade-records/{gradeRecord}', [StudentGradeRecordController::class, 'update'])->name('grade-records.update');
        Route::patch('grade-records/{gradeRecord}/toggle-visibility', [StudentGradeRecordController::class, 'toggleSingleVisibility'])->name('grade-records.toggle-single-visibility');
        Route::post('module-instances/{moduleInstance}/grades/bulk-update', [StudentGradeRecordController::class, 'bulkUpdate'])->name('grade-records.bulk-update');
        Route::post('module-instances/{moduleInstance}/grades/modern-bulk-update', [StudentGradeRecordController::class, 'modernBulkUpdate'])->name('grade-records.modern-bulk-update');
        Route::patch('module-instances/{moduleInstance}/visibility', [StudentGradeRecordController::class, 'toggleVisibility'])->name('grade-records.toggle-visibility');
        Route::patch('module-instances/{moduleInstance}/overall-visibility', [StudentGradeRecordController::class, 'toggleOverallVisibility'])->name('grade-records.toggle-overall-visibility');
        Route::patch('module-instances/{moduleInstance}/student-visibility', [StudentGradeRecordController::class, 'toggleStudentVisibility'])->name('grade-records.toggle-student-visibility');
        Route::patch('module-instances/{moduleInstance}/individual-component-visibility', [StudentGradeRecordController::class, 'toggleIndividualComponentVisibility'])->name('grade-records.toggle-individual-component-visibility');
        Route::patch('module-instances/{moduleInstance}/individual-overall-visibility', [StudentGradeRecordController::class, 'toggleIndividualOverallVisibility'])->name('grade-records.toggle-individual-overall-visibility');
        Route::post('module-instances/{moduleInstance}/bulk-component-visibility', [StudentGradeRecordController::class, 'bulkComponentVisibility'])->name('grade-records.bulk-component-visibility');
        Route::post('module-instances/{moduleInstance}/schedule-release', [StudentGradeRecordController::class, 'scheduleRelease'])->name('grade-records.schedule-release');
        Route::post('module-instances/{moduleInstance}/schedule-component-release', [StudentGradeRecordController::class, 'scheduleComponentRelease'])->name('grade-records.schedule-component-release');
        Route::get('module-instances/{moduleInstance}/export', [StudentGradeRecordController::class, 'export'])->name('grade-records.export');
        Route::get('module-instances/{moduleInstance}/export-component', [StudentGradeRecordController::class, 'exportComponent'])->name('grade-records.export-component');
        Route::get('students/{student}/module-instances/{moduleInstance}/completion', [StudentGradeRecordController::class, 'moduleCompletion'])->name('grade-records.module-completion');

        // System Health & Data Integrity Routes
        Route::prefix('admin/system-health')->name('admin.system-health.')->group(function () {
            Route::get('dashboard', [App\Http\Controllers\Admin\ArchitectureController::class, 'dashboard'])->name('dashboard');
            Route::get('validation', [App\Http\Controllers\Admin\ArchitectureController::class, 'validation'])->name('validation');
            Route::post('auto-fix', [App\Http\Controllers\Admin\ArchitectureController::class, 'autoFix'])->name('auto-fix');
            Route::get('statistics', [App\Http\Controllers\Admin\ArchitectureController::class, 'statistics'])->name('statistics');
            Route::get('curriculum/{programmeInstance}/validate', [App\Http\Controllers\Admin\ArchitectureController::class, 'validateCurriculum'])->name('validate-curriculum');
            Route::get('export-report', [App\Http\Controllers\Admin\ArchitectureController::class, 'exportReport'])->name('export-report');
            Route::post('run-validation', [App\Http\Controllers\Admin\ArchitectureController::class, 'runValidation'])->name('run-validation');
        });

        // Reporting routes
        Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
        Route::get('reports/programme-instances/{programmeInstance}/students', [ReportController::class, 'programmeInstanceList'])->name('reports.programme-instance-list');
        Route::get('reports/students/{student}/progress', [ReportController::class, 'studentProgress'])->name('reports.student-progress');

        // Analytics API routes
        Route::prefix('api/analytics')->name('analytics.')->group(function () {
            Route::get('system-overview', [AnalyticsController::class, 'systemOverview'])->name('system-overview');
            Route::get('student-performance', [AnalyticsController::class, 'studentPerformance'])->name('student-performance');
            Route::get('programme-effectiveness', [AnalyticsController::class, 'programmeEffectiveness'])->name('programme-effectiveness');
            Route::get('assessment-completion', [AnalyticsController::class, 'assessmentCompletion'])->name('assessment-completion');
            Route::get('student-engagement', [AnalyticsController::class, 'studentEngagement'])->name('student-engagement');
            Route::get('chart-data/{type}', [AnalyticsController::class, 'chartData'])->name('chart-data');
            Route::get('historical-metrics', [AnalyticsController::class, 'historicalMetrics'])->name('historical-metrics');
            Route::post('refresh-cache', [AnalyticsController::class, 'refreshCache'])->name('refresh-cache');
            Route::post('clear-expired-cache', [AnalyticsController::class, 'clearExpiredCache'])->name('clear-expired-cache');
        });
    });

    // =================================================================
    // TRANSCRIPT ROUTES - Authenticated users with permissions
    // =================================================================
    Route::get('students/{student}/transcript/download', [TranscriptController::class, 'download'])->name('transcripts.download');
    Route::middleware(['role:manager,student_services,teacher'])->group(function () {
        Route::get('students/{student}/transcript/preview', [TranscriptController::class, 'preview'])->name('transcripts.preview');
    });

    // =================================================================
    // EMAIL TEMPLATE ROUTES - Manager and Student Services only
    // =================================================================
    Route::middleware(['role:manager,student_services'])->group(function () {
        Route::resource('admin/email-templates', EmailTemplateController::class, [
            'as' => 'admin',
            'names' => [
                'index' => 'admin.email-templates.index',
                'create' => 'admin.email-templates.create',
                'store' => 'admin.email-templates.store',
                'show' => 'admin.email-templates.show',
                'edit' => 'admin.email-templates.edit',
                'update' => 'admin.email-templates.update',
                'destroy' => 'admin.email-templates.destroy',
            ],
        ]);
        Route::get('admin/email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])->name('admin.email-templates.preview');
        Route::post('admin/email-templates/{emailTemplate}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('admin.email-templates.duplicate');
    });

    // =================================================================
    // STUDENT EMAIL ROUTES - Manager, Student Services, and Teachers
    // =================================================================
    Route::middleware(['role:manager,student_services,teacher'])->group(function () {
        Route::get('students/{student}/emails', [StudentEmailController::class, 'index'])->name('student-emails.index');
        Route::get('students/{student}/emails/compose', [StudentEmailController::class, 'compose'])->name('student-emails.compose');
        Route::post('students/{student}/emails/preview', [StudentEmailController::class, 'preview'])->name('student-emails.preview');
        Route::post('students/{student}/emails/send', [StudentEmailController::class, 'send'])->name('student-emails.send');
        Route::post('students/{student}/emails/quick-send', [StudentEmailController::class, 'quickSend'])->name('student-emails.quick-send');
    });

    // =================================================================
    // NOTIFICATION ROUTES - All authenticated users
    // =================================================================
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Manager-only notification routes
    Route::middleware(['role:manager'])->group(function () {
        Route::get('admin/notifications', [NotificationController::class, 'adminDashboard'])->name('notifications.admin');
        Route::match(['get', 'post'], 'admin/notifications/announcement', [NotificationController::class, 'createAnnouncement'])->name('notifications.announcement');
    });

    // =================================================================
    // MOODLE INTEGRATION ROUTES - Manager and Student Services
    // =================================================================
    Route::middleware(['role:manager,student_services'])->group(function () {
        Route::get('admin/moodle', [App\Http\Controllers\Admin\MoodleController::class, 'index'])->name('moodle.index');
        Route::get('admin/moodle/test-connection', [App\Http\Controllers\Admin\MoodleController::class, 'testConnection'])->name('moodle.test-connection');
        Route::post('admin/moodle/sync-all-courses', [App\Http\Controllers\Admin\MoodleController::class, 'syncAllCourses'])->name('moodle.sync-all-courses');

        // Course management
        Route::post('admin/moodle/courses/{moduleInstance}/create', [App\Http\Controllers\Admin\MoodleController::class, 'createCourse'])->name('moodle.create-course');
        Route::get('admin/moodle/courses/{moduleInstance}', [App\Http\Controllers\Admin\MoodleController::class, 'showCourse'])->name('moodle.show-course');

        // Student enrollment
        Route::post('admin/moodle/courses/{moduleInstance}/enroll/{student}', [App\Http\Controllers\Admin\MoodleController::class, 'enrollStudent'])->name('moodle.enroll-student');
        Route::post('admin/moodle/courses/{moduleInstance}/bulk-enroll', [App\Http\Controllers\Admin\MoodleController::class, 'bulkEnrollCohort'])->name('moodle.bulk-enroll');
    });

    // =================================================================
    // EMAIL API ROUTES - Graph API Integration
    // =================================================================
    
    // Email summary for dashboard widget
    Route::get('/api/email-summary', [App\Http\Controllers\Api\EmailController::class, 'summary'])
        ->name('api.email.summary');
    
    Route::post('/api/email/refresh', [App\Http\Controllers\Api\EmailController::class, 'refresh'])
        ->name('api.email.refresh');
    
    // Manager/admin only routes
    Route::middleware(['role:manager,student_services'])->group(function () {
        Route::get('/api/email/health', [App\Http\Controllers\Api\EmailController::class, 'health'])
            ->name('api.email.health');
    });

    // =================================================================
    // STUDENT DOCUMENT MANAGEMENT ROUTES
    // =================================================================

    // Student document management
    Route::get('/students/{student}/documents', [App\Http\Controllers\StudentDocumentController::class, 'index'])
        ->name('students.documents.index');

    Route::get('/students/{student}/documents/create', [App\Http\Controllers\StudentDocumentController::class, 'create'])
        ->name('students.documents.create');

    Route::post('/students/{student}/documents', [App\Http\Controllers\StudentDocumentController::class, 'store'])
        ->name('students.documents.store');

    Route::get('/student-documents/{document}/download', [App\Http\Controllers\StudentDocumentController::class, 'download'])
        ->name('student-documents.download');

    Route::get('/student-documents/{document}/view', [App\Http\Controllers\StudentDocumentController::class, 'view'])
        ->name('student-documents.view');

    Route::delete('/student-documents/{document}', [App\Http\Controllers\StudentDocumentController::class, 'destroy'])
        ->name('student-documents.destroy');

    // Staff-only document verification routes
    Route::post('/student-documents/{document}/verify', [App\Http\Controllers\StudentDocumentController::class, 'verify'])
        ->name('student-documents.verify');

    Route::post('/student-documents/{document}/reject', [App\Http\Controllers\StudentDocumentController::class, 'reject'])
        ->name('student-documents.reject');

    // Student's own document routes (simplified paths)
    Route::get('/my-documents', function () {
        $student = auth()->user()->student;
        if (! $student) {
            abort(404, 'Student profile not found');
        }

        return redirect()->route('students.documents.index', $student);
    })->name('my-documents');

});
