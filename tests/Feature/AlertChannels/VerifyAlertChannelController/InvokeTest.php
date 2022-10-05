<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\VerifyAlertChannelController;

use Domain\Alerts\Actions\SignedUrlForChannelVerificationAction;
use Domain\Alerts\Models\AlertChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_valid_signed_url_successfully_verifies_the_alert_channel(): void
    {
        // Arrange
        $this->withoutExceptionHandling();
        $alertChannel = AlertChannel::factory()->verificationRequiredChannel()->create();
        $signedUrlAction = app(SignedUrlForChannelVerificationAction::class);
        $signedUrl = ($signedUrlAction)($alertChannel);

        // Act
        $response = $this->json('GET', $signedUrl);

        // Assert
        $response->assertJson([
            'data' => [
                'uuid' => $alertChannel->uuid,
                'verified_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);

        $this->assertDatabaseCount('alert_channels', 1);
        $this->assertDatabaseHas('alert_channels', [
            'uuid' => $alertChannel->uuid,
            'verified_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test **/
    public function only_alert_channels_that_require_verification_may_be_verified(): void
    {
        // Arrange
        $date = now();
        $alertChannel = AlertChannel::factory()
            ->verificationNotRequiredChannel()
            ->create();

        $signedUrlAction = app(SignedUrlForChannelVerificationAction::class);
        $signedUrl = ($signedUrlAction)($alertChannel);

        // Act
        $response = $this->json('GET', $signedUrl);

        // Assert

        $response->assertNotFound();

        $this->assertDatabaseHas('alert_channels', [
            'uuid' => $alertChannel->uuid,
            'verified_at' => null,
        ]);
    }

    /** @test **/
    public function a_verified_alert_channel_may_not_be_reverified(): void
    {
        // Arrange
        $date = now();
        $alertChannel = AlertChannel::factory()
            ->verificationRequiredChannel()
            ->create([
                'verified_at' => $date,
            ]);

        $signedUrlAction = app(SignedUrlForChannelVerificationAction::class);
        $signedUrl = ($signedUrlAction)($alertChannel);

        // Act
        $response = $this->json('GET', $signedUrl);

        // Assert

        $response->assertNotFound();
        $this->travel('+1 minutes');
        $this->assertDatabaseHas('alert_channels', [
            'uuid' => $alertChannel->uuid,
            'verified_at' => $date->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test **/
    public function the_signed_middleware_is_attached(): void
    {
        $this->assertRouteUsesMiddleware('alertChannel.verify', ['signed']);
    }
}
