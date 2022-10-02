<?php

namespace Tests\Unit\Domain\Stocks\Actions;

use Domain\Stocks\Actions\CreateHistoryForStockAction;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateHistoryForStockActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function given_a_stock_it_creates_new_historic_record_if_a_change_to_price_or_availability_has_occurred_or_if_the_stock_is_new(): void
    {
        // Arrange
        $stock = Stock::factory()->create();

        // Act
        app(CreateHistoryForStockAction::class)($stock);

        // Assert
        $this->assertDatabaseHas('stock_histories', [
            'stock_id' => $stock->id,
            'price' => $stock->getRawOriginal('price'),
            'availability' => $stock->availability,
        ]);
    }

    /** @test **/
    public function if_the_latest_historic_record_of_a_stock_is_identical_in_price_and_availability_to_the_stock_itself_no_new_record_is_added(): void
    {
        // Arrange
        $stock = Stock::factory()->create();

        // Act
        app(CreateHistoryForStockAction::class)($stock);
        $this->travel(2)->days();
        app(CreateHistoryForStockAction::class)($stock);

        // Assert
        $this->assertDatabaseCount('stock_histories', 1);
        $this->assertFalse($stock->histories()->sole()->updated_at->isToday());
    }

    /** @test **/
    public function if_price_changes_it_makes_a_new_record(): void
    {
        // Arrange
        $stock = Stock::factory()
            ->has(StockHistory::factory(state: ['price' => 100, 'created_at' => now()->subDay()]), 'histories')
            ->create(['price' => 200]);

        // Act
        app(CreateHistoryForStockAction::class)($stock);

        // Assert
        $this->assertDatabaseCount('stock_histories', 2);
        $this->assertSame(200, $stock->histories()->latest()->first()->getRawOriginal('price'));
    }

    /** @test **/
    public function if_availability_changes_it_makes_a_new_record(): void
    {
        // Arrange
        $stock = Stock::factory()
            ->has(StockHistory::factory(state: ['availability' => true, 'created_at' => now()->subDay()]), 'histories')
            ->create(['availability' => false]);

        // Act
        app(CreateHistoryForStockAction::class)($stock);

        // Assert
        $this->assertDatabaseCount('stock_histories', 2);
        $this->assertFalse($stock->histories()->latest()->first()->availability);
    }
}
