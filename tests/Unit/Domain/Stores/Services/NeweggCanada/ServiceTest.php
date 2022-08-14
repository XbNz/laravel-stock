<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stores\Services\NeweggCanada;

use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Psr\Http\Message\UriInterface;
use Tests\TestCase;
use Tests\Unit\Domain\Stores\Services\StoreContractTests;

class ServiceTest extends TestCase
{
    use StoreContractTests;

    public function getStoreImplementation(): string
    {
        return NeweggCanadaService::class;
    }

    public function randomSearchLinkForStore(): UriInterface
    {
        return Collection::make([
            new Uri('https://www.newegg.ca/p/pl?d=laptop'),
            new Uri('https://www.newegg.ca/p/pl?d=graphics card'),
            new Uri('https://www.newegg.ca/p/pl?d=mouse'),
            new Uri('https://www.newegg.ca/p/pl?d=monitor'),
            new Uri('https://www.newegg.ca/p/pl?d=tablet'),
            new Uri('https://www.newegg.ca/p/pl?d=controller'),
            new Uri('https://www.newegg.ca/p/pl?d=keyboard'),
        ])->random();
    }
}
