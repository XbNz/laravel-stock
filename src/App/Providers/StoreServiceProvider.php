<?php

namespace App\Providers;

use App\Application;
use App\Stores\Commands\SearchForStockCommand;
use App\Stores\GetProductInfoCommand;
use Domain\Stores\Factories\BrowserShotFactory;
use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\AmazonCanada\Mappers\ProductMapper;
use Domain\Stores\Services\AmazonCanada\Mappers\SearchMapper;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AmazonCanadaService::class, fn (Application $app) => new AmazonCanadaService(
            $app->make(BrowserShotFactory::class)->for(AmazonCanadaService::class),
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
