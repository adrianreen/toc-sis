<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OutlookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    private OutlookService $outlookService;

    public function __construct(OutlookService $outlookService)
    {
        $this->outlookService = $outlookService;
    }

    /**
     * Get email summary for dashboard widget
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => 'Authentication required',
                    'unread_count' => 0,
                    'recent_emails' => [],
                ], 401);
            }

            // Check if this is a refresh request
            $refresh = $request->boolean('refresh', false);

            if ($refresh) {
                $emailData = $this->outlookService->refreshEmailCache($user);
            } else {
                $emailData = $this->outlookService->getEmailSummary($user);
            }

            return response()->json($emailData);

        } catch (\Exception $e) {
            Log::error('Email summary API error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'server_error',
                'message' => 'Internal server error occurred',
                'unread_count' => 0,
                'recent_emails' => [],
            ], 500);
        }
    }

    /**
     * Get service health status
     */
    public function health(): JsonResponse
    {
        try {
            $health = $this->outlookService->getServiceHealth();
            return response()->json($health);

        } catch (\Exception $e) {
            Log::error('Email health check error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'graph_api_available' => false,
                'error' => 'Health check failed'
            ], 500);
        }
    }

    /**
     * Force refresh email cache for user
     */
    public function refresh(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => 'Authentication required'
                ], 401);
            }

            $emailData = $this->outlookService->refreshEmailCache($user);

            return response()->json([
                'success' => true,
                'message' => 'Email cache refreshed',
                'data' => $emailData
            ]);

        } catch (\Exception $e) {
            Log::error('Email cache refresh error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'refresh_failed',
                'message' => 'Failed to refresh email cache'
            ], 500);
        }
    }

}
