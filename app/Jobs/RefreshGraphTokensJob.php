<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GraphTokenService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefreshGraphTokensJob implements ShouldQueue
{
    use Queueable;

    private User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(GraphTokenService $tokenService): void
    {
        try {
            Log::info('Refreshing Graph tokens for user', ['user_id' => $this->user->id]);
            
            $newToken = $tokenService->refreshToken($this->user);
            
            if ($newToken) {
                Log::info('Graph tokens refreshed successfully', ['user_id' => $this->user->id]);
            } else {
                Log::warning('Failed to refresh Graph tokens', ['user_id' => $this->user->id]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in RefreshGraphTokensJob', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e; // Re-throw to mark job as failed
        }
    }
}
