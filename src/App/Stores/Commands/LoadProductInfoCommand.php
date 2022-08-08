<?php

declare(strict_types=1);

namespace App\Stores\Commands;

use Domain\Stores\Enums\Store;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Support\Contracts\StoreContract;

class LoadProductInfoCommand extends Command
{
    protected $signature = 'stock:load {store} {--link=}';

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

        $stock = $service->product(
            new Uri($this->option('link'))
        );

        File::put($path, $stock->image, 'w+');

        $this->table(['Title', 'Link', 'Price', 'SKU', 'Availability'], [
            [
                Str::limit($stock->title, 30),
                $stock->link,
                $stock->price,
                $stock->sku,
                $stock->available ? '✅' : '❌',
            ],
        ]);
        $this->info("Saving image to {$path}");
    }
}
