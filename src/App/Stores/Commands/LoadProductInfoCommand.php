<?php

declare(strict_types=1);

namespace App\Stores\Commands;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Store;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Fork\Fork;
use Support\Contracts\StoreContract;

class LoadProductInfoCommand extends Command
{
    protected $signature = 'stock:load {store} {--links=*}';

    protected $description = 'Command description';

    public function handle()
    {
        $store = Store::from($this->argument('store'));

        /** @var StoreContract $service */
        $service = app($store->serviceFqcn());

        $this->info("Retrieving item from {$this->argument('store')}");

        $path = storage_path();
        $path .= '/';
        $path .= Config::get('store.' . $store->serviceFqcn() . '.image_prefix');
        $path .= Str::random(10);
        $path .= '.';
        $path .= Config::get('store.' . $store->serviceFqcn() . '.image_format');

        $functions = [];

        foreach ($this->option('links') as $link) {
            $functions[] = fn () => serialize($service->product(new Uri($link)));
        }

        $stockData = Fork::new()
            ->concurrent(10)
            ->run(...$functions);


        foreach ($stockData as $stockDatum) {
            $stockDatum = unserialize($stockDatum);

            $this->table(['Title', 'Link', 'Price', 'SKU', 'Availability'], [
                [
                    Str::limit($stockDatum->title, 30),
                    $stockDatum->link,
                    $stockDatum->price,
                    $stockDatum->sku,
                    $stockDatum->available ? '✅' : '❌',
                ],
            ]);

            $this->info("Saving image to {$stockDatum->image}");
        }



    }
}
