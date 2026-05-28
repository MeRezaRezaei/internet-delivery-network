<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\IDN\Technitium\TechnitiumClient;

class TechnitiumServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TechnitiumClient::class, function ($app) {
            $config = $app['config']['technitium'];
            
            return new TechnitiumClient(
                baseUrl: $config['url'],
                token: $config['token'],
                username: $config['username'],
                password: $config['password']
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
