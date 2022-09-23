<?php

namespace Database\Factories;

use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockHistoryFactory extends Factory
{
    protected $model = StockHistory::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'stock_id' => Stock::factory(),
            'price' => $this->faker->numberBetween(3, 5000),
            'availability' => $this->faker->boolean(),
            'created_at' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
        ];
    }
}
