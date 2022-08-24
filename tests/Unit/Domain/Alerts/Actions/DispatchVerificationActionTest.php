<?php

namespace Tests\Unit\Domain\Alerts\Actions;

use Domain\Alerts\Actions\DispatchVerificationAction;
use Domain\Alerts\Exceptions\ChannelNotVerifiableException;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Notifications\VerifyAlertChannelNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Spatie\QueueableAction\Testing\QueueableActionFake;
use Tests\TestCase;

class DispatchVerificationActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function it_dispatches_a_verification_notification_to_the_correct_signed_url_given_an_alert_channel_model(): void
    {
        // Arrange
        Notification::fake();
        $action = app(DispatchVerificationAction::class);
        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create();

        // Act
        ($action)($alertChannel);

        // Assert
        Notification::assertSentTimes(VerifyAlertChannelNotification::class, 1);
        Notification::assertSentTo(
            $alertChannel,
            VerifyAlertChannelNotification::class,
            function (VerifyAlertChannelNotification $notification, array $channels) use ($alertChannel) {
                $this->assertCount(1, $channels);
                $signedUrl = invade($notification)->signedUrl;
                $this->assertStringContainsString($alertChannel->uuid, $signedUrl);
                return true;
            }
        );
    }


    /** @test **/
    public function it_throws_an_exception_if_the_channel_is_not_verifiable(): void
    {
        // Arrange
        Notification::fake();
        $action = app(DispatchVerificationAction::class);
        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();

        // Act
        $this->expectException(ChannelNotVerifiableException::class);
        ($action)($alertChannel);
    }
}
