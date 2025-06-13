<?php

use App\Http\Controllers\Auth\AzureController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\EnrolmentController;
use App\Http\Controllers\DeferralController;
use App\Http\Controllers\ModuleInstanceController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\RepeatAssessmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AssessmentComponentController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExtensionRequestController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\StudentEmailController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
            if (!Auth::user()->student) {
                return view('dashboard-student-error', [
                    'user' => Auth::user(),
                    'message' => 'No student record found. Please contact Student Services.'
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
    // STUDENT-ONLY ROUTES - Simplified, direct access
    // =================================================================
    Route::middleware(['role:student'])->group(function () {
        
        Route::get('/my-enrolments', function () {
            $student = Auth::user()->student;
            if (!$student) {
                abort(404, 'Student record not found');
            }
            $enrolments = $student->enrolments()->with(['programme', 'cohort'])->get();
            return view('students.enrolments', compact('student', 'enrolments'));
        })->name('students.enrolments');
        
        Route::get('/my-profile', function () {
            $student = Auth::user()->student;
            if (!$student) {
                abort(404, 'Student record not found');
            }
            $student->load(['enrolments.programme', 'enrolments.cohort']);
            return view('students.profile', compact('student')); // Simple student view
        })->name('students.profile');

Route::get('/my-progress', function () {
    $student = Auth::user()->student;
    if (!$student) {
        abort(404, 'Student record not found');
    }
    
    // Load basic progress data with visibility filtering
    $student->load([
        'studentModuleEnrolments.moduleInstance.module',
        'studentModuleEnrolments.studentAssessments' => function($query) {
            // Show all assessments but filter results by visibility in the view
            $query->with('assessmentComponent');
        }
    ]);
    
    return view('students.progress', compact('student'));
})->name('students.progress');
        
      Route::get('/my-assessments', function () {
    $student = Auth::user()->student;
    if (!$student) {
        abort(404, 'Student record not found');
    }
    
    // Get upcoming and recent assessments - ONLY VISIBLE ONES for results
    $allAssessments = $student->studentModuleEnrolments->flatMap->studentAssessments;
    
    // Upcoming assessments (always show these regardless of results visibility)
    $upcomingAssessments = $allAssessments->filter(function($assessment) {
        return $assessment->status === 'pending' && $assessment->due_date >= now();
    })->sortBy('due_date')->take(5);
    
    // Recent assessments - ONLY show results if visible to student
    $recentAssessments = $allAssessments->filter(function($assessment) {
        return in_array($assessment->status, ['graded', 'passed', 'failed']) 
               && $assessment->isVisibleToStudent(); // KEY CHANGE: Only visible results
    })->sortByDesc('graded_date')->take(10);
    
    // Overdue assessments (always show)
    $overdueAssessments = $allAssessments->filter(function($assessment) {
        return $assessment->status === 'pending' && $assessment->due_date < now();
    })->sortBy('due_date');
    
    // Submitted awaiting grading (always show)
    $awaitingGrading = $allAssessments->filter(function($assessment) {
        return $assessment->status === 'submitted';
    })->sortByDesc('submission_date');
    
    return view('students.assessments', compact(
        'student', 
        'upcomingAssessments', 
        'recentAssessments',
        'overdueAssessments',
        'awaitingGrading'
    ));
})->name('students.assessments');
        
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
        
        // Enrolment routes
        Route::get('students/{student}/enrol', [EnrolmentController::class, 'create'])->name('enrolments.create');
        Route::post('students/{student}/enrol', [EnrolmentController::class, 'store'])->name('enrolments.store');
        Route::patch('students/{student}/enrolments/{enrolment}/status', [EnrolmentController::class, 'updateStatus'])->name('enrolments.update-status');
        
        // Deferral routes
        Route::get('deferrals', [DeferralController::class, 'index'])->name('deferrals.index');
        Route::get('students/{student}/enrolments/{enrolment}/defer', [DeferralController::class, 'create'])->name('deferrals.create');
        Route::post('students/{student}/enrolments/{enrolment}/defer', [DeferralController::class, 'store'])->name('deferrals.store');
        Route::patch('deferrals/{deferral}/approve', [DeferralController::class, 'approve'])->name('deferrals.approve');
        Route::patch('deferrals/{deferral}/process-return', [DeferralController::class, 'processReturn'])->name('deferrals.process-return');
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
        Route::get('students/{student}/repeat-assessments/create', [RepeatAssessmentController::class, 'create'])->name('repeat-assessments.create');
        Route::post('students/{student}/repeat-assessments', [RepeatAssessmentController::class, 'store'])->name('repeat-assessments.store');
        Route::patch('repeat-assessments/{repeatAssessment}/approve', [RepeatAssessmentController::class, 'approve'])->name('repeat-assessments.approve');

        // Student Assessment routes
        Route::get('assessments', [StudentAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('assessments/pending', [StudentAssessmentController::class, 'pending'])->name('assessments.pending');
        Route::get('assessments/module-instances/{moduleInstance}', [StudentAssessmentController::class, 'moduleInstance'])->name('assessments.module-instance');
        Route::get('assessments/{studentAssessment}/grade', [StudentAssessmentController::class, 'grade'])->name('assessments.grade');
        Route::put('assessments/{studentAssessment}/grade', [StudentAssessmentController::class, 'storeGrade'])->name('assessments.store-grade');

        Route::patch('assessments/{studentAssessment}/submit', [StudentAssessmentController::class, 'markSubmitted'])->name('assessments.mark-submitted');
        Route::get('assessments/module-instances/{moduleInstance}/components/{assessmentComponent}/bulk-grade', [StudentAssessmentController::class, 'bulkGradeForm'])->name('assessments.bulk-grade-form');
        Route::post('assessments/module-instances/{moduleInstance}/components/{assessmentComponent}/bulk-grade', [StudentAssessmentController::class, 'storeBulkGrades'])->name('assessments.bulk-grade');
            // Visibility control routes
    Route::patch('assessments/{studentAssessment}/quick-visibility', [StudentAssessmentController::class, 'quickVisibility'])->name('assessments.quick-visibility');
    Route::post('assessments/module-instances/{moduleInstance}/components/{assessmentComponent}/bulk-visibility', [StudentAssessmentController::class, 'bulkVisibility'])->name('assessments.bulk-visibility');

        // Admin view of student progress (separate from student's own view)
        Route::get('admin/students/{student}/progress', [StudentAssessmentController::class, 'studentProgress'])->name('admin.student-progress');
        Route::get('assessments/module-instances/{moduleInstance}/export', [StudentAssessmentController::class, 'exportGrades'])->name('assessments.export');
    });
    
    // =================================================================
    // MANAGER-ONLY ROUTES - System administration
    // =================================================================
    Route::middleware(['role:manager'])->group(function () {
        // Programme routes
        Route::resource('programmes', ProgrammeController::class);
        
        // Cohort routes
        Route::resource('cohorts', CohortController::class);
        
        // Module routes
        Route::resource('modules', ModuleController::class);
        
        // Assessment Component routes (nested under modules)
        Route::get('modules/{module}/assessment-components', [AssessmentComponentController::class, 'index'])->name('assessment-components.index');
        Route::get('modules/{module}/assessment-components/create', [AssessmentComponentController::class, 'create'])->name('assessment-components.create');
        Route::post('modules/{module}/assessment-components', [AssessmentComponentController::class, 'store'])->name('assessment-components.store');
        Route::get('modules/{module}/assessment-components/{assessmentComponent}/edit', [AssessmentComponentController::class, 'edit'])->name('assessment-components.edit');
        Route::put('modules/{module}/assessment-components/{assessmentComponent}', [AssessmentComponentController::class, 'update'])->name('assessment-components.update');
        Route::delete('modules/{module}/assessment-components/{assessmentComponent}', [AssessmentComponentController::class, 'destroy'])->name('assessment-components.destroy');
        Route::post('modules/{module}/assessment-components/reorder', [AssessmentComponentController::class, 'reorder'])->name('assessment-components.reorder');
            // Admin visibility management
    Route::get('assessments/scheduled-releases', [StudentAssessmentController::class, 'scheduledReleases'])->name('assessments.scheduled-releases');
    Route::post('assessments/process-scheduled-releases', [StudentAssessmentController::class, 'processScheduledReleases'])->name('assessments.process-scheduled-releases');

        // Module Instance routes
        Route::resource('module-instances', ModuleInstanceController::class);
        Route::patch('module-instances/{moduleInstance}/assign-teacher', [ModuleInstanceController::class, 'assignTeacher'])->name('module-instances.assign-teacher');

        // Reporting routes
        Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
        Route::get('reports/cohorts/{cohort}/students', [ReportController::class, 'cohortList'])->name('reports.cohort-list');
        Route::get('reports/students/{student}/progress', [ReportController::class, 'studentProgress'])->name('reports.student-progress');
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
            ]
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

});