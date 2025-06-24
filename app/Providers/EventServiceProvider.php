<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Registered::class => [
        //     SendEmailVerificationNotification::class,
        //   ],
        //  SocialiteWasCalled::class => [
        //       'SocialiteProviders\\Azure\\AzureExtendSocialite@handle',
        //  ],
    ];

    public function boot(): void
    {
        //
    }
}
