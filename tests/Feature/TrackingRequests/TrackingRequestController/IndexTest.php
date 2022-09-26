<?php

declare(strict_types=1);

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_is_allowed_to_retrieve_only_their_own_tracking_requests(): void
    {
        // Arrange

        $trackingRequestA = TrackingRequest::factory(5, [
            'update_interval' => 10,
        ]);
        $userA = User::factory()->has($trackingRequestA)->create();

        $trackingRequestB = TrackingRequest::factory(5, [
            'update_interval' => 20,
        ]);
        $userB = User::factory()->has($trackingRequestB)->create();

        Sanctum::actingAs($userA);

        // Act

        $response = $this->json('GET', route('trackingRequest.index'));

        // Assert

        $response->assertJsonCount(5, 'data');
        $response->assertJsonFragment([
            'update_interval' => 10,
        ]);

        $response->assertJsonMissing([
            'update_interval' => 20,
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'url',
                    'store',
                    'tracking_type',
                    'update_interval',
                    'status',
                    'color',
                    'color',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    /** @test **/
    public function tracking_requests_can_be_filtered_by_store(): void
    {
        // Arrange
        $user = User::factory()->create();

        foreach (Store::cases() as $store) {
            TrackingRequest::factory()->for($user)->create([
                'store' => $store,
            ]);
        }

        Sanctum::actingAs($user);

        // Act
        $response = $this->json(
            'GET',
            route('trackingRequest.index') . '?filter[store]=' . Store::cases()[0]->value
        );

        // Assert

        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [
                [
                    'store' => Store::cases()[0]->value,
                ],
            ],
        ]);
    }

    /** @test **/
    public function protected_by_middleware(): void
    {
        $this->assertRouteUsesMiddleware('trackingRequest.index', ['auth:sanctum']);
    }
}
