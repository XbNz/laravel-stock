<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application;
use App\Stores\Commands\LoadProductInfoCommand;
use App\Stores\Commands\SearchForStockCommand;
use Domain\Stores\Factories\BrowserShotFactory;
use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\AmazonCanada\Mappers\ProductMapper;
use Domain\Stores\Services\AmazonCanada\Mappers\SearchMapper;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
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

        $this->app->bind(BestBuyCanadaService::class, fn (Application $app) => new BestBuyCanadaService(
            $app->make(BrowserShotFactory::class)->for(BestBuyCanadaService::class),
            $app->make(\Domain\Stores\Services\BestBuyCanada\Mappers\ProductMapper::class),
            $app->make(\Domain\Stores\Services\BestBuyCanada\Mappers\SearchMapper::class),
        ));

        $this->app->bind(NeweggCanadaService::class, fn (Application $app) => new NeweggCanadaService(
            $app->make(BrowserShotFactory::class)->for(NeweggCanadaService::class),
            $app->make(\Domain\Stores\Services\NeweggCanada\Mappers\ProductMapper::class),
            $app->make(\Domain\Stores\Services\NeweggCanada\Mappers\SearchMapper::class),
        ));

        $this->commands([
            SearchForStockCommand::class,
            LoadProductInfoCommand::class,
        ]);
    }

    public function boot(): void
    {
    }
}
