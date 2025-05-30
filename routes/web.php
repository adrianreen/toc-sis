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
use App\Http\Controllers\DevRoleController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Azure AD authentication
Route::get('/login', [AzureController::class, 'redirect'])->name('login');
Route::get('/callback', [AzureController::class, 'callback']);
Route::post('/logout', [AzureController::class, 'logout'])->name('logout');

Route::get('/my-groups', function() {
    $user = Auth::user();
    return response()->json([
        'email' => $user->email,
        'role' => $user->role, 
        'groups' => $user->azure_groups,
        'group_count' => count($user->azure_groups ?? [])
    ]);
})->middleware('auth');
// Protected routes
Route::middleware(['auth'])->group(function () {
    // Add this line in the protected routes section (after Route::middleware(['auth'])->group(function () {)
// Add these to routes/web.php in the protected section:
Route::get('/debug-groups', [AzureController::class, 'debugGroups']);
Route::get('/refresh-groups', [AzureController::class, 'refreshAndGetGroups']);
Route::get('/test-groups-simple', [AzureController::class, 'testGroupsSimple']);
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Student routes
    Route::resource('students', StudentController::class);
    
    // Future: Server-side search endpoint (uncomment when needed for performance)
    // Route::get('students-search', [StudentController::class, 'search'])->name('students.search');
    
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
    
    // Module Instance routes
    Route::resource('module-instances', ModuleInstanceController::class);
    Route::patch('module-instances/{moduleInstance}/assign-teacher', [ModuleInstanceController::class, 'assignTeacher'])->name('module-instances.assign-teacher');

    // Extension routes
    Route::get('extensions', [ExtensionController::class, 'index'])->name('extensions.index');
    Route::get('students/{student}/extensions/create', [ExtensionController::class, 'create'])->name('extensions.create');
    Route::post('students/{student}/extensions', [ExtensionController::class, 'store'])->name('extensions.store');
    Route::patch('extensions/{extension}/approve', [ExtensionController::class, 'approve'])->name('extensions.approve');
    Route::patch('extensions/{extension}/reject', [ExtensionController::class, 'reject'])->name('extensions.reject');
    
    // Repeat Assessment routes
    Route::get('repeat-assessments', [RepeatAssessmentController::class, 'index'])->name('repeat-assessments.index');
    Route::get('students/{student}/repeat-assessments/create', [RepeatAssessmentController::class, 'create'])->name('repeat-assessments.create');
    Route::post('students/{student}/repeat-assessments', [RepeatAssessmentController::class, 'store'])->name('repeat-assessments.store');
    Route::patch('repeat-assessments/{repeatAssessment}/approve', [RepeatAssessmentController::class, 'approve'])->name('repeat-assessments.approve');
    
    // Reporting routes
    Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('reports/cohorts/{cohort}/students', [ReportController::class, 'cohortList'])->name('reports.cohort-list');
    Route::get('reports/students/{student}/progress', [ReportController::class, 'studentProgress'])->name('reports.student-progress');

    // Student Assessment routes

    Route::get('assessments', [StudentAssessmentController::class, 'index'])->name('assessments.index');
Route::get('assessments/pending', [StudentAssessmentController::class, 'pending'])->name('assessments.pending');
Route::get('assessments/module-instances/{moduleInstance}', [StudentAssessmentController::class, 'moduleInstance'])->name('assessments.module-instance');
Route::get('assessments/{studentAssessment}/grade', [StudentAssessmentController::class, 'grade'])->name('assessments.grade');
Route::put('assessments/{studentAssessment}/grade', [StudentAssessmentController::class, 'storeGrade'])->name('assessments.store-grade');
Route::patch('assessments/{studentAssessment}/submit', [StudentAssessmentController::class, 'markSubmitted'])->name('assessments.mark-submitted');
Route::get('assessments/module-instances/{moduleInstance}/components/{assessmentComponent}/bulk-grade', [StudentAssessmentController::class, 'bulkGradeForm'])->name('assessments.bulk-grade-form');
Route::post('assessments/module-instances/{moduleInstance}/components/{assessmentComponent}/bulk-grade', [StudentAssessmentController::class, 'storeBulkGrades'])->name('assessments.bulk-grade');
Route::get('assessments/students/{student}/progress', [StudentAssessmentController::class, 'studentProgress'])->name('assessments.student-progress');
Route::get('assessments/module-instances/{moduleInstance}/export', [StudentAssessmentController::class, 'exportGrades'])->name('assessments.export');



// ... your other routes

// WARNING: DEVELOPMENT ONLY ROUTE - REMOVE BEFORE DEPLOYMENT
Route::middleware(['auth'])->group(function () { // Ensure user is authenticated
    Route::post('/dev/super-secret-role-switcher-path', [DevRoleController::class, 'switchRole'])->name('dev.switch-role');
});
// Consider using a less obvious path if you're concerned about it being guessed on a staging server,
// though the real solution is removing it.
                                                                      
});