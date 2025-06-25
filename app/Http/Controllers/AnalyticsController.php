<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService) {}

    /**
     * Get system overview analytics
     */
    public function systemOverview(): JsonResponse
    {
        try {
            $data = $this->analyticsService->getSystemOverview();

            return response()->json($data);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get system overview');
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
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get student performance data');
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
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get programme effectiveness data');
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
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get assessment completion data');
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
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get student engagement data');
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
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get chart data');
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

            if (! $metricType) {
                return $this->errorResponse('metric_type is required');
            }

            $data = $this->analyticsService->getHistoricalMetrics($metricType, $periodType, $limit);

            return response()->json($data);
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to get historical metrics');
        }
    }

    /**
     * Refresh analytics cache
     */
    public function refreshCache(): JsonResponse
    {
        try {
            $this->analyticsService->refreshAllCache();

            return $this->successResponse('Analytics cache refreshed');
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to refresh cache');
        }
    }

    /**
     * Clear expired cache entries
     */
    public function clearExpiredCache(): JsonResponse
    {
        try {
            $cleared = $this->analyticsService->clearExpiredCache();

            return $this->successResponse("Cleared {$cleared} expired cache entries");
        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to clear expired cache');
        }
    }
}
