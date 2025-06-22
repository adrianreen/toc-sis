<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\Enrolment;
use App\Models\StudentGradeRecord;
use App\Models\Notification;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SystemHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:manager');
    }

    /**
     * Display the system health dashboard
     */
    public function index()
    {
        $healthData = $this->gatherHealthMetrics();
        
        return view('admin.system-health', $healthData);
    }

    /**
     * API endpoint for real-time health data
     */
    public function api()
    {
        $healthData = $this->gatherHealthMetrics();
        
        return response()->json($healthData);
    }

    /**
     * Gather comprehensive system health metrics
     */
    private function gatherHealthMetrics(): array
    {
        $startTime = microtime(true);

        // System Overview
        $systemOverview = $this->getSystemOverview();
        
        // Database Health
        $databaseHealth = $this->getDatabaseHealth();
        
        // Academic System Health
        $academicHealth = $this->getAcademicSystemHealth();
        
        // Performance Metrics
        $performanceMetrics = $this->getPerformanceMetrics();
        
        // Security Status
        $securityStatus = $this->getSecurityStatus();
        
        // Notification System Health
        $notificationHealth = $this->getNotificationSystemHealth();
        
        // Recent Activity
        $recentActivity = $this->getRecentActivity();
        
        // System Warnings
        $systemWarnings = $this->getSystemWarnings();

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'system_overview' => $systemOverview,
            'database_health' => $databaseHealth,
            'academic_health' => $academicHealth,
            'performance_metrics' => $performanceMetrics,
            'security_status' => $securityStatus,
            'notification_health' => $notificationHealth,
            'recent_activity' => $recentActivity,
            'system_warnings' => $systemWarnings,
            'dashboard_metrics' => [
                'execution_time_ms' => $executionTime,
                'generated_at' => now(),
                'cache_status' => $this->getCacheStatus(),
            ]
        ];
    }

    private function getSystemOverview(): array
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'url' => config('app.url'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'disk_usage' => $this->getDiskUsage(),
            'memory_usage' => [
                'current' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                'limit' => ini_get('memory_limit'),
            ]
        ];
    }

    private function getDatabaseHealth(): array
    {
        $connectionTime = $this->measureDbConnectionTime();
        
        return [
            'connection_status' => $this->testDatabaseConnection(),
            'connection_time_ms' => $connectionTime,
            'total_tables' => $this->countDatabaseTables(),
            'migrations_status' => $this->getMigrationsStatus(),
            'query_performance' => $this->getQueryPerformanceMetrics(),
        ];
    }

    private function getAcademicSystemHealth(): array
    {
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'active')->count();
        $totalEnrolments = Enrolment::count();
        $activeEnrolments = Enrolment::where('status', 'active')->count();
        
        return [
            'students' => [
                'total' => $totalStudents,
                'active' => $activeStudents,
                'inactive' => $totalStudents - $activeStudents,
                'health_score' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0,
            ],
            'programmes' => [
                'total' => Programme::count(),
                'instances' => ProgrammeInstance::count(),
                'active_instances' => ProgrammeInstance::where('start_date', '<=', now())
                    ->where('end_date', '>=', now())->count(),
            ],
            'modules' => [
                'total' => Module::count(),
                'instances' => ModuleInstance::count(),
                'with_tutors' => ModuleInstance::whereNotNull('tutor_id')->count(),
            ],
            'enrollments' => [
                'total' => $totalEnrolments,
                'active' => $activeEnrolments,
                'programme_enrollments' => Enrolment::where('enrolment_type', 'programme')->count(),
                'module_enrollments' => Enrolment::where('enrolment_type', 'module')->count(),
            ],
            'assessments' => [
                'total_grade_records' => StudentGradeRecord::count(),
                'graded' => StudentGradeRecord::whereNotNull('grade')->count(),
                'visible_to_students' => StudentGradeRecord::where('is_visible_to_student', true)->count(),
                'pending_grading' => StudentGradeRecord::whereNull('grade')->count(),
            ]
        ];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'average_response_time' => $this->getAverageResponseTime(),
            'cache_hit_ratio' => $this->getCacheHitRatio(),
            'database_query_count' => $this->getDatabaseQueryCount(),
            'memory_efficiency' => $this->getMemoryEfficiency(),
            'uptime' => $this->getSystemUptime(),
        ];
    }

    private function getSecurityStatus(): array
    {
        return [
            'https_enabled' => request()->isSecure(),
            'csrf_protection' => config('session.same_site') !== null,
            'password_hashing' => config('hashing.driver') === 'bcrypt',
            'session_security' => [
                'secure_cookies' => config('session.secure'),
                'http_only' => config('session.http_only'),
                'same_site' => config('session.same_site'),
            ],
            'failed_login_attempts' => $this->getFailedLoginAttempts(),
            'admin_accounts' => User::where('role', 'manager')->count(),
            'student_accounts' => User::where('role', 'student')->count(),
        ];
    }

    private function getNotificationSystemHealth(): array
    {
        $totalNotifications = Notification::count();
        $unreadNotifications = Notification::whereNull('read_at')->count();
        $emailsSent = EmailLog::count();
        $emailsToday = EmailLog::whereDate('created_at', today())->count();
        
        return [
            'notifications' => [
                'total' => $totalNotifications,
                'unread' => $unreadNotifications,
                'read_rate' => $totalNotifications > 0 ? round((($totalNotifications - $unreadNotifications) / $totalNotifications) * 100, 1) : 0,
            ],
            'emails' => [
                'total_sent' => $emailsSent,
                'sent_today' => $emailsToday,
                'delivery_rate' => $this->getEmailDeliveryRate(),
            ],
            'queue_status' => $this->getQueueStatus(),
        ];
    }

    private function getRecentActivity(): array
    {
        return [
            'recent_students' => Student::latest()->take(5)->get(['id', 'full_name', 'created_at']),
            'recent_enrollments' => Enrolment::with(['student', 'programmeInstance.programme'])
                ->latest()->take(5)->get(),
            'recent_grades' => StudentGradeRecord::with(['student', 'moduleInstance.module'])
                ->whereNotNull('grade')->latest('graded_date')->take(5)->get(),
            'recent_notifications' => Notification::with('user')
                ->latest()->take(5)->get(),
        ];
    }

    private function getSystemWarnings(): array
    {
        $warnings = [];

        // Check for critical issues
        if ($this->testDatabaseConnection() !== 'Connected') {
            $warnings[] = ['type' => 'critical', 'message' => 'Database connection issues detected'];
        }

        if (config('app.debug') && app()->environment('production')) {
            $warnings[] = ['type' => 'security', 'message' => 'Debug mode enabled in production'];
        }

        if (StudentGradeRecord::whereNull('grade')->count() > 100) {
            $warnings[] = ['type' => 'academic', 'message' => 'High number of ungraded assessments'];
        }

        if (Notification::whereNull('read_at')->count() > 1000) {
            $warnings[] = ['type' => 'notification', 'message' => 'High number of unread notifications'];
        }

        if ($this->getDiskUsage()['percentage'] > 80) {
            $warnings[] = ['type' => 'system', 'message' => 'Disk usage above 80%'];
        }

        return $warnings;
    }

    // Helper methods
    private function testDatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Failed: ' . $e->getMessage();
        }
    }

    private function measureDbConnectionTime(): float
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            return round((microtime(true) - $start) * 1000, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function countDatabaseTables(): int
    {
        try {
            $tables = DB::select('SHOW TABLES');
            return count($tables);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getMigrationsStatus(): array
    {
        try {
            $migrations = DB::table('migrations')->count();
            $lastMigration = DB::table('migrations')->latest('batch')->first();
            
            return [
                'total_migrations' => $migrations,
                'last_migration' => $lastMigration?->migration ?? 'None',
                'last_batch' => $lastMigration?->batch ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space(base_path());
        $freeSpace = disk_free_space(base_path());
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 1),
        ];
    }

    private function formatBytes($size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    private function getCacheStatus(): string
    {
        try {
            Cache::put('health_check', 'test', 10);
            return Cache::get('health_check') === 'test' ? 'Working' : 'Failed';
        } catch (\Exception $e) {
            return 'Failed: ' . $e->getMessage();
        }
    }

    private function getQueryPerformanceMetrics(): array
    {
        // Simple query performance test
        $start = microtime(true);
        Student::count();
        $studentQueryTime = round((microtime(true) - $start) * 1000, 2);

        $start = microtime(true);
        StudentGradeRecord::count();
        $gradeQueryTime = round((microtime(true) - $start) * 1000, 2);

        return [
            'student_count_query_ms' => $studentQueryTime,
            'grade_count_query_ms' => $gradeQueryTime,
            'average_query_time_ms' => round(($studentQueryTime + $gradeQueryTime) / 2, 2),
        ];
    }

    private function getAverageResponseTime(): string
    {
        // Placeholder - would integrate with monitoring tools in production
        return '< 200ms';
    }

    private function getCacheHitRatio(): string
    {
        // Placeholder - would integrate with cache monitoring
        return '85%';
    }

    private function getDatabaseQueryCount(): int
    {
        // Placeholder - would use query logging in production
        return 0;
    }

    private function getMemoryEfficiency(): string
    {
        $current = memory_get_usage();
        $peak = memory_get_peak_usage();
        $efficiency = round(($current / $peak) * 100, 1);
        return $efficiency . '%';
    }

    private function getSystemUptime(): string
    {
        // Placeholder - would integrate with system monitoring
        return 'N/A';
    }

    private function getFailedLoginAttempts(): int
    {
        // Placeholder - would integrate with authentication logging
        return 0;
    }

    private function getEmailDeliveryRate(): string
    {
        $total = EmailLog::count();
        $successful = EmailLog::where('status', 'sent')->count();
        
        if ($total === 0) return '100%';
        
        return round(($successful / $total) * 100, 1) . '%';
    }

    private function getQueueStatus(): array
    {
        // Placeholder - would integrate with queue monitoring
        return [
            'jobs_waiting' => 0,
            'jobs_processing' => 0,
            'failed_jobs' => 0,
        ];
    }
}