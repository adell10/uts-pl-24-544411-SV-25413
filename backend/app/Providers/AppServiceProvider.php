<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APCng;
use Prometheus\Storage\InMemory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
        public function register(): void
    {
        // Satu registry metrik untuk seluruh aplikasi.
        // Di container: APCu → angka tersimpan di memori bersama dan bertahan antar-request.
        // Di laptop / CI (tanpa APCu): InMemory, cukup untuk test.
        $this->app->singleton(CollectorRegistry::class, function () {
            $storage = (extension_loaded('apcu') && apcu_enabled())
                ? new APCng('cafehopper_')
                : new InMemory();

            return new CollectorRegistry($storage, false);
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
