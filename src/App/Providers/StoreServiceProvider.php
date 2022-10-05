<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Stores\Commands\LoadProductInfoCommand;
use App\Console\Stores\Commands\SearchForStockCommand;
use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            AmazonCanadaService::class,
            BestBuyCanadaService::class,
            NeweggCanadaService::class,
        ], 'stores');

        $this->app->when(FulfillTrackingRequestAction::class)
            ->needs('$storeServices')
            ->giveTagged('stores');

        $this->commands([
            SearchForStockCommand::class,
            LoadProductInfoCommand::class,
        ]);
    }

    public function boot(): void
    {
    }
}
