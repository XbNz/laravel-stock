<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\AlertChannelController;

use Domain\Alerts\Models\AlertChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_can_only_delete_their_own_alert_channels(): void
    {
        // Arrange
        $alertChannelA = AlertChannel::factory()->create();
        $alertChannelB = AlertChannel::factory()->create();

        Sanctum::actingAs($alertChannelA->user);

        // Act
        $responseA = $this->json('DELETE', route('alertChannel.destroy', [
            'alertChannel' => $alertChannelA->uuid,
        ]));
        $responseB = $this->json('DELETE', route('alertChannel.destroy', [
            'alertChannel' => $alertChannelB->uuid,
        ]));

        // Assert
        $responseA->assertNoContent();
        $responseB->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertDatabaseCount('alert_channels', 1);
        $this->assertDatabaseHas('alert_channels', [
            'uuid' => $alertChannelB->uuid,
        ]);
        $this->assertDatabaseMissing('alert_channels', [
            'uuid' => $alertChannelA->uuid,
        ]);
    }
}
