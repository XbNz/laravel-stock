<?php

namespace Tests\Feature\TrackingRequests\TogglePauseTrackingRequestController;

use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\InProgressState;
use Domain\TrackingRequests\States\PausedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function tracking_request_owned_by_logged_in_user_may_be_paused(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create([
            'status' => DormantState::class,
        ]);

        $trackingRequestB = TrackingRequest::factory()->create([
            'status' => DormantState::class,
        ]);

        Sanctum::actingAs($trackingRequestA->user);

        // Act
        $responseA = $this->json('POST', route('trackingRequest.togglePause', ['trackingRequest' => $trackingRequestA->uuid]));
        $responseB = $this->json('POST', route('trackingRequest.togglePause', ['trackingRequest' => $trackingRequestB->uuid]));

        // Assert
        $responseA->assertOk();
        $responseA->assertJsonFragment(['status' => 'paused']);
        $trackingRequestA->status->equals(PausedState::class);
        $responseB->assertNotFound();
    }

    /** @test **/
    public function tracking_request_in_an_in_progress_state_may_not_be_paused(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create([
            'status' => InProgressState::class,
        ]);

        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('POST', route('trackingRequest.togglePause', ['trackingRequest' => $trackingRequest->uuid]));

        // Assert
        $response->assertStatus(Response::HTTP_PRECONDITION_FAILED);
    }

    /** @test **/
    public function paused_request_should_return_to_dormant_state(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create([
            'status' => PausedState::class,
        ]);
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('POST', route('trackingRequest.togglePause', ['trackingRequest' => $trackingRequest->uuid]));

        // Assert
        $this->assertTrue($trackingRequest->fresh()->status->equals(DormantState::class));

        // TODO: Stock update event and subscriber firing notifications for price changes
        // then complete other todos
    }
}
