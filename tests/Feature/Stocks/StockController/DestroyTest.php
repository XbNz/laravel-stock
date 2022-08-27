<?php

declare(strict_types=1);

namespace Tests\Feature\Stocks\StockController;

use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_user_may_dissociate_from_a_stock_but_the_stock_should_remain(): void
    {
        // Arrange
        $user = User::factory()->has(
            Stock::factory()
        )->create();

        Sanctum::actingAs($user);

        // Act

        $stocksWithUser = Stock::query()->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $this->assertEquals(1, $stocksWithUser);

        $response = $this->json('DELETE', route('stock.destroy', [
            'stock' => $user->stocks->sole()->uuid,
        ]));

        // Assert

        $stocksWithUser = Stock::query()->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $this->assertEquals(0, $stocksWithUser);

        $this->assertDatabaseCount('stocks', 1);
    }

    /** @test **/
    public function sanctum_middleware_attached(): void
    {
        $this->assertRouteUsesMiddleware('stock.destroy', ['auth:sanctum']);
    }
}
