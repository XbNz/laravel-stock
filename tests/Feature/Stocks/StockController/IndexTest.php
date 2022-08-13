<?php

namespace Tests\Feature\Stocks\StockController;

use Domain\User\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test **/
    public function an_authenticated_user_may_retrieve_their_own_stocks(): void
    {
        // Arrange
        $userA = User::factory();
        $stocksA = Stock::factory()->for($userA)->count(5)->create();
        $userB = User::factory();
        $stocksB = Stock::factory()->for($userB)->count(5)->create();

        Sanctum::actingAs($userA);

        // Act
        $response = $this->json(route('stocks.index', ['user' => $userA]));

        // Assert

        $response->assertJsonCount(5);
    }

    /** @test **/
    public function the_route_is_protected_by_the_intended_middleware(): void
    {
        // Arrange

        // Act

        // Assert
    }
}
