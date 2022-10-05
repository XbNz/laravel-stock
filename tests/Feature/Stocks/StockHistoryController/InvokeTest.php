<?php

declare(strict_types=1);

namespace Tests\Feature\Stocks\StockHistoryController;

use Carbon\Carbon;
use Database\Factories\StockHistoryFactory;
use Domain\Stocks\Actions\FormatPriceAction;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvokeTest extends TestCase
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
        $responseA->assertJsonStructure([
            'data' => [
                '*' => [
                    'price',
                    'availability',
                    'created_at',
                ],
            ],
        ]);
    }

    /** @test **/
    public function pagination_works(): void
    {
        // Arrange
        $userFactory = User::factory();
        $stock = Stock::factory()->has($userFactory)->has(StockHistoryFactory::times(10), 'histories')->create();
        $user = $stock->users()->sole();
        Sanctum::actingAs($user);

        // Act
        $response = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]));

        // Assert
        $response->assertJsonStructure([
            'links', 'meta',
        ]);
    }

    /** @test **/
    public function sorting_price_works(): void
    {
        // Arrange
        $userFactory = User::factory();
        $stock = Stock::factory()->has($userFactory)->has(StockHistoryFactory::times(10), 'histories')->create();
        $user = $stock->users()->sole();
        Sanctum::actingAs($user);

        // Act
        $responseA = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]) . '?sort=-price');

        $responseB = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]) . '?sort=price');

        // Assert
        $responseA->assertOk();
        $responseB->assertOk();

        $historicRecords = StockHistory::query()->where('stock_id', $stock->id);

        $shouldBeResponseAPrices = $historicRecords->pluck('price')->sortDesc()->values();
        $shouldBeResponseBPrices = $historicRecords->pluck('price')->sort()->values();

        $responseAPrices = Collection::make($responseA->json('data'))
            ->map(fn (array $record) => (int) Str::of($record['price'])->replaceMatches('/[^0-9]/', '')->value());
        $responseBPrices = Collection::make($responseB->json('data'))
            ->map(fn (array $record) => (int) Str::of($record['price'])->replaceMatches('/[^0-9]/', '')->value());

        $this->assertSame($shouldBeResponseAPrices->toArray(), $responseAPrices->toArray());
        $this->assertSame($shouldBeResponseBPrices->toArray(), $responseBPrices->toArray());
    }

    /** @test **/
    public function sorting_availability_works(): void
    {
        // Arrange
        $userFactory = User::factory();
        $stock = Stock::factory()->has($userFactory)->has(StockHistoryFactory::times(10), 'histories')->create();
        $user = $stock->users()->sole();
        Sanctum::actingAs($user);

        // Act
        $responseA = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]) . '?sort=-availability');

        $responseB = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]) . '?sort=availability');

        // Assert
        $responseA->assertOk();
        $responseB->assertOk();

        $historicRecords = StockHistory::query()->where('stock_id', $stock->id);

        $shouldBeResponseAPrices = $historicRecords->pluck('availability')->sortDesc()->values();
        $shouldBeResponseBPrices = $historicRecords->pluck('availability')->sort()->values();

        $responseAPrices = Collection::make($responseA->json('data'))->pluck('availability');
        $responseBPrices = Collection::make($responseB->json('data'))->pluck('availability');

        $this->assertSame($shouldBeResponseAPrices->toArray(), $responseAPrices->toArray());
        $this->assertSame($shouldBeResponseBPrices->toArray(), $responseBPrices->toArray());
    }

    /** @test **/
    public function sorting_created_at_works(): void
    {
        // Arrange
        $userFactory = User::factory();
        $stock = Stock::factory()->has($userFactory)->has(StockHistoryFactory::times(10), 'histories')->create();
        $user = $stock->users()->sole();
        Sanctum::actingAs($user);

        // Act
        $responseA = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]) . '?sort=-created_at');

        $responseB = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]) . '?sort=created_at');

        // Assert
        $responseA->assertOk();
        $responseB->assertOk();

        $historicRecords = StockHistory::query()->where('stock_id', $stock->id);

        $shouldBeResponseAPrices = $historicRecords->pluck('created_at')->sortDesc()->values();
        $shouldBeResponseBPrices = $historicRecords->pluck('created_at')->sort()->values();

        [$shouldBeResponseAPrices, $shouldBeResponseBPrices] = [
            $shouldBeResponseAPrices->map(fn (Carbon $date) => $date->format('Y-m-d H:i:s')),
            $shouldBeResponseBPrices->map(fn (Carbon $date) => $date->format('Y-m-d H:i:s')),
        ];

        $responseAPrices = Collection::make($responseA->json('data'))->pluck('created_at');
        $responseBPrices = Collection::make($responseB->json('data'))->pluck('created_at');

        $this->assertSame($shouldBeResponseAPrices->toArray(), $responseAPrices->toArray());
        $this->assertSame($shouldBeResponseBPrices->toArray(), $responseBPrices->toArray());
    }

    /** @test **/
    public function price_is_displayed_with_correct_localization(): void
    {
        // Arrange
        $userFactory = User::factory();
        $stock = Stock::factory()->has($userFactory)->has(StockHistoryFactory::times(10), 'histories')->create();
        $user = $stock->users()->sole();
        Sanctum::actingAs($user);
        $priceFormatter = app(FormatPriceAction::class);

        // Act
        $response = $this->json('GET', route('stock.history', [
            'stock' => $stock->uuid,
        ]));

        // Assert
        $response->assertOk();
        $this->assertIsInt($stock->histories()->first()->getRawOriginal('price'));

        $this->assertSame(
            $response->json('data.0.price'),
            ($priceFormatter)($stock->histories()->first()->getRawOriginal('price'), $stock->store->currency())
        );
    }
}
