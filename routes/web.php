<?php

use App\Http\Controllers\Auth\AzureController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\EnrolmentController;
use App\Http\Controllers\DeferralController;
use App\Http\Controllers\ModuleInstanceController;

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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Student routes
    Route::resource('students', StudentController::class);
        // Programme routes
    Route::resource('programmes', ProgrammeController::class);
        Route::resource('cohorts', CohortController::class);
            // Module routes
    Route::resource('modules', ModuleController::class);
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
    Route::patch('module-instances/{instance}/assign-teacher', [ModuleInstanceController::class, 'assignTeacher'])->name('module-instances.assign-teacher');
});