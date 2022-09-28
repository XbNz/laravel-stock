<?php

namespace Tests\Unit\Domain\TrackingRequests\Actions;

use Domain\Alerts\Models\AlertChannel;
use Domain\TrackingRequests\Actions\SendTrackingRequestFailedNotificationAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Notifications\TrackingRequestFailedNotification;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Tests\TestCase;

class SendTrackingRequestFailedNotificationActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function it_sends_an_alert_to_the_supplied_channel(): void
    {
        // Arrange
        Notification::fake();
        $trackingRequest = TrackingRequest::factory()->create();
        $alertChannel = AlertChannel::factory()->for($trackingRequest->user)
            ->verificationRequiredChannel()
            ->create(['verified_at' => now()]);

        // Act
        app(SendTrackingRequestFailedNotificationAction::class)(
            $trackingRequest,
            $alertChannel,
        );

        // Assert
        Notification::assertSentTo(
            $alertChannel,
            TrackingRequestFailedNotification::class,
            function (TrackingRequestFailedNotification $notification) use ($trackingRequest) {
                return invade($notification)->trackingRequest->is($trackingRequest);
            }
        );
    }

    /** @test **/
    public function if_the_channel_requires_verification_it_must_be_verified(): void
    {
        // Arrange
        Notification::fake();
        $trackingRequest = TrackingRequest::factory()->create();
        $alertChannel = AlertChannel::factory()->for($trackingRequest->user)
            ->verificationRequiredChannel()
            ->create(['verified_at' => null]);

        // Act & Assert

        $this->expectException(InvalidArgumentException::class);

        app(SendTrackingRequestFailedNotificationAction::class)(
            $trackingRequest,
            $alertChannel,
        );

    }

    /** @test **/
    public function the_alert_channel_must_be_owned_by_the_tracking_request_user(): void
    {
        // Arrange
        Notification::fake();
        $randomUser = User::factory()->create();
        $trackingRequest = TrackingRequest::factory()->create();
        $alertChannel = AlertChannel::factory()->for($randomUser)
            ->verificationRequiredChannel()
            ->create(['verified_at' => now()]);

        // Act & Assert

        $this->expectException(InvalidArgumentException::class);

        app(SendTrackingRequestFailedNotificationAction::class)(
            $trackingRequest,
            $alertChannel,
        );

    }
}
