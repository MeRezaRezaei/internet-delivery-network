<?php

namespace App\Providers;

use App\Services\Xray\XrayManager;
use Illuminate\Support\ServiceProvider;

class XrayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('xray', function ($app) {
            return new XrayManager();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                base_path('config/xray.php') => config_path('xray.php'),
            ], 'xray-config');
        }
    }
}
