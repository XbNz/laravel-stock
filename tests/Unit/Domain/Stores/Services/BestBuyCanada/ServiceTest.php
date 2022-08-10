<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stores\Services\BestBuyCanada;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Store;
use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Psr\Http\Message\UriInterface;
use Support\Contracts\StoreContract;
use Tests\TestCase;
use Tests\Unit\Domain\Stores\Services\StoreContractTests;

class ServiceTest extends TestCase
{
    use StoreContractTests;

    public function getStoreImplementation(): string
    {
        return BestBuyCanadaService::class;
    }

    public function randomSearchLinkForStore(): UriInterface
    {
        return Collection::make([
            new Uri('https://www.bestbuy.ca/en-ca/search?search=television'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=computer'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=laptop'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=graphics card'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=monitor'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=mouse'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=tablet'),
        ])->random();
    }
}
