<?php

declare(strict_types=1);

namespace Tests\Feature\AlertChannels\TrackingAlertController;

use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_logged_in_user_can_destroy_their_own_tracking_alerts(): void
    {
        // Arrange
        $trackingAlertA = TrackingAlert::factory()->create();
        $trackingAlertB = TrackingAlert::factory()->create();
        Sanctum::actingAs($trackingAlertA->user);

        // Act
        $responseA = $this->json('DELETE', route('trackingAlert.destroy', [
            'trackingAlert' => $trackingAlertA->uuid,
        ]));
        $responseB = $this->json('DELETE', route('trackingAlert.destroy', [
            'trackingAlert' => $trackingAlertB->uuid,
        ]));

        // Assert
        $responseA->assertStatus(Response::HTTP_NO_CONTENT);
        $responseB->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertModelMissing($trackingAlertA);
        $this->assertModelExists($trackingAlertB);
    }

    /** @test **/
    public function when_a_tracking_request_is_deleted_the_pivot_record_connecting_it_to_a_tracking_request_should_too(): void
    {
        // Arrange
        $user = User::factory()->create();
        $trackingAlert = TrackingAlert::factory([
            'user_id' => $user->id,
        ]);
        $trackingRequest = TrackingRequest::factory([
            'user_id' => $user->id,
        ])->has($trackingAlert)->create();
        $this->assertDatabaseCount('tracking_alert_tracking_request', 1);
        Sanctum::actingAs($trackingRequest->user);

        // Act
        $this->json('DELETE', route('trackingAlert.destroy', [
            'trackingAlert' => $trackingRequest->trackingAlerts()->sole()->uuid,
        ]));

        // Assert
        $this->assertDatabaseCount('tracking_alerts', 0);
        $this->assertDatabaseCount('tracking_alert_tracking_request', 0);
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingAlert.destroy', ['auth:sanctum']);
    }
}
