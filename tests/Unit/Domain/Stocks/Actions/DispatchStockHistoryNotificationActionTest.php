<?php

namespace Tests\Unit\Domain\Stocks\Actions;

use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Actions\DispatchStockHistoryNotificationAction;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Domain\Stocks\Notifications\StockAvailabilityNotification;
use Domain\Stocks\Notifications\StockPriceNotification;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class DispatchStockHistoryNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function it_dispatches_price_change_notification(): void
    {
        // Arrange
        Notification::fake();

        $subjectStock = Stock::factory()->create();

        $oldestHistory = StockHistory::factory()->create(['price' => 100, 'created_at' => now()->subDays(2)]);
        $oldHistory = StockHistory::factory()->create(['price' => 200, 'created_at' => now()->subDays(1)]);
        $priceThatIsHigherThanOldHistoryAButLowerThanOldHistoryB = 150;

        $newestHistoricRecord = StockHistory::factory()->create([
            'price' => $priceThatIsHigherThanOldHistoryAButLowerThanOldHistoryB,
            'created_at' => now(),
        ]);

        $subjectStock->histories()->saveMany([$oldestHistory, $oldHistory, $newestHistoricRecord]);

        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel();
        $trackingAlert = TrackingAlert::factory()->create(['percentage_trigger' => 25, 'alert_channel_id' => $alertChannel]);
        $trackingRequest = TrackingRequest::factory()->create();

        $trackingRequest->stocks()->attach($subjectStock);
        $trackingRequest->trackingAlerts()->attach($trackingAlert);

        // Act
        app(DispatchStockHistoryNotificationAction::class)($newestHistoricRecord);

        // Assert
        Notification::assertSentTo(
            $trackingAlert->alertChannel,
            StockPriceNotification::class,
            function (StockPriceNotification $notification) use ($oldHistory, $newestHistoricRecord) {
                $this->assertTrue(invade($notification)->previous->is($oldHistory));
                $this->assertTrue(invade($notification)->current->is($newestHistoricRecord));
                return true;
            }
        );
    }

    /** @test **/
    public function it_dispatches_availability_change_notification(): void
    {
        // Arrange
        Notification::fake();

        $subjectStock = Stock::factory()->create();

        $oldestHistory = StockHistory::factory()->create([
            'price' => 100,
            'availability' => true,
            'created_at' => now()->subDays(2)
        ]);
        $oldHistory = StockHistory::factory()->create([
            'price' => 100,
            'availability' => false,
            'created_at' => now()->subDays(1)
        ]);

        $newestHistoricRecord = StockHistory::factory()->create([
            'price' => 100,
            'availability' => true,
            'created_at' => now(),
        ]);

        $subjectStock->histories()->saveMany([$oldestHistory, $oldHistory, $newestHistoricRecord]);

        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        $trackingAlert = TrackingAlert::factory()->create([
            'percentage_trigger' => null,
            'availability_trigger' => true,
            'alert_channel_id' => $alertChannel
        ]);
        $trackingRequest = TrackingRequest::factory()->create();

        $trackingRequest->stocks()->attach($subjectStock);
        $trackingRequest->trackingAlerts()->attach($trackingAlert);

        // Act
        app(DispatchStockHistoryNotificationAction::class)($newestHistoricRecord);

        // Assert
        Notification::assertSentTo(
            $trackingAlert->alertChannel,
            StockAvailabilityNotification::class,
            function (StockAvailabilityNotification $notification) use ($newestHistoricRecord) {
                $this->assertTrue(invade($notification)->current->is($newestHistoricRecord));
                return true;
            }
        );
    }

    /** @test **/
    public function it_dispatches_both(): void
    {
        // Arrange
        Notification::fake();

        $subjectStock = Stock::factory()->create();

        $oldHistory = StockHistory::factory()->create([
            'price' => 150,
            'availability' => false,
            'created_at' => now()->subDays(1)
        ]);

        $newestHistoricRecord = StockHistory::factory()->create([
            'price' => 100,
            'availability' => true,
            'created_at' => now(),
        ]);

        $subjectStock->histories()->saveMany([$oldHistory, $newestHistoricRecord]);

        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        $trackingAlert = TrackingAlert::factory()->create([
            'percentage_trigger' => 10,
            'availability_trigger' => true,
            'alert_channel_id' => $alertChannel
        ]);
        $trackingRequest = TrackingRequest::factory()->create();

        $trackingRequest->stocks()->attach($subjectStock);
        $trackingRequest->trackingAlerts()->attach($trackingAlert);

        // Act
        app(DispatchStockHistoryNotificationAction::class)($newestHistoricRecord);

        // Assert
        Notification::assertSentTo($trackingAlert->alertChannel, StockPriceNotification::class);
        Notification::assertSentTo($trackingAlert->alertChannel, StockAvailabilityNotification::class);
    }

    /** @test **/
    public function if_nothing_changes_nothing_dispatches(): void
    {
        // Arrange
        Notification::fake();

        $subjectStock = Stock::factory()->create();

        $oldHistory = StockHistory::factory()->create([
            'price' => 100,
            'availability' => false,
            'created_at' => now()->subDays(1)
        ]);

        $newestHistoricRecord = StockHistory::factory()->create([
            'price' => 100,
            'availability' => false,
            'created_at' => now(),
        ]);

        $subjectStock->histories()->saveMany([$oldHistory, $newestHistoricRecord]);

        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        $trackingAlert = TrackingAlert::factory()->create([
            'percentage_trigger' => 0,
            'availability_trigger' => true,
            'alert_channel_id' => $alertChannel
        ]);
        $trackingRequest = TrackingRequest::factory()->create();

        $trackingRequest->stocks()->attach($subjectStock);
        $trackingRequest->trackingAlerts()->attach($trackingAlert);

        // Act
        app(DispatchStockHistoryNotificationAction::class)($newestHistoricRecord);

        // Assert
        Notification::assertNothingSent();
    }

    /** @test **/
    public function if_alert_channel_is_not_verified_it_doesnt_send(): void
    {
        // Arrange
        Notification::fake();

        $subjectStock = Stock::factory()->create();

        $oldHistory = StockHistory::factory()->create([
            'price' => 150,
            'availability' => false,
            'created_at' => now()->subDays(1)
        ]);

        $newestHistoricRecord = StockHistory::factory()->create([
            'price' => 100,
            'availability' => true,
            'created_at' => now(),
        ]);

        $subjectStock->histories()->saveMany([$oldHistory, $newestHistoricRecord]);

        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create(['verified_at' => null]);
        $trackingAlert = TrackingAlert::factory()->create([
            'percentage_trigger' => 10,
            'availability_trigger' => true,
            'alert_channel_id' => $alertChannel
        ]);
        $trackingRequest = TrackingRequest::factory()->create();

        $trackingRequest->stocks()->attach($subjectStock);
        $trackingRequest->trackingAlerts()->attach($trackingAlert);

        // Act
        app(DispatchStockHistoryNotificationAction::class)($newestHistoricRecord);

        // Assert
        Notification::assertNothingSent();
    }
}
