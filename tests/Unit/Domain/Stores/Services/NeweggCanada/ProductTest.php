<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stores\Services\NeweggCanada;

use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use GuzzleHttp\Psr7\Uri;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function testItFetchesAProductFromAmazonUsingAUrl(): void
    {
        // Arrange
        $url = new Uri('https://www.newegg.ca/p/N82E16824160482');

        // Act
        $neweggService = app(NeweggCanadaService::class);

        // Assert

        dd(
            $neweggService->search($url)
        );
    }
}
