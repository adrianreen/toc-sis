<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootAzureSocialite();
    }
    
    private function bootAzureSocialite()
    {
        Socialite::extend('azure', function ($app) {
            $config = $app['config']['services.azure'];
            return Socialite::buildProvider(
                \SocialiteProviders\Azure\Provider::class, $config
            );
        });
    }
}