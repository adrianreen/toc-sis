<?php

namespace App\Services;

use App\Models\AnalyticsCache;
use App\Models\AnalyticsMetric;
use App\Models\Enrolment;
use App\Models\Programme;
use App\Models\Student;
use App\Models\StudentGradeRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
     * Get student performance trends over time
     */
    public function getStudentPerformanceTrends($periodType = 'monthly', $months = 12)
    {
        $cacheKey = "student_performance_trends_{$periodType}_{$months}";

        // Try to get from cache first
        $cached = AnalyticsCache::getCached($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $startDate = now()->subMonths($months);

            // Get assessment completion rates by period using new grade records
            $assessmentTrends = StudentGradeRecord::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                DB::raw('COUNT(*) as total_assessments'),
                DB::raw('SUM(CASE WHEN grade >= '.config('academic.default_pass_mark').' THEN 1 ELSE 0 END) as passed_assessments'),
                DB::raw('AVG(CASE WHEN grade IS NOT NULL THEN grade ELSE 0 END) as avg_grade')
            )
                ->where('created_at', '>=', $startDate)
                ->whereNotNull('grade')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Get student enrollment trends
            $enrollmentTrends = Enrolment::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                DB::raw('COUNT(*) as new_enrollments'),
                DB::raw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_enrollments')
            )
                ->where('created_at', '>=', $startDate)
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $data = [
                'assessment_trends' => $assessmentTrends,
                'enrollment_trends' => $enrollmentTrends,
                'period_type' => $periodType,
                'generated_at' => now()->toISOString(),
            ];

            // Cache for 1 hour
            AnalyticsCache::setCached($cacheKey, $data, 60);

            return $data;

        } catch (\Exception $e) {
            Log::error('Student performance trends calculation failed', [
                'error' => $e->getMessage(),
                'period_type' => $periodType,
                'months' => $months,
            ]);

            // Return empty data structure instead of failing
            return [
                'assessment_trends' => [],
                'enrollment_trends' => [],
                'period_type' => $periodType,
                'generated_at' => now()->toISOString(),
                'error' => 'Data temporarily unavailable',
            ];
        }
    }

    /**
     * Get programme effectiveness metrics
     */
    public function getProgrammeEffectiveness()
    {
        $cacheKey = 'programme_effectiveness';

        // Check cache first
        $cached = AnalyticsCache::getCached($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            // Use raw SQL queries for better performance and to avoid memory issues
            $programmes = Programme::all();
            $data = [];

            foreach ($programmes as $programme) {
                // Get enrollment statistics with single queries
                $enrollmentStats = DB::table('enrolments as e')
                    ->join('programme_instances as pi', 'e.programme_instance_id', '=', 'pi.id')
                    ->where('pi.programme_id', $programme->id)
                    ->where('e.enrolment_type', 'programme')
                    ->selectRaw('
                        COUNT(*) as total_enrollments,
                        SUM(CASE WHEN e.status = "active" THEN 1 ELSE 0 END) as active_enrollments,
                        SUM(CASE WHEN e.status = "completed" THEN 1 ELSE 0 END) as completed_enrollments
                    ')
                    ->first();

                $totalEnrolments = $enrollmentStats->total_enrollments ?? 0;
                $activeEnrolments = $enrollmentStats->active_enrollments ?? 0;
                $completedEnrolments = $enrollmentStats->completed_enrollments ?? 0;

                // Calculate completion rate
                $completionRate = $totalEnrolments > 0 ? ($completedEnrolments / $totalEnrolments) * 100 : 0;

                // Get assessment performance with new architecture
                $assessmentStats = DB::table('student_grade_records as sgr')
                    ->join('module_instances as mi', 'sgr.module_instance_id', '=', 'mi.id')
                    ->join('programme_instance_curriculum as pic', 'mi.id', '=', 'pic.module_instance_id')
                    ->join('programme_instances as pi', 'pic.programme_instance_id', '=', 'pi.id')
                    ->where('pi.programme_id', $programme->id)
                    ->whereNotNull('sgr.grade')
                    ->whereNotNull('sgr.grade')
                    ->selectRaw('
                        COUNT(*) as total_graded,
                        AVG(sgr.grade) as avg_grade,
                        SUM(CASE WHEN sgr.grade >= '.config('academic.default_pass_mark').' THEN 1 ELSE 0 END) as passed_count
                    ')
                    ->first();

                $totalGraded = $assessmentStats->total_graded ?? 0;
                $avgGrade = $assessmentStats->avg_grade ?? 0;
                $passedCount = $assessmentStats->passed_count ?? 0;
                $passRate = $totalGraded > 0 ? ($passedCount / $totalGraded) * 100 : 0;

                $data[] = [
                    'programme_id' => $programme->id,
                    'programme_title' => $programme->title,
                    'awarding_body' => $programme->awarding_body,
                    'total_enrollments' => (int) $totalEnrolments,
                    'active_enrollments' => (int) $activeEnrolments,
                    'completed_enrollments' => (int) $completedEnrolments,
                    'completion_rate' => round($completionRate, 2),
                    'average_grade' => round($avgGrade, 2),
                    'pass_rate' => round($passRate, 2),
                ];
            }

            $result = [
                'programmes' => $data,
                'generated_at' => now()->toISOString(),
            ];

            // Cache for 2 hours - but don't fail if caching fails
            AnalyticsCache::setCached($cacheKey, $result, 120);

            return $result;

        } catch (\Exception $e) {
            Log::error('Programme effectiveness calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty data structure instead of failing
            return [
                'programmes' => [],
                'generated_at' => now()->toISOString(),
                'error' => 'Data temporarily unavailable',
            ];
        }
    }

    /**
     * Get system overview statistics
     */
    public function getSystemOverview()
    {
        $cacheKey = 'system_overview';

        $cached = AnalyticsCache::getCached($cacheKey);
        if ($cached) {
            return $cached;
        }

        $data = [
            'students' => [
                'total' => Student::count(),
                'active' => Student::where('status', 'active')->count(),
                'enrolled' => Student::where('status', 'enrolled')->count(),
                'deferred' => Student::where('status', 'deferred')->count(),
                'completed' => Student::where('status', 'completed')->count(),
            ],
            'programmes' => [
                'total' => Programme::count(),
                'active' => Programme::count(),
            ],
            'grade_records' => [
                'total' => StudentGradeRecord::count(),
                'graded' => StudentGradeRecord::whereNotNull('grade')->count(),
                'passed' => StudentGradeRecord::where('grade', '>=', config('academic.default_pass_mark'))->count(),
                'failed' => StudentGradeRecord::where('grade', '<', config('academic.default_pass_mark'))->whereNotNull('grade')->count(),
            ],
            'enrollments' => [
                'total' => Enrolment::count(),
                'active' => Enrolment::where('status', 'active')->count(),
                'completed' => Enrolment::where('status', 'completed')->count(),
                'deferred' => Enrolment::where('status', 'deferred')->count(),
            ],
            'generated_at' => now()->toISOString(),
        ];

        // Cache for 30 minutes
        AnalyticsCache::setCached($cacheKey, $data, 30);

        return $data;
    }

    /**
     * Get assessment completion rates by time period
     */
    public function getAssessmentCompletionRates($periodType = 'weekly', $periods = 12)
    {
        $cacheKey = "assessment_completion_rates_{$periodType}_{$periods}";

        $cached = AnalyticsCache::getCached($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $dateFormat = $periodType === 'weekly' ? '%Y-%u' : '%Y-%m';
            $startDate = $periodType === 'weekly'
                ? now()->subWeeks($periods)
                : now()->subMonths($periods);

            $completionRates = StudentGradeRecord::select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as total_assessments'),
                DB::raw('SUM(CASE WHEN grade IS NOT NULL THEN 1 ELSE 0 END) as completed_assessments'),
                DB::raw('0 as overdue_assessments')
            )
                ->where('created_at', '>=', $startDate)
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->map(function ($item) {
                    $completionRate = $item->total_assessments > 0
                        ? ($item->completed_assessments / $item->total_assessments) * 100
                        : 0;

                    return [
                        'period' => $item->period,
                        'total_assessments' => $item->total_assessments,
                        'completed_assessments' => $item->completed_assessments,
                        'overdue_assessments' => $item->overdue_assessments,
                        'completion_rate' => round($completionRate, 2),
                    ];
                });

            $data = [
                'completion_rates' => $completionRates,
                'period_type' => $periodType,
                'generated_at' => now()->toISOString(),
            ];

            // Cache for 1 hour
            AnalyticsCache::setCached($cacheKey, $data, 60);

            return $data;

        } catch (\Exception $e) {
            Log::error('Assessment completion rates calculation failed', [
                'error' => $e->getMessage(),
                'period_type' => $periodType,
                'periods' => $periods,
            ]);

            // Return empty data structure instead of failing
            return [
                'completion_rates' => [],
                'period_type' => $periodType,
                'generated_at' => now()->toISOString(),
                'error' => 'Data temporarily unavailable',
            ];
        }
    }

    /**
     * Get student engagement metrics
     */
    public function getStudentEngagement()
    {
        $cacheKey = 'student_engagement';

        $cached = AnalyticsCache::getCached($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            // Get recent activity metrics
            $thirtyDaysAgo = now()->subDays(30);

            $recentlyActive = Student::whereHas('studentGradeRecords', function ($query) use ($thirtyDaysAgo) {
                $query->where('updated_at', '>=', $thirtyDaysAgo);
            })->count();

            $totalActiveStudents = Student::where('status', 'active')->count();
            $engagementRate = $totalActiveStudents > 0 ? ($recentlyActive / $totalActiveStudents) * 100 : 0;

            // Get submission patterns
            $submissionPatterns = StudentGradeRecord::select(
                DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                DB::raw('COUNT(*) as submission_count')
            )
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->whereNotNull('grade')
                ->groupBy('day_of_week')
                ->orderBy('day_of_week')
                ->get()
                ->mapWithKeys(function ($item) {
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                    return [$days[$item->day_of_week - 1] => $item->submission_count];
                });

            $data = [
                'total_active_students' => $totalActiveStudents,
                'recently_active_students' => $recentlyActive,
                'engagement_rate' => round($engagementRate, 2),
                'submission_patterns' => $submissionPatterns,
                'generated_at' => now()->toISOString(),
            ];

            // Cache for 1 hour
            AnalyticsCache::setCached($cacheKey, $data, 60);

            return $data;

        } catch (\Exception $e) {
            Log::error('Student engagement calculation failed', [
                'error' => $e->getMessage(),
            ]);

            // Return empty data structure instead of failing
            return [
                'total_active_students' => 0,
                'recently_active_students' => 0,
                'engagement_rate' => 0,
                'submission_patterns' => [],
                'generated_at' => now()->toISOString(),
                'error' => 'Data temporarily unavailable',
            ];
        }
    }

    /**
     * Store calculated analytics metric
     */
    public function storeMetric($metricType, $metricKey, $data, $periodType = 'daily', $periodDate = null)
    {
        $periodDate = $periodDate ?: now()->toDateString();

        return AnalyticsMetric::create([
            'metric_type' => $metricType,
            'metric_key' => $metricKey,
            'metric_data' => $data,
            'period_type' => $periodType,
            'period_date' => $periodDate,
            'calculated_at' => now(),
        ]);
    }

    /**
     * Get historical metrics for trend analysis
     */
    public function getHistoricalMetrics($metricType, $periodType = 'daily', $limit = 30)
    {
        return AnalyticsMetric::ofType($metricType)
            ->ofPeriod($periodType)
            ->orderBy('period_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clear expired cache entries
     */
    public function clearExpiredCache()
    {
        return AnalyticsCache::clearExpired();
    }

    /**
     * Force refresh of all cached analytics
     */
    public function refreshAllCache()
    {
        AnalyticsCache::clearAll();

        // Regenerate key metrics
        $this->getSystemOverview();
        $this->getStudentPerformanceTrends();
        $this->getProgrammeEffectiveness();
        $this->getAssessmentCompletionRates();
        $this->getStudentEngagement();

        Log::info('Analytics cache refreshed');
    }

    /**
     * Get chart data formatted for Chart.js
     */
    public function getChartData($type, $options = [])
    {
        switch ($type) {
            case 'student_performance':
                return $this->formatStudentPerformanceChart($options);
            case 'programme_effectiveness':
                return $this->formatProgrammeEffectivenessChart($options);
            case 'assessment_completion':
                return $this->formatAssessmentCompletionChart($options);
            case 'student_engagement':
                return $this->formatStudentEngagementChart($options);
            default:
                return ['error' => 'Unknown chart type'];
        }
    }

    /**
     * Format student performance data for Chart.js
     */
    private function formatStudentPerformanceChart($options = [])
    {
        $data = $this->getStudentPerformanceTrends();

        // Handle error cases where data might be empty or have error
        if (isset($data['error']) || empty($data['assessment_trends'])) {
            return [
                'type' => 'line',
                'data' => [
                    'labels' => [],
                    'datasets' => [],
                ],
                'error' => $data['error'] ?? 'No data available',
            ];
        }

        // Convert to collection if it's an array
        $trends = collect($data['assessment_trends']);

        $labels = $trends->pluck('period')->toArray();
        $avgGrades = $trends->pluck('avg_grade')->toArray();
        $passRates = $trends->map(function ($item) {
            $item = (object) $item; // Ensure it's an object

            return $item->total_assessments > 0
                ? ($item->passed_assessments / $item->total_assessments) * 100
                : 0;
        })->toArray();

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Average Grade',
                        'data' => $avgGrades,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'Pass Rate (%)',
                        'data' => $passRates,
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'yAxisID' => 'y1',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'interaction' => ['mode' => 'index', 'intersect' => false],
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'title' => ['display' => true, 'text' => 'Grade'],
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'title' => ['display' => true, 'text' => 'Pass Rate (%)'],
                        'grid' => ['drawOnChartArea' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * Format programme effectiveness data for Chart.js
     */
    private function formatProgrammeEffectivenessChart($options = [])
    {
        $data = $this->getProgrammeEffectiveness();

        // Handle error cases where data might be empty or have error
        if (isset($data['error']) || empty($data['programmes'])) {
            return [
                'type' => 'bar',
                'data' => [
                    'labels' => [],
                    'datasets' => [],
                ],
                'error' => $data['error'] ?? 'No data available',
            ];
        }

        $labels = array_column($data['programmes'], 'programme_title');
        $completionRates = array_column($data['programmes'], 'completion_rate');
        $enrollments = array_column($data['programmes'], 'total_enrollments');

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Completion Rate (%)',
                        'data' => $completionRates,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => 'rgb(59, 130, 246)',
                        'borderWidth' => 1,
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'Total Enrollments',
                        'data' => $enrollments,
                        'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                        'borderColor' => 'rgb(34, 197, 94)',
                        'borderWidth' => 1,
                        'yAxisID' => 'y1',
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'title' => ['display' => true, 'text' => 'Completion Rate (%)'],
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'title' => ['display' => true, 'text' => 'Enrollments'],
                        'grid' => ['drawOnChartArea' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * Format assessment completion data for Chart.js
     */
    private function formatAssessmentCompletionChart($options = [])
    {
        $data = $this->getAssessmentCompletionRates();

        // Handle error cases where data might be empty or have error
        if (isset($data['error']) || empty($data['completion_rates'])) {
            return [
                'type' => 'line',
                'data' => [
                    'labels' => [],
                    'datasets' => [],
                ],
                'error' => $data['error'] ?? 'No data available',
            ];
        }

        // Convert to collection if it's an array
        $rates = collect($data['completion_rates']);

        $labels = $rates->pluck('period')->toArray();
        $completionRates = $rates->pluck('completion_rate')->toArray();
        $overdueRates = $rates->map(function ($item) {
            $item = (array) $item; // Ensure it's an array for access

            return $item['total_assessments'] > 0
                ? ($item['overdue_assessments'] / $item['total_assessments']) * 100
                : 0;
        })->toArray();

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Completion Rate (%)',
                        'data' => $completionRates,
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'fill' => true,
                    ],
                    [
                        'label' => 'Overdue Rate (%)',
                        'data' => $overdueRates,
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'fill' => true,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => ['display' => true, 'text' => 'Rate (%)'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Format student engagement data for Chart.js
     */
    private function formatStudentEngagementChart($options = [])
    {
        $data = $this->getStudentEngagement();

        // Handle error cases where data might be empty or have error
        if (isset($data['error']) || empty($data['submission_patterns'])) {
            return [
                'type' => 'doughnut',
                'data' => [
                    'labels' => [],
                    'datasets' => [],
                ],
                'error' => $data['error'] ?? 'No data available',
            ];
        }

        // Convert to array if it's a collection, or ensure it's an array
        $patterns = is_array($data['submission_patterns'])
            ? $data['submission_patterns']
            : $data['submission_patterns']->toArray();

        $labels = array_keys($patterns);
        $submissions = array_values($patterns);

        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Submissions by Day',
                        'data' => $submissions,
                        'backgroundColor' => [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(14, 165, 233, 0.8)',
                        ],
                        'borderColor' => [
                            'rgb(239, 68, 68)',
                            'rgb(34, 197, 94)',
                            'rgb(59, 130, 246)',
                            'rgb(168, 85, 247)',
                            'rgb(245, 158, 11)',
                            'rgb(236, 72, 153)',
                            'rgb(14, 165, 233)',
                        ],
                        'borderWidth' => 1,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
            ],
        ];
    }
}
