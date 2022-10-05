<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TrackingRequests\States;

use Domain\Alerts\Models\AlertChannel;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Notifications\TrackingRequestFailedNotification;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\FailedState;
use Domain\TrackingRequests\States\InProgressState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ToFailedTransitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function transitioning_from_any_state_to_failed_state_is_allowed(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create([
            'status' => InProgressState::class,
        ]);

        $trackingRequestB = TrackingRequest::factory()->create([
            'status' => DormantState::class,
        ]);

        // Act

        // Assert

        $this->assertTrue(
            $trackingRequestA->status->canTransitionTo(FailedState::class)
        );
        $this->assertTrue(
            $trackingRequestB->status->canTransitionTo(FailedState::class)
        );
    }

    /** @test **/
    public function transitioning_from_failed_to_any_state_is_disallowed(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create([
            'status' => FailedState::class,
        ]);

        // Act

        // Assert

        $this->assertFalse(
            $trackingRequest->status->canTransitionTo(DormantState::class)
        );
        $this->assertFalse(
            $trackingRequest->status->canTransitionTo(InProgressState::class)
        );
    }

    /** @test **/
    public function transitioning_from_any_state_to_failed_will_cause_a_notification_to_be_sent_to_one_of_the_users_alert_channels(): void
    {
        // Arrange
        Notification::fake();
        $trackingRequest = TrackingRequest::factory()->create();
        $alertChannel = AlertChannel::factory()->for($trackingRequest->user)
            ->verificationRequiredChannel()
            ->create([
                'verified_at' => now(),
            ]);

        // Act
        $trackingRequest->status->transitionTo(FailedState::class);

        // Assert
        Notification::assertSentTo($alertChannel, TrackingRequestFailedNotification::class);
    }
}
