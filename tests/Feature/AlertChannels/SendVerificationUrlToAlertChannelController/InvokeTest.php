<?php

namespace Tests\Feature\AlertChannels\SendVerificationUrlToAlertChannelController;

use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Notifications\VerifyAlertChannelNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function authenticated_user_can_send_verification_links_to_their_own_channels(): void
    {
        // Arrange
        Notification::fake();
        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create();
        Sanctum::actingAs($alertChannel->user);

        // Act
        $response = $this->json(
            'POST', route('alertChannel.sendVerification', [
                'alertChannel' => $alertChannel->uuid
            ])
        );

        // Assert

        $response->assertStatus(Response::HTTP_ACCEPTED);

        Notification::assertSentTo(
            $alertChannel,
            VerifyAlertChannelNotification::class
        );
    }

    /** @test **/
    public function alert_channel_must_be_Verifiable(): void
    {
        // Arrange
        Notification::fake();
        $alertChannel = AlertChannel::factory()->verificationNotRequiredChannel()->create();
        Sanctum::actingAs($alertChannel->user);

        // Act
        $response = $this->json(
            'POST', route('alertChannel.sendVerification', [
                'alertChannel' => $alertChannel->uuid
            ])
        );

        // Assert
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        Notification::assertNothingSent();
    }

    /** @test **/
    public function a_verifiable_alert_channel_may_not_be_reverified(): void
    {
        // Arrange
        Notification::fake();
        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create(['verified_at' => now()]);
        Sanctum::actingAs($alertChannel->user);

        // Act
        $response = $this->json(
            'POST', route('alertChannel.sendVerification', [
                'alertChannel' => $alertChannel->uuid
            ])
        );

        // Assert
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        Notification::assertNothingSent();
    }

    /** @test **/
    public function route_is_protected_by_sanctum(): void
    {
        $this->assertRouteUsesMiddleware('alertChannel.sendVerification', ['auth:sanctum']);
    }

}
