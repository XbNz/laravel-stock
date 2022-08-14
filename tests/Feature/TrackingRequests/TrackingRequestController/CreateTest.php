<?php

namespace Tests\Feature\TrackingRequests\TrackingRequestController;

use Domain\Stores\Enums\Store;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_logged_in_user_can_create_a_tracking_request_with_a_valid_product_or_search_url(): void
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $randomStoreUrl = Arr::random(Store::cases();

        // Act
        $response = $this->json('POST', route('trackingRequest.store'), [
            'url' => "https://{$randomStoreUrl->getBaseUrl()}",
            'update_interval' => 35,
        ]);

        // Assert

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('tracking_requests', [
            'url' => "https://{$randomStoreUrl->getBaseUrl()}",
            'store' => $randomStoreUrl->value,
            'tracking_type' => 'search',
            'user_id' => $user->id,
            'update_interval' => 35,
        ]);
    }

    /** @test **/
    public function the_url_must_be_unique(): void
    {
        // Arrange

        // Act

        // Assert
    }

    /** @test **/
    public function alert_to_be_attached_must_be_unique(): void
    {
        // Arrange

        // Act

        // Assert
    }

    /** @test **/
    public function an_event_is_fired_post_creation(): void
    {
        // Arrange

        // Act

        // Assert
    }

    /** @test **/
    public function validation_tests(): void
    {
        // Arrange

        // Act

        // Assert
    }
}
