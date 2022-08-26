<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class TrackingRequestFactory extends Factory
{
    protected $model = TrackingRequest::class;

    public function definition(): array
    {
        $randomStore = Arr::random(Store::cases());
        $randomTrackingType = Arr::random(TrackingRequestEnum::cases());

        return [
            'user_id' => User::factory(),
            'url' => $randomStore->storeBaseUri(),
            'store' => $randomStore->value,
            'tracking_type' => $randomTrackingType->value,
            'update_interval' => $this->faker->numberBetween(30, 3600),
        ];
    }
}
