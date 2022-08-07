<?php

namespace Tests\Unit\Domain\Stores\Services\BestBuyCanada;

use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use GuzzleHttp\Psr7\Uri;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /** @test **/
    public function it_fetches_a_product_from_amazon_using_a_url(): void
    {
        // Arrange
        $url = new Uri('https://www.bestbuy.ca/en-ca/product/citizen-crystal-42mm-men-s-solar-powered-chronograph-dress-watch-w-swarovski-crystals-silver-black/14611463');

        // Act
        $bestbuyService = app(BestBuyCanadaService::class);

        // Assert

        dd(
            $bestbuyService->search('3080')
        );
    }
}
