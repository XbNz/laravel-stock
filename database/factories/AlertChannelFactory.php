<?php

namespace Database\Factories;

use Domain\Alerts\Enums\AlertChannel as AlertChannelEnum;
use Domain\Alerts\Models\AlertChannel;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AlertChannelFactory extends Factory
{
    protected $model = AlertChannel::class;

    public function definition(): array
    {
        $type = Arr::random(AlertChannelEnum::cases());
        $value = $this->suitableValueForType($type);

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'value' => $value,
            'verified_at' => null,
        ];
    }

    public function verificationRequiredChannel(): AlertChannelFactory
    {
        return $this->state(function (array $attributes) {
            $type = Collection::make(AlertChannelEnum::cases())
                ->filter(fn (AlertChannelEnum $type) => $type->requiresVerification())
                ->random();

            $value = $this->suitableValueForType($type);

            return [
                'type' => $type,
                'value' => $value,
            ];
        });
    }

    public function verificationNotRequiredChannel(): AlertChannelFactory
    {
        return $this->state(function (array $attributes) {
            $type = Collection::make(AlertChannelEnum::cases())
                ->reject(fn (AlertChannelEnum $type) => $type->requiresVerification())
                ->random();

            $value = $this->suitableValueForType($type);

            return [
                'type' => $type,
                'value' => $value,
            ];
        });
    }

    public function suitableValueForType(AlertChannelEnum $type): string
    {
        return match ($type) {
            AlertChannelEnum::SMS => $this->faker->e164PhoneNumber(),
            AlertChannelEnum::Email => $this->faker->safeEmail(),
            AlertChannelEnum::Discord => $this->faker->url(),
        };
    }


}
