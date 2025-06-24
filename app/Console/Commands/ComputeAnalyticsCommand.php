<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class ComputeAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:compute {--clear-cache : Clear existing cache before computing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precompute analytics metrics and refresh cache for better performance';

    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        parent::__construct();
        $this->analyticsService = $analyticsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Computing analytics metrics...');

        if ($this->option('clear-cache')) {
            $this->info('Clearing existing analytics cache...');
            $this->analyticsService->clearExpiredCache();
        }

        $startTime = microtime(true);

        try {
            // Precompute all analytics
            $this->info('Computing system overview...');
            $this->analyticsService->getSystemOverview();

            $this->info('Computing student performance trends...');
            $this->analyticsService->getStudentPerformanceTrends();

            $this->info('Computing programme effectiveness...');
            $this->analyticsService->getProgrammeEffectiveness();

            $this->info('Computing assessment completion rates...');
            $this->analyticsService->getAssessmentCompletionRates();

            $this->info('Computing student engagement metrics...');
            $this->analyticsService->getStudentEngagement();

            // Store historical metrics for trends
            $systemOverview = $this->analyticsService->getSystemOverview();
            $this->analyticsService->storeMetric(
                'system_overview',
                'daily_snapshot',
                $systemOverview,
                'daily',
                now()->toDateString()
            );

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->info("Analytics computation completed successfully in {$duration} seconds.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to compute analytics: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
