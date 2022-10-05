<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\TrackingAlertController;

use Domain\Alerts\Models\TrackingAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_logged_in_user_can_list_their_own_tracking_alerts(): void
    {
        // Arrange
        $trackingAlertA = TrackingAlert::factory()->create([
            'percentage_trigger' => 50,
        ]);
        $trackingAlertB = TrackingAlert::factory()->create([
            'percentage_trigger' => 75,
        ]);
        Sanctum::actingAs($trackingAlertA->user);

        // Act
        $response = $this->json('GET', route('trackingAlert.index'));

        // Assert

        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [
                [
                    'uuid' => $trackingAlertA->uuid,
                    'alert_channel' => [
                        'uuid' => $trackingAlertA->alertChannel->uuid,
                    ],
                    'percentage_trigger' => 50,
                    'availability_trigger' => $trackingAlertA->availability_trigger,
                    'created_at' => $trackingAlertA->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $trackingAlertA->updated_at->format('Y-m-d H:i:s'),
                ],
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'alert_channel',
                    'percentage_trigger',
                    'availability_trigger',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingAlert.index', ['auth:sanctum']);
    }
}
