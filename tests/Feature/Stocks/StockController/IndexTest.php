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
    public function an_authenticated_user_may_retrieve_only_stocks_that_they_have_been_associated_with(): void
    {
        // Arrange
        $stocksA = Stock::factory(5, [
            'price' => 1000,
        ]);
        $userA = User::factory()->has($stocksA)->create();

        $stocksB = Stock::factory(5, [
            'price' => 2000,
        ]);
        $userB = User::factory()->has($stocksB)->create();

        Sanctum::actingAs($userA);

        // Act
        $response = $this->json('GET', route('stock.index', [
            'user' => $userA->uuid,
        ]));

        // Assert

        $response->assertJsonCount(5, 'data');
        $response->assertJsonFragment([
            'uuid' => $userA->stocks->first()->uuid,
        ]);
        $response->assertJsonMissing([
            'uuid' => $userB->stocks->first()->uuid,
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'url',
                    'store',
                    'price',
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
            $stock = Stock::factory()->create([
                'store' => $store,
            ]);

            $user->stocks()->attach($stock);
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
