<?php

declare(strict_types=1);

namespace Tests\Feature\Stocks\StockController;

use Domain\Stocks\Models\Stock;
use Domain\Stores\Enums\Store;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function an_authenticated_user_may_retrieve_only_their_own_stocks(): void
    {
        // Arrange
        $userA = User::factory()->create();
        $stocksA = Stock::factory()->for($userA)->count(5)->create([
            'price' => 1000,
        ]);
        $userB = User::factory()->create();
        $stocksB = Stock::factory()->for($userB)->count(5)->create([
            'price' => 2000,
        ]);

        Sanctum::actingAs($userA);

        // Act
        $response = $this->json('GET', route('stock.index', [
            'user' => $userA->uuid,
        ]));

        // Assert

        $response->assertJsonCount(5, 'data');
        $response->assertJsonFragment([
            'price' => 1000,
        ]);
        $response->assertJsonMissing([
            'price' => 2000,
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'url',
                    'store',
                    'price',
                    'update_interval',
                    'sku',
                    'image',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    /** @test **/
    public function stocks_can_be_filtered_by_store(): void
    {
        // Arrange
        $user = User::factory()->create();

        foreach (Store::cases() as $store) {
            Stock::factory()->for($user)->create([
                'store' => $store,
            ]);
        }

        Sanctum::actingAs($user);

        // Act
        $response = $this->json(
            'GET',
            route('stock.index') . '?filter[store]=' . Store::cases()[0]->value
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
    public function the_route_is_protected_by_the_intended_middleware(): void
    {
        $this->assertRouteUsesMiddleware('stock.index', ['auth:sanctum']);
    }
}
