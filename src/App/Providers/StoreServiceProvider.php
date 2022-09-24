<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application;
use App\Console\Stores\Commands\LoadProductInfoCommand;
use App\Console\Stores\Commands\SearchForStockCommand;
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
        $this->commands([
            SearchForStockCommand::class,
            LoadProductInfoCommand::class,
        ]);
    }

    public function boot(): void
    {
    }
}
