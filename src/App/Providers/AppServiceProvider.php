<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Alerts\Models\TrackingAlert;
use Domain\Browser\Browser;
use Domain\Browser\PythonUndetectedChrome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\Uuid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Browser::class, function (Application $app) {
            return new PythonUndetectedChrome();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Model::preventLazyLoading();
    }
}
