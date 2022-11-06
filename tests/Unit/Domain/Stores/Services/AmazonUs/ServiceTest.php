<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stores\Services\AmazonUs;

use Domain\Stores\Services\AmazonUs\AmazonUsService;
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
        Config::set([
            'store.Domain\Stores\Services\AmazonUs\AmazonUsService.proxy' => false,
        ]);
    }

    public function getStoreImplementation(): string
    {
        return AmazonUsService::class;
    }

    public function randomSearchLinkForStore(): UriInterface
    {
        return Collection::make([
            new Uri('https://www.amazon.com/s?k=laptop'),
            new Uri('https://www.amazon.com/s?k=computer'),
            new Uri('https://www.amazon.com/s?k=slippers'),
            new Uri('https://www.amazon.com/s?k=mask'),
            new Uri('https://www.amazon.com/s?k=running shoes'),
            new Uri('https://www.amazon.com/s?k=cpu'),
            new Uri('https://www.amazon.com/s?k=motherboard'),
        ])->random();
    }
}
