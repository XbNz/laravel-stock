<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TrackingRequests\Actions;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\InferTrackingTypeForStoreAction;
use Domain\TrackingRequests\Enums\TrackingRequest;
use Generator;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class InferTrackingTypeForStoreActionTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     **/
    public function given_a_store_and_url_it_infers_the_tracking_type(string $url, Store $store, TrackingRequest $expectedTrackingRequest): void
    {
        // Arrange
        $action = app(InferTrackingTypeForStoreAction::class);

        // Act
        $trackingRequest = ($action)($store, new Uri($url));

        // Assert
        $this->assertSame($expectedTrackingRequest, $trackingRequest);
    }

    public function dataProvider(): Generator
    {
        yield from [
            'amazon_canada_single_product' => [
                'url' => 'https://www.amazon.ca/dp/B07QQQQQQQQ',
                'store' => Store::AmazonCanada,
                'expectedTrackingType' => TrackingRequest::SingleProduct,
            ],
            'best_buy_canada_single_product' => [
                'url' => 'https://www.bestbuy.ca/en-ca/product/xerox-013r00669-workcentre-5945-5955-print-cartridge-90000-yield/11378395',
                'store' => Store::BestBuyCanada,
                'expectedTrackingType' => TrackingRequest::SingleProduct,
            ],
            'newegg_canada_single_product' => [
                'url' => 'https://www.newegg.ca/wavlink-umd03-black-gray/p/1DN-0023-00078?Item=9SIACU9EVM9524&cm_sp=Homepage_SS-_-P1_9SIACU9EVM9524-_-08152022',
                'store' => Store::NeweggCanada,
                'expectedTrackingType' => TrackingRequest::SingleProduct,
            ],
            'amazon_canada_multiple_products' => [
                'url' => 'https://www.amazon.ca/',
                'store' => Store::AmazonCanada,
                'expectedTrackingType' => TrackingRequest::Search,
            ],
            'best_buy_canada_multiple_products' => [
                'url' => 'https://www.bestbuy.ca/en-ca/',
                'store' => Store::BestBuyCanada,
                'expectedTrackingType' => TrackingRequest::Search,
            ],
            'newegg_canada_multiple_products' => [
                'url' => 'https://www.newegg.ca/',
                'store' => Store::NeweggCanada,
                'expectedTrackingType' => TrackingRequest::Search,
            ],
        ];
    }
}
