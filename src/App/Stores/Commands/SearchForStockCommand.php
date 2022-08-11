<?php

declare(strict_types=1);

namespace App\Stores\Commands;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Store;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Support\Contracts\StoreContract;

class SearchForStockCommand extends Command
{
    protected $signature = 'stock:search {store} {--link=}';

    protected $description = 'Command description';

    public function handle()
    {
        $store = Store::from($this->argument('store'));

        /** @var StoreContract $service */
        $service = app($store->serviceFqcn());

        $this->info("Searching on {$this->argument('store')}");
        $searchData = $service->search(new Uri($this->option('link')));

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
            ->map(fn (StockData $stock) => (array) $stock)
            ->map(function (array $stock) {
                return array_merge($stock, [
                    'title' => $stock['title'] = Str::limit($stock['title'], 30),
                ]);
            })
            ->map(fn (array $stock) => Arr::only($stock, ['title', 'price', 'sku', 'link']));

        $this->table(['Title', 'Link', 'Price', 'SKU'], $stocksToDisplay->toArray());
        $this->info("Found {$searchData->stocks->count()} results");
        $this->info("Saving image to {$path}");
    }
}
