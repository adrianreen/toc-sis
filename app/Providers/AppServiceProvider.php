<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/*  👇 ADD THESE THREE LINES  */
use Illuminate\Support\Facades\Event;                       // <- the missing one
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Azure\Provider as AzureProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    $this->app->singleton(\App\Services\EnrolmentService::class);
    }

    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
            $event->extendSocialite('azure', AzureProvider::class);
        });
    }
}
