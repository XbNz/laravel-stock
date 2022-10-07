<?php

declare(strict_types=1);

namespace App\Console\Stores\Commands;

use Domain\Stores\Enums\Store;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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

        $links = Collection::make($this->option('links'))->map(fn (string $link) => new Uri($link));

        $stockData = $service->product($links->toArray());

        foreach ($stockData as $stock) {
            $this->table(
                ['Title', 'Link', 'Store', 'Price', 'Availability', 'SKU', 'Image'],
                [
                    [
                        'title' => Str::of($stock->title)->limit(15),
                        'link' => Str::of($stock->link)->limit(15),
                        'store' => $stock->store->value,
                        'price' => $stock->price,
                        'availability' => $stock->available,
                        'sku' => $stock->sku,
                        'image' => $stock->imagePath,
                    ],
                ]
            );
        }
    }
}
