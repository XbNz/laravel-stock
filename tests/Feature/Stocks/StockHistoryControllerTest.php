<?php

namespace Tests\Feature\Stocks;

use Database\Factories\StockHistoryFactory;
use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StockHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_logged_in_user_may_retrieve_historic_records_for_stocks_they_subscribe_to(): void
    {
        // Arrange
        $userFactory = User::factory();
        $stock = Stock::factory()->has($userFactory)->has(StockHistoryFactory::times(10), 'histories')->create();
        $stockB = Stock::factory()->create();

        $user = $stock->users()->sole();

        Sanctum::actingAs($user);

        // Act
        $responseA = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]));
        $responseB = $this->json('GET', route('stock.history', [
            'stock' => $stockB->uuid,
        ]));

        // Assert
        $responseA->assertOk();
        $responseB->assertNotFound();

        $responseA->assertJsonCount(10, 'data');
        dd($responseA->json());
        $responseA->assertJsonStructure([
            'data' => [
                '*' => [
                    'price',
                    'availability',
                    'created_at',
                ]
            ]
        ]);
    }
}
