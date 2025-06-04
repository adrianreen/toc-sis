<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled notifications';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing scheduled notifications...');

        $processed = $this->notificationService->processScheduledNotifications();

        $this->info("Processed {$processed} scheduled notifications.");

        return 0;
    }
}
