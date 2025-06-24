<?php

namespace App\Console\Commands;

use App\Models\Enrolment;
use App\Models\ModuleInstance;
use App\Models\Student;
use App\Models\StudentGradeRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PerformanceMonitor extends Command
{
    protected $signature = 'performance:monitor 
                          {--output=console : Output format (console, file, json)}
                          {--benchmark : Run performance benchmarks}
                          {--queries : Monitor slow queries}
                          {--memory : Monitor memory usage}
                          {--all : Run all monitoring checks}';

    protected $description = 'Monitor TOC-SIS system performance and generate reports';

    private $results = [];

    private $startTime;

    private $startMemory;

    public function handle()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);

        $this->info('TOC-SIS Performance Monitoring');
        $this->info('================================');

        if ($this->option('all')) {
            $this->runAllChecks();
        } else {
            if ($this->option('benchmark')) {
                $this->runBenchmarks();
            }
            if ($this->option('queries')) {
                $this->monitorQueries();
            }
            if ($this->option('memory')) {
                $this->monitorMemory();
            }
            if (! $this->option('benchmark') && ! $this->option('queries') && ! $this->option('memory')) {
                $this->runAllChecks();
            }
        }

        $this->generateReport();
    }

    private function runAllChecks()
    {
        $this->runBenchmarks();
        $this->monitorQueries();
        $this->monitorMemory();
        $this->checkDatabaseHealth();
        $this->checkSystemResources();
    }

    private function runBenchmarks()
    {
        $this->info('Running performance benchmarks...');

        // Database query benchmarks
        $this->results['benchmarks'] = [
            'dashboard_load' => $this->benchmarkDashboardLoad(),
            'student_query' => $this->benchmarkStudentQueries(),
            'enrolment_processing' => $this->benchmarkEnrolmentProcessing(),
            'grade_record_operations' => $this->benchmarkGradeRecordOperations(),
            'complex_relationships' => $this->benchmarkComplexRelationships(),
        ];
    }

    private function benchmarkDashboardLoad()
    {
        $this->line('- Dashboard load performance...');

        $start = microtime(true);

        // Simulate dashboard data loading
        $students = Student::with(['enrolments.programmeInstance', 'gradeRecords'])
            ->limit(10)
            ->get();

        $end = microtime(true);
        $duration = ($end - $start) * 1000; // Convert to milliseconds

        $status = $duration < 3000 ? 'PASS' : 'WARN';
        if ($duration > 5000) {
            $status = 'FAIL';
        }

        return [
            'duration_ms' => round($duration, 2),
            'status' => $status,
            'threshold_ms' => 3000,
            'records_loaded' => $students->count(),
        ];
    }

    private function benchmarkStudentQueries()
    {
        $this->line('- Student query performance...');

        $tests = [];

        // Test 1: Simple student count
        $start = microtime(true);
        $count = Student::count();
        $tests['student_count'] = [
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'result' => $count,
        ];

        // Test 2: Student with relationships
        $start = microtime(true);
        $students = Student::with(['enrolments', 'gradeRecords'])->limit(50)->get();
        $tests['students_with_relationships'] = [
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'result' => $students->count(),
        ];

        // Test 3: Complex student search
        $start = microtime(true);
        $searchResults = Student::where('status', 'active')
            ->whereHas('enrolments', function ($query) {
                $query->where('status', 'active');
            })
            ->with(['enrolments.programmeInstance.programme'])
            ->limit(25)
            ->get();
        $tests['complex_search'] = [
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'result' => $searchResults->count(),
        ];

        return $tests;
    }

    private function benchmarkEnrolmentProcessing()
    {
        $this->line('- Enrolment processing performance...');

        $start = microtime(true);

        // Simulate enrolment service operations
        $enrolments = Enrolment::with(['student', 'programmeInstance', 'moduleInstance'])
            ->limit(100)
            ->get();

        $duration = (microtime(true) - $start) * 1000;

        return [
            'duration_ms' => round($duration, 2),
            'enrolments_processed' => $enrolments->count(),
            'avg_per_enrolment_ms' => $enrolments->count() > 0 ? round($duration / $enrolments->count(), 2) : 0,
        ];
    }

    private function benchmarkGradeRecordOperations()
    {
        $this->line('- Grade record operations...');

        $tests = [];

        // Test grade record retrieval
        $start = microtime(true);
        $grades = StudentGradeRecord::with(['student', 'moduleInstance'])
            ->where('is_visible_to_student', true)
            ->limit(200)
            ->get();
        $tests['grade_retrieval'] = [
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'records' => $grades->count(),
        ];

        // Test grade aggregation
        $start = microtime(true);
        $avgGrades = StudentGradeRecord::select('student_id', DB::raw('AVG(grade) as avg_grade'))
            ->groupBy('student_id')
            ->limit(50)
            ->get();
        $tests['grade_aggregation'] = [
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'students' => $avgGrades->count(),
        ];

        return $tests;
    }

    private function benchmarkComplexRelationships()
    {
        $this->line('- Complex relationship queries...');

        $start = microtime(true);

        // Complex query with multiple joins and conditions
        $complexQuery = Student::select('students.*')
            ->join('enrolments', 'students.id', '=', 'enrolments.student_id')
            ->join('programme_instances', 'enrolments.programme_instance_id', '=', 'programme_instances.id')
            ->join('programmes', 'programme_instances.programme_id', '=', 'programmes.id')
            ->leftJoin('student_grade_records', 'students.id', '=', 'student_grade_records.student_id')
            ->where('enrolments.status', 'active')
            ->groupBy('students.id')
            ->havingRaw('COUNT(student_grade_records.id) > 0')
            ->limit(30)
            ->get();

        $duration = (microtime(true) - $start) * 1000;

        return [
            'duration_ms' => round($duration, 2),
            'records' => $complexQuery->count(),
            'status' => $duration < 2000 ? 'PASS' : 'WARN',
        ];
    }

    private function monitorQueries()
    {
        $this->info('Monitoring database queries...');

        // Enable query logging
        DB::enableQueryLog();

        // Run some typical operations
        $this->runTypicalOperations();

        $queries = DB::getQueryLog();

        $this->results['query_analysis'] = [
            'total_queries' => count($queries),
            'slow_queries' => $this->analyzeSlowQueries($queries),
            'query_types' => $this->categorizeQueries($queries),
        ];

        DB::disableQueryLog();
    }

    private function runTypicalOperations()
    {
        // Typical operations that would occur in the application
        Student::with('enrolments')->limit(10)->get();
        ModuleInstance::with(['module', 'curriculum'])->limit(5)->get();
        StudentGradeRecord::with('student')->where('is_visible_to_student', true)->limit(20)->get();
    }

    private function analyzeSlowQueries($queries)
    {
        $slowQueries = [];
        $threshold = 1000; // 1 second in milliseconds

        foreach ($queries as $query) {
            if ($query['time'] > $threshold) {
                $slowQueries[] = [
                    'sql' => $query['query'],
                    'time_ms' => $query['time'],
                    'bindings' => $query['bindings'],
                ];
            }
        }

        return [
            'count' => count($slowQueries),
            'threshold_ms' => $threshold,
            'queries' => $slowQueries,
        ];
    }

    private function categorizeQueries($queries)
    {
        $categories = ['SELECT' => 0, 'INSERT' => 0, 'UPDATE' => 0, 'DELETE' => 0, 'OTHER' => 0];

        foreach ($queries as $query) {
            $sql = strtoupper(trim($query['query']));
            if (strpos($sql, 'SELECT') === 0) {
                $categories['SELECT']++;
            } elseif (strpos($sql, 'INSERT') === 0) {
                $categories['INSERT']++;
            } elseif (strpos($sql, 'UPDATE') === 0) {
                $categories['UPDATE']++;
            } elseif (strpos($sql, 'DELETE') === 0) {
                $categories['DELETE']++;
            } else {
                $categories['OTHER']++;
            }
        }

        return $categories;
    }

    private function monitorMemory()
    {
        $this->info('Monitoring memory usage...');

        $currentMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        $memoryIncrease = $currentMemory - $this->startMemory;

        $this->results['memory'] = [
            'start_memory_mb' => round($this->startMemory / 1024 / 1024, 2),
            'current_memory_mb' => round($currentMemory / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'memory_increase_mb' => round($memoryIncrease / 1024 / 1024, 2),
            'memory_limit' => ini_get('memory_limit'),
            'status' => $currentMemory < (512 * 1024 * 1024) ? 'GOOD' : 'WARN',
        ];
    }

    private function checkDatabaseHealth()
    {
        $this->info('Checking database health...');

        try {
            $dbSize = $this->getDatabaseSize();
            $tableStats = $this->getTableStatistics();
            $indexUsage = $this->checkIndexUsage();

            $this->results['database_health'] = [
                'database_size' => $dbSize,
                'table_statistics' => $tableStats,
                'index_usage' => $indexUsage,
                'connection_status' => 'OK',
            ];
        } catch (\Exception $e) {
            $this->results['database_health'] = [
                'error' => $e->getMessage(),
                'connection_status' => 'ERROR',
            ];
        }
    }

    private function getDatabaseSize()
    {
        // This is SQLite specific - adjust for other databases
        $dbPath = database_path('database.sqlite');

        return file_exists($dbPath) ? round(filesize($dbPath) / 1024 / 1024, 2).' MB' : 'Unknown';
    }

    private function getTableStatistics()
    {
        $tables = ['students', 'enrolments', 'student_grade_records', 'modules', 'programmes'];
        $stats = [];

        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $stats[$table] = $count;
            } catch (\Exception $e) {
                $stats[$table] = 'Error: '.$e->getMessage();
            }
        }

        return $stats;
    }

    private function checkIndexUsage()
    {
        // Basic index information - this would be database-specific
        return [
            'note' => 'Index usage monitoring requires database-specific implementation',
            'recommendation' => 'Consider adding EXPLAIN ANALYZE for query optimization',
        ];
    }

    private function checkSystemResources()
    {
        $this->info('Checking system resources...');

        $diskUsage = $this->getDiskUsage();
        $loadAverage = $this->getLoadAverage();

        $this->results['system_resources'] = [
            'disk_usage' => $diskUsage,
            'load_average' => $loadAverage,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }

    private function getDiskUsage()
    {
        $bytes = disk_free_space('/');
        $totalBytes = disk_total_space('/');

        if ($bytes !== false && $totalBytes !== false) {
            $usedBytes = $totalBytes - $bytes;
            $usagePercent = round(($usedBytes / $totalBytes) * 100, 2);

            return [
                'free_gb' => round($bytes / 1024 / 1024 / 1024, 2),
                'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
                'used_percent' => $usagePercent,
                'status' => $usagePercent < 80 ? 'OK' : 'WARN',
            ];
        }

        return ['status' => 'Unable to determine'];
    }

    private function getLoadAverage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            return [
                '1min' => $load[0],
                '5min' => $load[1],
                '15min' => $load[2],
                'status' => $load[0] < 2.0 ? 'OK' : 'HIGH',
            ];
        }

        return ['status' => 'Not available'];
    }

    private function generateReport()
    {
        $totalTime = round((microtime(true) - $this->startTime) * 1000, 2);

        $this->results['execution_summary'] = [
            'total_time_ms' => $totalTime,
            'timestamp' => now()->toISOString(),
            'environment' => config('app.env'),
        ];

        switch ($this->option('output')) {
            case 'json':
                $this->outputJson();
                break;
            case 'file':
                $this->outputToFile();
                break;
            default:
                $this->outputToConsole();
        }
    }

    private function outputToConsole()
    {
        $this->info("\nPerformance Monitoring Results:");
        $this->info('==============================');

        if (isset($this->results['benchmarks'])) {
            $this->line("\n📊 Performance Benchmarks:");
            foreach ($this->results['benchmarks'] as $test => $result) {
                if (is_array($result) && isset($result['duration_ms'])) {
                    $status = $result['status'] ?? 'INFO';
                    $icon = $status === 'PASS' ? '✅' : ($status === 'WARN' ? '⚠️' : '❌');
                    $this->line("  {$icon} {$test}: {$result['duration_ms']}ms ({$status})");
                }
            }
        }

        if (isset($this->results['memory'])) {
            $this->line("\n🧠 Memory Usage:");
            $mem = $this->results['memory'];
            $this->line("  Current: {$mem['current_memory_mb']}MB");
            $this->line("  Peak: {$mem['peak_memory_mb']}MB");
            $this->line("  Increase: {$mem['memory_increase_mb']}MB");
        }

        if (isset($this->results['query_analysis'])) {
            $this->line("\n🔍 Query Analysis:");
            $qa = $this->results['query_analysis'];
            $this->line("  Total queries: {$qa['total_queries']}");
            $this->line("  Slow queries: {$qa['slow_queries']['count']}");
        }

        if (isset($this->results['database_health'])) {
            $this->line("\n💾 Database Health:");
            $db = $this->results['database_health'];
            if (isset($db['database_size'])) {
                $this->line("  Database size: {$db['database_size']}");
            }
            if (isset($db['table_statistics'])) {
                $this->line('  Table statistics:');
                foreach ($db['table_statistics'] as $table => $count) {
                    $this->line("    {$table}: {$count} records");
                }
            }
        }

        $exec = $this->results['execution_summary'];
        $this->line("\n⏱️  Total execution time: {$exec['total_time_ms']}ms");
    }

    private function outputJson()
    {
        echo json_encode($this->results, JSON_PRETTY_PRINT);
    }

    private function outputToFile()
    {
        $filename = storage_path('logs/performance-monitor-'.date('Y-m-d-H-i-s').'.json');
        file_put_contents($filename, json_encode($this->results, JSON_PRETTY_PRINT));
        $this->info("Performance report saved to: {$filename}");
    }
}
