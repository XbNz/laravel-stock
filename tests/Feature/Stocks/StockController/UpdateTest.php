<?php

namespace Tests\Feature\Stocks\StockController;

use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_can_only_update_their_own_stocks(): void
    {
        // Arrange
        $userA = User::factory()->create();
        $stockA = Stock::factory()->for($userA)->create(['update_interval' => 1000]);
        $userB = User::factory()->create();
        $stockB = Stock::factory()->for($userB)->create(['update_interval' => 2000]);

        Sanctum::actingAs($userA);

        // Act
        $responseA = $this->json('PUT', route('stock.update', ['stock' => $stockA->uuid]), [
            'update_interval' => 5000,
        ]);

        $responseB = $this->json('PUT', route('stock.update', ['stock' => $stockB->uuid]), [
            'update_interval' => 5000,
        ]);

        // Assert

        $responseA->assertOk();
        $responseA->assertJsonFragment([
            'update_interval' => 5000,
        ]);

        $this->assertDatabaseHas('stocks', [
            'uuid' => $stockA->uuid,
            'update_interval' => 5000,
        ]);

        $this->assertDatabaseHas('stocks', [
            'uuid' => $stockB->uuid,
            'update_interval' => 2000,
        ]);
    }

    /** @test **/
    public function the_route_is_protected_by_the_intended_middleware(): void
    {
        $this->assertRouteUsesMiddleware('stock.update', ['auth:sanctum']);
    }
}
