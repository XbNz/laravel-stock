<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Stores\Commands\DiscoverTrackingRequestsCommand;
use App\Console\Stores\Commands\RecycleTempFolderCommand;
use App\Console\Stores\Commands\RefreshGluetunServerCommand;
use Domain\Browser\Browser;
use Domain\Browser\PythonUndetectedChrome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

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

        $this->commands([
            DiscoverTrackingRequestsCommand::class,
            RefreshGluetunServerCommand::class,
            RecycleTempFolderCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        //        Model::preventLazyLoading();
    }
}
