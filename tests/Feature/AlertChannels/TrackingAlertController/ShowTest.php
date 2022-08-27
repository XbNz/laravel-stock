<?php

namespace Tests\Feature\AlertChannels\TrackingAlertController;

use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_can_only_retrieve_their_own_tracking_alerts(): void
    {
        // Arrange
        $trackingAlertA = TrackingAlert::factory()->create(['percentage_trigger' => 50]);
        $trackingAlertB = TrackingAlert::factory()->create(['percentage_trigger' => 75]);
        Sanctum::actingAs($trackingAlertA->user);

        // Act
        $responseA = $this->json('GET', route('trackingAlert.show', ['trackingAlert' => $trackingAlertA->uuid]));
        $responseB = $this->json('GET', route('trackingAlert.show', ['trackingAlert' => $trackingAlertB->uuid]));

        // Assert
        $responseB->assertNotFound();
        $responseA->assertJson([
            'data' => [
                'uuid' => $trackingAlertA->uuid,
                'alert_channel' => ['uuid' => $trackingAlertA->alertChannel->uuid],
                'tracking_requests' => [],
                'percentage_trigger' => 50,
                'availability_trigger' => $trackingAlertA->availability_trigger,
                'created_at' => $trackingAlertA->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $trackingAlertA->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /** @test **/
    public function the_tracking_request_relationship_is_loaded_but_should_not_recursively_display_the_alert_itself(): void
    {
        // Arrange
        $trackingRequests = TrackingRequest::factory(6);
        $trackingAlert = TrackingAlert::factory()->has($trackingRequests)->create();
        Sanctum::actingAs($trackingAlert->user);

        // Act
        $response = $this->json('GET', route('trackingAlert.show', ['trackingAlert' => $trackingAlert->uuid]));

        // Assert
        $response->assertJsonMissingPath('data.tracking_requests.0.tracking_alerts');
        $response->assertJsonStructure([
            'data' => [
                'uuid',
                'alert_channel',
                'tracking_requests',
                'percentage_trigger',
                'availability_trigger',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonCount(5, 'data.tracking_requests');
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingAlert.show', ['auth:sanctum']);
    }
}
