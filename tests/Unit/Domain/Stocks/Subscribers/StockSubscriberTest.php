<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stocks\Subscribers;

use Domain\Stocks\Actions\CreateHistoryForStockAction;
use Domain\Stocks\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockSubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function stock_history_action_is_invoked_upon_update_of_stock(): void
    {
        // Arrange
        $stock = Stock::factory()->create();
        $this->travel(1)->minutes();
        $actionMock = $this->mock(CreateHistoryForStockAction::class);
        $actionMock->shouldReceive('__invoke')->once();

        // Act
        $stock->touch();
    }

    /** @test **/
    public function updating_a_stock_doesnt_create_history_for_a_stock_with_either_a_null_price_or_availability_field(): void
    {
        // Arrange
        $stock = Stock::factory()->create([
            'price' => 100,
            'availability' => null,
        ]);
        $this->travel(1)->minutes();
        $actionMock = $this->mock(CreateHistoryForStockAction::class);
        $actionMock->shouldReceive('__invoke')->never();

        // Act
        $stock->touch();

    }

    /** @test **/
    public function creating_a_stock_doesnt_create_history_for_a_stock_with_either_a_null_price_or_availability_field(): void
    {
        $actionMock = $this->mock(CreateHistoryForStockAction::class);
        $actionMock->shouldReceive('__invoke')->never();
        $stock = Stock::factory()->create([
            'price' => 100,
            'availability' => null,
        ]);
    }

    /** @test **/
    public function stock_history_action_is_invoked_upon_creation_of_stock(): void
    {
        $actionMock = $this->mock(CreateHistoryForStockAction::class);
        $actionMock->shouldReceive('__invoke')->once();
        $d = Stock::factory()->create();
    }
}
