<?php

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
}
