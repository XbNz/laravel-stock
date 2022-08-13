<?php

namespace Database\Factories;

use Domain\Stocks\Models\Stock;
use Domain\Stores\Enums\Store;
use Domain\Users\Models\User;
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
            'user_id' => User::factory(),
            'url' => $randomStore->storeBaseUri(),
            'store' => $randomStore->value,
            'price' => $this->faker->numberBetween(1000, 250000),
            'update_interval' => $this->faker->numberBetween(30, 3600),
            'sku' => $this->faker->ean8(),
            'image' => $this->faker->filePath(),
        ];
    }
}
