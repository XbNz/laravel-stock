<?php

declare(strict_types=1);

namespace Tests\Feature\TrackingRequests\ToggleTrackingRequestAlertRelationshipController;

use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvokeTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_logged_in_user_may_attach_a_tracking_alert_which_they_own_to_a_tracking_request_which_they_own(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();
        $trackingAlert = TrackingAlert::factory()->create([
            'user_id' => $trackingRequest->user_id,
        ]);

        Sanctum::actingAs($trackingRequest->user);

        // Act
        $this->assertDatabaseCount('tracking_alert_tracking_request', 0);
        $response = $this->json('POST', route('trackingRequest.toggleAlert', [
            'trackingRequest' => $trackingRequest->uuid,
            'trackingAlert' => $trackingAlert->uuid,
        ]));

        // Assert
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'tracking_alerts' => [
                    [
                        'uuid' => $trackingAlert->uuid,
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('tracking_alert_tracking_request', [
            'tracking_request_id' => $trackingRequest->id,
            'tracking_alert_id' => $trackingAlert->id,
        ]);
    }

    /** @test **/
    public function a_user_cannot_attach_a_tracking_alert_to_a_tracking_request_they_dont_own(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();
        $trackingAlert = TrackingAlert::factory()->create();
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('POST', route('trackingRequest.toggleAlert', [
            'trackingRequest' => $trackingRequest->uuid,
            'trackingAlert' => $trackingAlert->uuid,
        ]));

        // Assert
        $response->assertNotFound();
        $this->assertDatabaseCount('tracking_alert_tracking_request', 0);
    }

    /** @test **/
    public function a_user_cannot_attach_a_tracking_request_to_a_tracking_alert_they_dont_own(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();
        $trackingAlert = TrackingAlert::factory()->create();
        Sanctum::actingAs($trackingAlert->user);

        // Act
        $response = $this->json('POST', route('trackingRequest.toggleAlert', [
            'trackingRequest' => $trackingRequest->uuid,
            'trackingAlert' => $trackingAlert->uuid,
        ]));

        // Assert
        $response->assertNotFound();
        $this->assertDatabaseCount('tracking_alert_tracking_request', 0);
    }

    /** @test **/
    public function a_second_tracking_alert_may_be_added(): void
    {
        // Arrange
        $trackingRequest = TrackingRequest::factory()->create();
        $trackingAlertA = TrackingAlert::factory()->create([
            'user_id' => $trackingRequest->user_id,
        ]);
        $trackingAlertB = TrackingAlert::factory()->create([
            'user_id' => $trackingRequest->user_id,
        ]);
        $trackingRequest->trackingAlerts()->attach($trackingAlertA);
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $response = $this->json('POST', route('trackingRequest.toggleAlert', [
            'trackingRequest' => $trackingRequest->uuid,
            'trackingAlert' => $trackingAlertB->uuid,
        ]));

        // Assert
        $response->assertOk();
        $this->assertDatabaseCount('tracking_alert_tracking_request', 2);
    }
}
