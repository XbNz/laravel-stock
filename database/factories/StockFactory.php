<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Stocks\Models\Stock;
use Domain\Stores\Enums\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomStore = Arr::random(Store::cases());

        return [
            'uuid' => $this->faker->uuid(),
            'title' => $this->faker->sentence,
            'url' => $randomStore->storeBaseUri(),
            'store' => $randomStore,
            'price' => $this->faker->numberBetween(1000, 250000),
            'availability' => $this->faker->boolean,
            'sku' => $this->faker->ean8(),
            'image' => $this->faker->filePath(),
        ];
    }
}
