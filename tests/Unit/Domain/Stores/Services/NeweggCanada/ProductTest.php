<?php

namespace Tests\Unit\Domain\Stores\Services\NeweggCanada;

use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use GuzzleHttp\Psr7\Uri;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /** @test **/
    public function it_fetches_a_product_from_amazon_using_a_url(): void
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
