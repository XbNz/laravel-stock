<?php

namespace Tests\Unit\Domain\Stocks\Actions;

use Domain\Stocks\Actions\DispatchStockHistoryNotificationAction;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DispatchStockHistoryNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function it_dispatches_the_appropriate_notification_given_the_change_in_price_or_availability(): void
    {
        // Arrange
        Notification::fake();

        $subjectStock = Stock::factory()->create();

        $oldHistoryA = StockHistory::factory()->create();
        $oldHistoryB = StockHistory::factory()->create();
        $newestHistoricRecord = StockHistory::factory()->create();

        $subjectStock->histories()->saveMany([$oldHistoryA, $oldHistoryB, $newestHistoricRecord]);

        // Act
        app(DispatchStockHistoryNotificationAction::class)($newestHistoricRecord);

        // Assert
    }

    /** @test **/
    public function the_first_historic_record_is_always_ignored(): void
    {
        // Arrange

        // Act

        // Assert
    }
}
