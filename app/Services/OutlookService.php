<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutlookService
{
    private GraphTokenService $tokenService;

    public function __construct(GraphTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Get recent emails for user
     */
    public function getRecentEmails(User $user, int $count = 5): array
    {
        $cacheKey = "user_emails_{$user->id}_{$count}";

        return Cache::remember($cacheKey, 300, function () use ($user, $count) {
            return $this->fetchRecentEmails($user, $count);
        });
    }

    /**
     * Get unread email count for user
     */
    public function getUnreadCount(User $user): int
    {
        $cacheKey = "user_unread_count_{$user->id}";

        return Cache::remember($cacheKey, 180, function () use ($user) {
            return $this->fetchUnreadCount($user);
        });
    }

    /**
     * Get email summary data for dashboard widget
     */
    public function getEmailSummary(User $user): array
    {
        try {
            // Check if user has valid token and email permissions
            if (! $this->tokenService->hasEmailPermissions($user)) {
                return [
                    'error' => 'no_permissions',
                    'message' => 'Email access not authorized. Please re-authenticate.',
                    'unread_count' => 0,
                    'recent_emails' => [],
                ];
            }

            $unreadCount = $this->getUnreadCount($user);
            $recentEmails = $this->getRecentEmails($user, 5);

            // Log email access for audit
            activity()
                ->causedBy($user)
                ->withProperties([
                    'unread_count' => $unreadCount,
                    'recent_emails_count' => count($recentEmails),
                ])
                ->log('Email dashboard accessed');

            return [
                'unread_count' => $unreadCount,
                'recent_emails' => $recentEmails,
                'last_updated' => now()->toISOString(),
            ];

        } catch (Exception $e) {
            Log::error('Error getting email summary', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'api_error',
                'message' => 'Unable to load emails at this time. Please try again later.',
                'unread_count' => 0,
                'recent_emails' => [],
            ];
        }
    }

    /**
     * Refresh email data cache for user
     */
    public function refreshEmailCache(User $user): array
    {
        // Clear existing cache
        Cache::forget("user_emails_{$user->id}_5");
        Cache::forget("user_unread_count_{$user->id}");

        // Fetch fresh data
        return $this->getEmailSummary($user);
    }

    /**
     * Internal method to fetch recent emails from Graph API
     */
    private function fetchRecentEmails(User $user, int $count): array
    {
        $accessToken = $this->tokenService->getValidToken($user);

        if (! $accessToken) {
            Log::warning('No valid access token for email fetch', ['user_id' => $user->id]);

            return [];
        }

        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me/messages', [
                    '$top' => $count,
                    '$orderby' => 'receivedDateTime desc',
                    '$select' => 'id,subject,sender,receivedDateTime,isRead,bodyPreview,importance',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $this->formatEmailData($data['value'] ?? []);
            }

            Log::error('Graph API error fetching emails', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return [];

        } catch (Exception $e) {
            Log::error('Exception fetching emails', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Internal method to fetch unread count from Graph API
     */
    private function fetchUnreadCount(User $user): int
    {
        $accessToken = $this->tokenService->getValidToken($user);

        if (! $accessToken) {
            return 0;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me/mailFolders/inbox', [
                    '$select' => 'unreadItemCount',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['unreadItemCount'] ?? 0;
            }

            Log::error('Graph API error fetching unread count', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return 0;

        } catch (Exception $e) {
            Log::error('Exception fetching unread count', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Format raw email data from Graph API
     */
    private function formatEmailData(array $emails): array
    {
        return array_map(function ($email) {
            return [
                'id' => $email['id'] ?? '',
                'subject' => $email['subject'] ?? 'No Subject',
                'sender' => $email['sender']['emailAddress']['name'] ?? 'Unknown Sender',
                'sender_email' => $email['sender']['emailAddress']['address'] ?? '',
                'received_time' => $this->formatDateTime($email['receivedDateTime'] ?? null),
                'received_time_human' => $this->humanDateTime($email['receivedDateTime'] ?? null),
                'is_read' => $email['isRead'] ?? false,
                'preview' => $this->limitPreview($email['bodyPreview'] ?? ''),
                'importance' => $email['importance'] ?? 'normal',
                'is_important' => ($email['importance'] ?? 'normal') === 'high',
            ];
        }, $emails);
    }

    /**
     * Format datetime for display
     */
    private function formatDateTime(?string $dateTime): string
    {
        if (! $dateTime) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($dateTime)->format('M j, g:i A');
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Human readable datetime
     */
    private function humanDateTime(?string $dateTime): string
    {
        if (! $dateTime) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($dateTime)->diffForHumans();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Limit email preview text
     */
    private function limitPreview(string $preview): string
    {
        return \Str::limit($preview, 100);
    }

    /**
     * Check if Graph API is available
     */
    public function isGraphApiAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get('https://graph.microsoft.com/v1.0/$metadata');

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get service health status
     */
    public function getServiceHealth(): array
    {
        return [
            'graph_api_available' => $this->isGraphApiAvailable(),
            'token_stats' => $this->tokenService->getTokenStats(),
            'cache_stats' => [
                'email_cache_hits' => Cache::get('email_cache_hits', 0),
                'email_cache_misses' => Cache::get('email_cache_misses', 0),
            ],
        ];
    }
}
