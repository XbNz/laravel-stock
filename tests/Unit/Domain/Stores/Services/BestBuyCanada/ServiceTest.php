<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stores\Services\BestBuyCanada;

use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\UriInterface;
use Tests\TestCase;
use Tests\Unit\Domain\Stores\Services\StoreContractTests;

class ServiceTest extends TestCase
{
    use StoreContractTests;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set(['store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.proxy' => false]);
    }

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
            new Uri('https://www.bestbuy.ca/en-ca/search?search=fan'),
            new Uri('https://www.bestbuy.ca/en-ca/search?search=tablet'),
        ])->random();
    }
}
