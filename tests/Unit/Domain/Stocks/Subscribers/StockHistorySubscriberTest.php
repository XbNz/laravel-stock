<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Stocks\Subscribers;

use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Actions\DispatchStockHistoryNotificationAction;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StockHistorySubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function when_a_new_history_is_created_it_must_call_the_dispatch_action(): void
    {
        // Arrange, Act & Assert
        Notification::fake();
        $actionMock = $this->mock(DispatchStockHistoryNotificationAction::class);
        $actionMock->shouldReceive('__invoke')->once();

        $subjectStock = Stock::factory()->createQuietly();

        $oldHistory = StockHistory::factory()->createQuietly([
            'price' => 150,
            'availability' => false,
            'created_at' => now()->subDays(1),
        ]);

        $newestHistoricRecord = StockHistory::factory()->createQuietly([
            'price' => 100,
            'availability' => true,
            'created_at' => now(),
        ]);

        $subjectStock->histories()->saveMany([$oldHistory, $newestHistoricRecord]);

        $this->travel(1)->minutes();
        $subjectStock->touch();

        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        $trackingAlert = TrackingAlert::factory()->create([
            'percentage_trigger' => 10,
            'availability_trigger' => true,
            'alert_channel_id' => $alertChannel,
        ]);
        $trackingRequest = TrackingRequest::factory()->create();

        $trackingRequest->stocks()->attach($subjectStock);
        $trackingRequest->trackingAlerts()->attach($trackingAlert);
    }
}
