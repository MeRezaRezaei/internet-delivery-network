<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class IDNServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/idn.php'), 'idn'
        );

        $this->app->singleton(\App\Services\ControlPlane\DryRunService::class, function ($app) {
            return new \App\Services\ControlPlane\DryRunService();
        });

        $this->app->singleton(\App\Services\ControlPlane\ControlPlaneManager::class, function ($app) {
            return new \App\Services\ControlPlane\ControlPlaneManager(
                $app->make(\App\Services\ControlPlane\DryRunService::class)
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
