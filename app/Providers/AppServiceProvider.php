<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

/*  👇 ADD THESE THREE LINES  */
use Illuminate\Support\Facades\Event;                       // <- the missing one
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Azure\Provider as AzureProvider;

// Observer imports
// TODO: Re-add observers for new architecture models when implemented

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    $this->app->singleton(\App\Services\EnrolmentService::class);
    }

    public function boot(): void
    {
        // Register model observers
        // TODO: Re-add observers for new architecture models when implemented
        
        Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
            $event->extendSocialite('azure', AzureProvider::class);
        });

        // Schedule notification commands
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Send assessment deadline reminders daily at 9 AM
            $schedule->command('notifications:assessment-reminders')
                    ->dailyAt('09:00')
                    ->withoutOverlapping()
                    ->runInBackground();
            
            // Process scheduled notifications every 15 minutes
            $schedule->command('notifications:process-scheduled')
                    ->everyFifteenMinutes()
                    ->withoutOverlapping();
            
            // Release scheduled assessments every hour
            $schedule->command('assessments:release-scheduled')
                    ->hourly()
                    ->withoutOverlapping();
        });
    }
}
