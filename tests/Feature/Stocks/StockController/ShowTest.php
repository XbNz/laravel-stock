<?php

declare(strict_types=1);

namespace Tests\Feature\Stocks\StockController;

use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_can_only_view_their_own_stocks(): void
    {
        // Arrange
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $stockA = Stock::factory()->for($userA)->create();
        $stockB = Stock::factory()->for($userB)->create();

        Sanctum::actingAs($userA);

        // Act
        $responseA = $this->json('GET', route('stock.show', [
            'stock' => $stockA->uuid,
        ]));
        $responseB = $this->json('GET', route('stock.show', [
            'stock' => $stockB->uuid,
        ]));

        // Assert

        $responseA->assertOk();
        $responseB->assertNotFound();

        $responseA->assertJson([
            'data' => [
                'uuid' => $stockA->uuid,
                'url' => $stockA->url,
                'store' => $stockA->store->value,
                'price' => $stockA->price,
                'update_interval' => $stockA->update_interval,
                'sku' => $stockA->sku,
                'image' => $stockA->image,
                'created_at' => $stockA->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $stockA->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /** @test **/
    public function a_response_that_was_last_updated_by_a_search_request_will_include_the_search(): void
    {
        // Arrange

        // Act

        // Assert
    }

    /** @test **/
    public function the_route_is_protected_by_the_intended_middleware(): void
    {
        $this->assertRouteUsesMiddleware('stock.show', ['auth:sanctum']);
    }
}
