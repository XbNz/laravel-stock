<?php

namespace App\Providers;

use App\Application;
use App\Stores\Commands\SearchForStockCommand;
use App\Stores\GetProductInfoCommand;
use Domain\Stores\Factories\BrowserShotFactory;
use Domain\Stores\Services\Amazon\AmazonService;
use Domain\Stores\Services\Amazon\Mappers\ProductMapper;
use Domain\Stores\Services\Amazon\Mappers\SearchMapper;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AmazonService::class, fn (Application $app) => new AmazonService(
            $app->make(BrowserShotFactory::class)->for(AmazonService::class),
            $app->make(ProductMapper::class),
            $app->make(SearchMapper::class),
        ));

        $this->commands([
            SearchForStockCommand::class,
        ]);
    }

    public function boot(): void
    {
    }
}
