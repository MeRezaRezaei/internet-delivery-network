<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('xray.manager', function ($app) {
            return new \App\Services\Xray\XrayManager(
                new \App\Services\Xray\XrayConfigRenderer(),
                new \App\Services\Xray\XrayValidator()
            );
        });

        $this->app->singleton('technitium', function ($app) {
            return new \App\Services\ControlPlane\TechnitiumService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
