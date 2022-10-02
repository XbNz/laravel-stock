<?php

namespace Tests\Unit\Domain\Stocks\Subscribers;

use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistorySubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function when_a_new(): void
    {
        // Arrange
        StockHistory::factory()->for(Stock::factory())->count(10)->create();
        $newestHistoricRecord = StockHistory::factory()->newModel();
        Stock::query()->sole()->histories()->save($newestHistoricRecord);

        // assert action received instance...

        // Act

        // Assert
    }
}
