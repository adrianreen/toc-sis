<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserGraphToken;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GraphTokenService
{
    private string $clientId;

    private string $clientSecret;

    private string $tenantId;

    private string $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.azure.client_id');
        $this->clientSecret = config('services.azure.client_secret');
        $this->tenantId = config('services.azure.tenant');
        $this->redirectUri = config('services.azure.redirect');
    }

    /**
     * Store or update Graph API tokens for a user
     */
    public function storeTokens(User $user, array $tokenData): UserGraphToken
    {
        Log::info('Storing Graph API tokens', [
            'user_id' => $user->id,
            'has_access_token' => isset($tokenData['access_token']),
            'has_refresh_token' => isset($tokenData['refresh_token']),
            'expires_in' => $tokenData['expires_in'] ?? null,
        ]);

        return UserGraphToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                'scopes' => explode(' ', $tokenData['scope'] ?? ''),
                'last_refreshed_at' => now(),
            ]
        );
    }

    /**
     * Get valid access token for user (refresh if needed)
     */
    public function getValidToken(User $user): ?string
    {
        $graphToken = $user->graphToken;

        if (! $graphToken) {
            Log::info('No Graph token found for user', ['user_id' => $user->id]);

            return null;
        }

        // If token is not expired, return it
        if (! $graphToken->isExpired()) {
            return $graphToken->access_token;
        }

        // Try to refresh the token
        if ($graphToken->refresh_token) {
            Log::info('Attempting to refresh Graph token', ['user_id' => $user->id]);

            return $this->refreshToken($user);
        }

        Log::warning('Graph token expired and no refresh token available', ['user_id' => $user->id]);

        return null;
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken(User $user): ?string
    {
        $graphToken = $user->graphToken;

        if (! $graphToken || ! $graphToken->refresh_token) {
            return null;
        }

        try {
            $response = Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $graphToken->refresh_token,
                'scope' => 'https://graph.microsoft.com/Mail.Read https://graph.microsoft.com/offline_access',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $this->storeTokens($user, $tokenData);

                Log::info('Graph token refreshed successfully', ['user_id' => $user->id]);

                return $tokenData['access_token'];
            }

            Log::error('Failed to refresh Graph token', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            // If refresh fails, remove the invalid token
            $graphToken->delete();

            return null;

        } catch (Exception $e) {
            Log::error('Exception during token refresh', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens(string $code, User $user): ?UserGraphToken
    {
        try {
            Log::info('Exchanging authorization code for Graph tokens', ['user_id' => $user->id]);

            $response = Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'scope' => 'https://graph.microsoft.com/Mail.Read https://graph.microsoft.com/offline_access',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $graphToken = $this->storeTokens($user, $tokenData);

                Log::info('Graph tokens obtained successfully', ['user_id' => $user->id]);

                return $graphToken;
            }

            Log::error('Failed to exchange code for Graph tokens', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception during code exchange', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Revoke user's Graph API tokens
     */
    public function revokeTokens(User $user): bool
    {
        $graphToken = $user->graphToken;

        if (! $graphToken) {
            return true; // Already revoked/no tokens
        }

        try {
            // Attempt to revoke the refresh token with Microsoft
            if ($graphToken->refresh_token) {
                Http::asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/logout", [
                    'token' => $graphToken->refresh_token,
                    'token_type_hint' => 'refresh_token',
                ]);
            }

            // Delete from our database
            $graphToken->delete();

            Log::info('Graph tokens revoked', ['user_id' => $user->id]);

            return true;

        } catch (Exception $e) {
            Log::error('Error revoking Graph tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Still delete from our database even if remote revocation fails
            $graphToken->delete();

            return false;
        }
    }

    /**
     * Check if user has required email permissions
     */
    public function hasEmailPermissions(User $user): bool
    {
        $graphToken = $user->graphToken;

        if (! $graphToken) {
            return false;
        }

        return $graphToken->hasScope('https://graph.microsoft.com/Mail.Read') ||
               $graphToken->hasScope('Mail.Read');
    }

    /**
     * Get token statistics for debugging
     */
    public function getTokenStats(): array
    {
        return [
            'total_tokens' => UserGraphToken::count(),
            'valid_tokens' => UserGraphToken::where('expires_at', '>', now())->count(),
            'expired_tokens' => UserGraphToken::where('expires_at', '<=', now())->count(),
            'tokens_with_refresh' => UserGraphToken::whereNotNull('refresh_token')->count(),
        ];
    }
}
