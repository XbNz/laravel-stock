<?php

namespace App\Stores\Commands;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Store;
use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Support\Contracts\StoreContract;

class SearchForStockCommand extends Command
{
    protected $signature = 'stock:search {store} {--term=}';

    protected $description = 'Command description';

    public function handle()
    {
        $store = Store::from($this->argument('store'));

        /** @var StoreContract $service */
        $service = app($store->serviceFqcn());

        $this->info("Searching for {$this->option('term')} on {$this->argument('store')}");
        $searchData = $service->search($this->option('term'));

        $path = storage_path();
        $path .= '/';
        $path .= Config::get('store.' . $store->serviceFqcn() . '.image_prefix');
        $path .= Str::random(10);
        $path .= '.';
        $path .= Config::get('store.' . $store->serviceFqcn() . '.image_format');


        File::put($path, $searchData->image, 'w+');

        $stocksToDisplay = $searchData->stocks
            ->take(10)
            ->sortBy('price')
            ->map(fn(StockData $stock) => (array) $stock)
            ->map(function (array $stock) {
                return array_merge($stock, [
                    'title' => $stock['title'] = Str::limit($stock['title'], 30)
                ]);
            })
            ->map(fn(array $stock) => Arr::only($stock, ['title', 'price', 'sku', 'link']));

        $this->table(['Title', 'Link', 'Price', 'SKU'], $stocksToDisplay->toArray());
        $this->info("Found {$searchData->stocks->count()} results");
        $this->info("Saving image to {$path}");
    }
}
