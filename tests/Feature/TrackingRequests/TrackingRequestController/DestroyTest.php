<?php

declare(strict_types=1);

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Database\Factories\UserFactory;
use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_can_delete_their_own_tracking_requests(): void
    {
        // Arrange
        $trackingRequestA = TrackingRequest::factory()->create();
        $trackingRequestB = TrackingRequest::factory()->create();
        Sanctum::actingAs($trackingRequestA->user);

        // Act
        $responseA = $this->json('DELETE', route('trackingRequest.destroy', $trackingRequestA->uuid));
        $responseB = $this->json('DELETE', route('trackingRequest.destroy', $trackingRequestB->uuid));

        // Assert
        $responseA->assertNoContent();
        $responseB->assertNotFound();
        $this->assertModelMissing($trackingRequestA);
        $this->assertModelExists($trackingRequestB);
    }

    /** @test **/
    public function upon_deletion_of_a_tracking_request_any_stocks_related_to_the_given_request_which_do_not_relate_to_any_remaining_tracking_request_records_of_the_authenticated_user_will_be_detached_from_the_users_stocks(): void
    {
        // Arrange
        $stock = Stock::factory()->create();
        $trackingRequest = TrackingRequest::factory()->create();
        $user = $trackingRequest->user;

        $user->stocks()->attach($stock);
        $trackingRequest->stocks()->attach($stock);

        Sanctum::actingAs($user);

        // Act
        $this->json('DELETE', route('trackingRequest.destroy', $user->trackingRequests()->sole()->uuid));

        // Assert
        $this->assertDatabaseCount('stock_tracking_request', 0);
        $this->assertDatabaseCount('stock_user', 0);
        $this->assertDatabaseCount('tracking_requests', 0);
    }

    /** @test **/
    public function if_a_tracking_request_has_a_stock_that_relates_to_more_than_one_tracking_request_then_deleting_one_of_the_requests_should_not_result_in_the_stock_being_detached_from_the_user(): void
    {
        // Arrange
        $stock = Stock::factory()->create();
        $user = User::factory()->create();
        $trackingRequests = TrackingRequest::factory()->count(2)->create(['user_id' => $user->id]);

        $user->stocks()->attach($stock);
        $trackingRequests->each(fn (TrackingRequest $trackingRequest) => $trackingRequest->stocks()->attach($stock));

        Sanctum::actingAs($user);

        // Act
        $this->json('DELETE', route('trackingRequest.destroy', $user->trackingRequests()->first()->uuid));

        // Assert
        $this->assertDatabaseCount('stock_tracking_request', 1);
        $this->assertDatabaseCount('stock_user', 1);
        $this->assertDatabaseCount('tracking_requests', 1);
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingRequest.destroy', ['auth:sanctum']);
    }
}
