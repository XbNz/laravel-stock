<?php

declare(strict_types=1);

namespace App\Console\Stores\Commands;

use Domain\Stores\Enums\Store;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Support\Contracts\StoreContract;

class SearchForStockCommand extends Command
{
    protected $signature = 'stock:search {store} {--links=}';

    protected $description = 'Command description';

    public function handle()
    {
        $store = Store::from($this->argument('store'));

        /** @var StoreContract $service */
        $service = app($store->serviceFqcn());

        $this->info("Retrieving items from {$this->argument('store')}");

        $links = Collection::make($this->option('links'))->map(fn (string $link) => new Uri($link));

        $searchData = $service->search($links->toArray());

        foreach ($searchData as $search) {
            dd($search);
        }
    }
}
