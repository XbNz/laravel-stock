<?php

declare(strict_types=1);

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Domain\Stores\Actions\ParseStoreByLinkAction;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\InferTrackingTypeForStoreAction;
use Domain\TrackingRequests\Enums\TrackingRequest;
use Domain\Users\Models\User;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_logged_in_user_can_create_a_tracking_request_with_a_valid_and_unique_product_or_search_url(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $randomStore = Arr::random(Store::cases());
        $randomTrackingType = Arr::random(TrackingRequest::cases());

        $inferStoreMock = $this->mock(ParseStoreByLinkAction::class);
        $inferStoreMock->shouldReceive('__invoke')->andReturn($randomStore);
        $inferTrackingTypeMock = $this->mock(InferTrackingTypeForStoreAction::class);
        $inferTrackingTypeMock->shouldReceive('__invoke')->andReturn($randomTrackingType);

        // Act
        $responseA = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.anything.com/",
            'update_interval' => 350,
        ]);
        $responseB = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.anything.com/",
            'update_interval' => 350,
        ]);

        // Assert

        $responseA->assertStatus(Response::HTTP_CREATED);
        $responseA->assertJson([
            'data' => [
                'url' => "https://www.anything.com/",
                'store' => $randomStore->value,
                'tracking_type' => $randomTrackingType->value,
                'update_interval' => 350,
            ],
        ]);

        $responseB->assertJsonValidationErrorFor('url');

        $this->assertDatabaseHas('tracking_requests', [
            'user_id' => $user->id,
            'url' => "https://www.anything.com/",
            'store' => $randomStore->value,
            'tracking_type' => $randomTrackingType->value,
            'update_interval' => 350,
        ]);
    }

    /** @test **/
    public function if_the_same_user_adds_a_duplicate_link_a_validation_error_is_thrown(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $randomStore = Arr::random(Store::cases());
        $randomTrackingType = Arr::random(TrackingRequest::cases());

        $inferStoreMock = $this->mock(ParseStoreByLinkAction::class);
        $inferStoreMock->shouldReceive('__invoke')->andReturn($randomStore);
        $inferTrackingTypeMock = $this->mock(InferTrackingTypeForStoreAction::class);
        $inferTrackingTypeMock->shouldReceive('__invoke')->andReturn($randomTrackingType);

        // Act
        $responseA = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.anything.com/",
            'update_interval' => 350,
        ]);
        $responseB = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.anything.com/",
            'update_interval' => 350,
        ]);

        // Assert

        $responseA->assertStatus(Response::HTTP_CREATED);
        $responseB->assertJsonValidationErrorFor('url');
    }

    /** @test **/
    public function is_the_same_link_is_created_by_two_different_users_it_should_be_allowed(): void
    {
        // Arrange
        $trackingRequest = \Domain\TrackingRequests\Models\TrackingRequest::factory()->create(['url' => "https://www.anything.com/"]);
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $randomStore = Arr::random(Store::cases());
        $randomTrackingType = Arr::random(TrackingRequest::cases());

        $inferStoreMock = $this->mock(ParseStoreByLinkAction::class);
        $inferStoreMock->shouldReceive('__invoke')->andReturn($randomStore);
        $inferTrackingTypeMock = $this->mock(InferTrackingTypeForStoreAction::class);
        $inferTrackingTypeMock->shouldReceive('__invoke')->andReturn($randomTrackingType);

        // Act
        $response = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.anything.com/",
            'update_interval' => 350,
        ]);

        // Assert

        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('trackingRequest.store', ['auth:sanctum']);
    }


    /** @test **/
    public function the_update_interval_must_be_above_30(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $responseA = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.sdccdsc.com/",
            'update_interval' => 29,
        ]);
        $responseB = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.csdcbgnffgsd.com/",
            'update_interval' => 30,
        ]);

        // Assert
        $responseA->assertJsonValidationErrors('update_interval');
    }

    /** @test **/
    public function url_must_be_from_a_supported_store(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://www.google.com/",
            'update_interval' => 35,
        ]);

        // Assert
        $response->assertJsonValidationErrorFor('url');
        $response->assertJsonValidationErrors(['url' => 'Unsupported store.']);
    }

}
