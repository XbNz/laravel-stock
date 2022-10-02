<?php

namespace Tests\Unit\Domain\Stocks\Actions;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Domain\Stocks\Actions\CreateOrUpdateStocksForTrackingRequestAction;
use Domain\Stores\Collections\StockDataCollection;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use Domain\TrackingRequests\Models\TrackingRequest;
use File;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class CreateOrUpdateStocksForTrackingRequestActionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test **/
    public function given_a_stock_search_data_dto_and_a_tracking_request_model_it_creates_new_stocks_for_the_tracking_request(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();

        $image = imagecreate(200, 200);
        imagejpeg($image, storage_path('app/tmp/test.jpg'));

        $searchData = new StockSearchData(
            new Uri('https://www.example.com'),
            StockDataCollection::make(
                [
                    StockData::generateFake([
                        'title' => '::random-title::',
                        'link' => new Uri('https://example.com/skuhere'),
                        'price' => new Price(111, Arr::random(Currency::cases())),
                        'available' => true,
                        'sku' => '::random-sku::',
                    ])
                ]
            ),
            storage_path('app/tmp/test.jpg')
        );

        // Act

        app(CreateOrUpdateStocksForTrackingRequestAction::class)($searchData, $trackingRequest);

        // Assert

        $this->assertDatabaseHas('stocks', [
            'title' => '::random-title::',
            'url' => 'https://example.com/skuhere',
            'price' => 111,
            'availability' => true,
            'sku' => '::random-sku::',
            'image' => storage_path('app/tmp/test.jpg'),
        ]);
    }

    /** @test **/
    public function given_a_stock_data_dto_and_a_tracking_request_model_it_creates_a_new_stock_for_the_tracking_request(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();

        $stockData = StockData::generateFake([
            'title' => '::random-title::',
            'link' => new Uri('https://example.com/skuhere'),
            'price' => new Price(111, Arr::random(Currency::cases())),
            'available' => true,
            'sku' => '::random-sku::',
        ]);

        // Act

        app(CreateOrUpdateStocksForTrackingRequestAction::class)($stockData, $trackingRequest);

        // Assert

        $this->assertDatabaseHas('stocks', [
            'title' => '::random-title::',
            'url' => 'https://example.com/skuhere',
            'price' => 111,
            'availability' => true,
            'sku' => '::random-sku::',
            'image' => storage_path('app/tmp/test.jpg'),
        ]);
    }

    /** @test **/
    public function if_the_stock_is_already_there_it_just_updates_it(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();

        $sharedScore = Arr::random(Store::cases());

        $searchStocks = new StockSearchData(
            new Uri('https://www.example.com'),
            StockDataCollection::make([StockData::generateFake([
                'sku' => '::random-sku::',
                'title' => '::first::',
                'store' => $sharedScore,
            ])]),
            storage_path('app/tmp/test.jpg'),
        );

        $productStock = StockData::generateFake([
            'sku' => '::random-sku::',
            'title' => '::second::',
            'store' => $sharedScore,
        ]);

        // Act

        app(CreateOrUpdateStocksForTrackingRequestAction::class)($searchStocks, $trackingRequest);

        $this->assertDatabaseHas('stocks', [
            'sku' => '::random-sku::',
            'title' => '::first::',
        ]);

        app(CreateOrUpdateStocksForTrackingRequestAction::class)($productStock, $trackingRequest);

        $this->assertDatabaseHas('stocks', [
            'sku' => '::random-sku::',
            'title' => '::second::',
        ]);

        $this->assertDatabaseCount('stocks', 1);
    }

    /** @test **/
    public function it_touches_the_tracking_request_updated_at(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();
        $stockData = StockData::generateFake();
        $this->travel(3)->days();

        // Act
        app(CreateOrUpdateStocksForTrackingRequestAction::class)($stockData, $trackingRequest);

        // Assert
        $this->assertTrue($trackingRequest->fresh()->updated_at->isToday());
    }
}
