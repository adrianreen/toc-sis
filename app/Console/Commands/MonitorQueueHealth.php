<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorQueueHealth extends Command
{
    protected $signature = 'queue:health-check';

    protected $description = 'Check queue system health and report status';

    public function handle()
    {
        $this->info('🔍 Queue System Health Check');
        $this->line('');

        try {
            // Check queue counts
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            // Status indicators
            $this->line('📊 Queue Statistics:');
            $this->line('   Pending jobs: '.($pendingJobs > 0 ? "<fg=yellow>$pendingJobs</>" : "<fg=green>$pendingJobs</>"));
            $this->line('   Failed jobs: '.($failedJobs > 0 ? "<fg=red>$failedJobs</>" : "<fg=green>$failedJobs</>"));

            // Check for stuck jobs (older than 1 hour)
            $stuckJobs = DB::table('jobs')
                ->where('created_at', '<', now()->subHour())
                ->count();

            if ($stuckJobs > 0) {
                $this->warn("⚠️  Found $stuckJobs potentially stuck jobs (older than 1 hour)");
            }

            // Recent activity
            $recentJobs = DB::table('jobs')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get(['queue', 'payload', 'created_at']);

            if ($recentJobs->count() > 0) {
                $this->line('');
                $this->line('📋 Recent Jobs:');
                foreach ($recentJobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $jobClass = $payload['displayName'] ?? 'Unknown Job';
                    $this->line("   • $jobClass (Queue: {$job->queue})");
                }
            }

            // Overall status
            $this->line('');
            if ($failedJobs == 0 && $stuckJobs == 0) {
                $this->info('✅ Queue system is healthy');
            } elseif ($failedJobs > 0 || $stuckJobs > 0) {
                $this->warn('⚠️  Queue system needs attention');
            }

            // Configuration info
            $this->line('');
            $this->line('🔧 Configuration:');
            $this->line('   Driver: '.config('queue.default'));
            $this->line('   Connection: '.config('queue.connections.'.config('queue.default').'.driver', 'N/A'));

        } catch (\Exception $e) {
            $this->error('❌ Queue health check failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
