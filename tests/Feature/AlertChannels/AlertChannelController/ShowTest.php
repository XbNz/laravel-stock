<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\AlertChannelController;

use Domain\Alerts\Models\AlertChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_can_retrieve_only_their_own_alert_channels(): void
    {
        // Arrange
        $alertChannelA = AlertChannel::factory()->create([
            'verified_at' => now(),
        ]);
        $alertChannelB = AlertChannel::factory()->create();

        Sanctum::actingAs($alertChannelA->user);

        // Act
        $responseA = $this->json('GET', route('alertChannel.show', [
            'alertChannel' => $alertChannelA->uuid,
        ]));
        $responseB = $this->json('GET', route('alertChannel.show', [
            'alertChannel' => $alertChannelB->uuid,
        ]));

        // Assert
        $responseA->assertOk();
        $responseB->assertStatus(Response::HTTP_NOT_FOUND);

        $responseA->assertJson([
            'data' => [
                'uuid' => $alertChannelA->uuid,
                'type' => $alertChannelA->type->value,
                'value' => $alertChannelA->value,
                'verified_at' => $alertChannelA->verified_at->format('Y-m-d H:i:s'),
                'created_at' => $alertChannelA->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $alertChannelA->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /** @test **/
    public function the_sanctum_middleware_is_attached(): void
    {
        $this->assertRouteUsesMiddleware('alertChannel.show', ['auth:sanctum']);
    }
}
