<?php

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function the_owner_of_a_tracking_request_can_update_only_its_update_interval(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create(['update_interval' => 35]);
        $trackingRequestB = TrackingRequest::factory()->create();
        Sanctum::actingAs($trackingRequestA->user);

        // Act
        $responseA = $this->json('PUT', route('trackingRequest.update', ['trackingRequest' => $trackingRequestA->uuid]), [
            'update_interval' => 55,
        ]);
        $responseB = $this->json('PUT', route('trackingRequest.update', ['trackingRequest' => $trackingRequestB->uuid]), [
            'update_interval' => 55,
        ]);

        // Assert
        $responseA->assertOk();
        $responseB->assertNotFound();
        $responseA->assertJsonFragment(['update_interval' => 55]);

        $this->assertDatabaseHas('tracking_requests', [
            'user_id' => $trackingRequestA->user->id,
            'update_interval' => 55,
        ]);
        $this->assertDatabaseMissing('tracking_requests', [
            'user_id' => $trackingRequestB->user->id,
            'update_interval' => 55,
        ]);
    }

    /** @test **/
    public function updated_interval_must_be_above_30_seconds(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create(['update_interval' => 35]);
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('PUT', route('trackingRequest.update', ['trackingRequest' => $trackingRequest->uuid]), [
            'update_interval' => 29,
        ]);

        // Assert
        $response->assertJsonValidationErrorFor('update_interval');
    }
}
