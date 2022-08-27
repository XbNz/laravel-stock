<?php

namespace Database\Factories;

use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TrackingAlertFactory extends Factory
{
    protected $model = TrackingAlert::class;

    public function definition(): array
    {
        return [
            'alert_channel_id' => AlertChannel::factory(),
            'user_id' => User::factory(),
            'percentage_trigger' => $this->faker->numberBetween(1, 90),
            'availability_trigger' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
