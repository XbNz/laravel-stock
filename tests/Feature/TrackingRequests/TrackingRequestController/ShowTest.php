<?php

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_only_view_their_own_tracking_requests(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create();
        $trackingRequestB = TrackingRequest::factory()->create();
        Sanctum::actingAs($trackingRequestA->user);

        // Act
        $responseA = $this->json('GET', route('trackingRequest.show', ['trackingRequest' => $trackingRequestA->uuid]));
        $responseB = $this->json('GET', route('trackingRequest.show', ['trackingRequest' => $trackingRequestB->uuid]));

        // Assert
        $responseA->assertOk();
        $responseB->assertNotFound();

        $responseA->assertJson([
            'data' => [
                'uuid' => $trackingRequestA->uuid,
                'url' => $trackingRequestA->url,
                'store' => $trackingRequestA->store,
                'tracking_alerts' => [],
                'tracking_type' => $trackingRequestA->tracking_type->value,
                'update_interval' => $trackingRequestA->update_interval,
                'created_at' => $trackingRequestA->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $trackingRequestA->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /** @test **/
    public function the_tracking_alerts_are_loaded(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->has(TrackingAlert::factory()->count(3))->create();
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('GET', route('trackingRequest.show', ['trackingRequest' => $trackingRequest->uuid]));

        // Assert
        $response->assertJsonCount(3, 'data.tracking_alerts');
    }
}
