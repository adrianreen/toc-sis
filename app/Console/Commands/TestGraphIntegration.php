<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GraphTokenService;
use App\Services\OutlookService;
use Illuminate\Console\Command;

class TestGraphIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-integration {--user-id= : Test with specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Microsoft Graph API email integration';

    /**
     * Execute the console command.
     */
    public function handle(GraphTokenService $tokenService, OutlookService $outlookService)
    {
        $this->info('Testing Microsoft Graph API Email Integration');
        $this->info('=' . str_repeat('=', 50));

        // Test 1: Check Graph API availability
        $this->info('1. Testing Graph API availability...');
        $isAvailable = $outlookService->isGraphApiAvailable();
        $this->line("   Graph API Available: " . ($isAvailable ? '✓ YES' : '✗ NO'));

        // Test 2: Check token statistics
        $this->info('2. Checking token statistics...');
        $stats = $tokenService->getTokenStats();
        $this->line("   Total tokens: {$stats['total_tokens']}");
        $this->line("   Valid tokens: {$stats['valid_tokens']}");
        $this->line("   Expired tokens: {$stats['expired_tokens']}");
        $this->line("   Tokens with refresh: {$stats['tokens_with_refresh']}");

        // Test 3: Test with specific user if provided
        $userId = $this->option('user-id');
        if ($userId) {
            $this->info("3. Testing with user ID: {$userId}");
            $user = User::find($userId);
            
            if (!$user) {
                $this->error("   User not found!");
                return;
            }

            $this->line("   User: {$user->name} ({$user->email})");
            $this->line("   Role: {$user->role}");
            
            // Check if user has Graph token
            $hasToken = $user->hasValidGraphToken();
            $this->line("   Has valid Graph token: " . ($hasToken ? '✓ YES' : '✗ NO'));
            
            if ($user->graphToken) {
                $this->line("   Token expires: {$user->graphToken->expires_at}");
                $this->line("   Has email permissions: " . ($tokenService->hasEmailPermissions($user) ? '✓ YES' : '✗ NO'));
            }

            // Try to get email summary
            $this->info('   Testing email summary...');
            try {
                $emailData = $outlookService->getEmailSummary($user);
                
                if (isset($emailData['error'])) {
                    $this->error("   Error: {$emailData['error']} - {$emailData['message']}");
                } else {
                    $this->line("   ✓ Unread count: {$emailData['unread_count']}");
                    $this->line("   ✓ Recent emails: " . count($emailData['recent_emails']));
                    
                    if (count($emailData['recent_emails']) > 0) {
                        $this->line("   Recent email subjects:");
                        foreach (array_slice($emailData['recent_emails'], 0, 3) as $email) {
                            $status = $email['is_read'] ? '📖' : '📧';
                            $this->line("     {$status} {$email['subject']}");
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("   Exception: {$e->getMessage()}");
            }
        } else {
            $this->info('3. Testing with all users who have tokens...');
            $usersWithTokens = User::whereHas('graphToken')->limit(5)->get();
            
            if ($usersWithTokens->count() === 0) {
                $this->line("   No users with Graph tokens found");
            } else {
                foreach ($usersWithTokens as $user) {
                    $this->line("   User: {$user->name} - Valid: " . ($user->hasValidGraphToken() ? '✓' : '✗'));
                }
            }
        }

        // Test 4: Service health
        $this->info('4. Service health check...');
        try {
            $health = $outlookService->getServiceHealth();
            $this->line("   Graph API: " . ($health['graph_api_available'] ? '✓ Available' : '✗ Unavailable'));
            $this->line("   Token stats included: " . (isset($health['token_stats']) ? '✓ YES' : '✗ NO'));
        } catch (\Exception $e) {
            $this->error("   Health check failed: {$e->getMessage()}");
        }

        $this->info('Test completed!');
        
        if (!$userId) {
            $this->comment('Tip: Use --user-id=X to test with a specific user');
        }
    }
}
