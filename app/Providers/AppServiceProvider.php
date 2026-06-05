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
        $this->app->singleton(\App\Services\Safety\RiskGuard::class, function ($app) {
            return new \App\Services\Safety\RiskGuard();
        });

        $this->app->singleton('xray.manager', function ($app) {
            return new \App\Services\Xray\XrayManager(
                new \App\Services\Xray\XrayConfigRenderer(),
                new \App\Services\Xray\XrayValidator(),
                $app->make(\App\Services\Safety\RiskGuard::class)
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
        \App\Models\Node::observe(\App\Observers\NodeObserver::class);
    }
}
