<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get system overview analytics
     */
    public function systemOverview(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getSystemOverview();
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Analytics system overview error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to get system overview',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get student performance trends
     */
    public function studentPerformance(Request $request): JsonResponse
    {
        try {
            $periodType = $request->get('period_type', 'monthly');
            $months = $request->get('months', 12);
            
            $data = $this->analyticsService->getStudentPerformanceTrends($periodType, $months);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get student performance data'], 500);
        }
    }

    /**
     * Get programme effectiveness metrics
     */
    public function programmeEffectiveness(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getProgrammeEffectiveness();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get programme effectiveness data'], 500);
        }
    }

    /**
     * Get assessment completion rates
     */
    public function assessmentCompletion(Request $request): JsonResponse
    {
        try {
            $periodType = $request->get('period_type', 'weekly');
            $periods = $request->get('periods', 12);
            
            $data = $this->analyticsService->getAssessmentCompletionRates($periodType, $periods);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get assessment completion data'], 500);
        }
    }

    /**
     * Get student engagement metrics
     */
    public function studentEngagement(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getStudentEngagement();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get student engagement data'], 500);
        }
    }

    /**
     * Get chart data for specific chart type
     */
    public function chartData(Request $request, $type): JsonResponse
    {
        try {
            $options = $request->all();
            $data = $this->analyticsService->getChartData($type, $options);
            
            if (isset($data['error'])) {
                return response()->json($data, 400);
            }
            
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Analytics chart data error', [
                'type' => $type,
                'options' => $options,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to get chart data',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get historical metrics for trend analysis
     */
    public function historicalMetrics(Request $request): JsonResponse
    {
        try {
            $metricType = $request->get('metric_type');
            $periodType = $request->get('period_type', 'daily');
            $limit = $request->get('limit', 30);
            
            if (!$metricType) {
                return response()->json(['error' => 'metric_type is required'], 400);
            }
            
            $data = $this->analyticsService->getHistoricalMetrics($metricType, $periodType, $limit);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get historical metrics'], 500);
        }
    }

    /**
     * Refresh analytics cache
     */
    public function refreshCache(): JsonResponse
    {
        try {
            $this->analyticsService->refreshAllCache();
            return response()->json(['success' => true, 'message' => 'Analytics cache refreshed']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to refresh cache'], 500);
        }
    }

    /**
     * Clear expired cache entries
     */
    public function clearExpiredCache(): JsonResponse
    {
        try {
            $cleared = $this->analyticsService->clearExpiredCache();
            return response()->json([
                'success' => true, 
                'message' => "Cleared {$cleared} expired cache entries"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to clear expired cache'], 500);
        }
    }
}
