<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stocks\QueryBuilders;

use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistoryQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function where_has_stock_works(): void
    {
        // Arrange
        Stock::factory()->count(100)->create();
        $stockHistory = StockHistory::factory()->for(Stock::factory())->create();

        // Act
        $result = StockHistory::query()->whereHasStock($stockHistory->stock);

        // Assert
        $this->assertEquals($stockHistory->id, $result->first()->id);
    }
}
