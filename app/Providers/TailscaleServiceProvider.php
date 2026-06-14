<?php

namespace App\Providers;

use App\Services\Tailscale\TailscaleService;
use Illuminate\Support\ServiceProvider;

class TailscaleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('tailscale', function ($app) {
            return new TailscaleService();
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
