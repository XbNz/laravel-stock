<?php

declare(strict_types=1);

namespace Domain\Alerts\Models;

use Database\Factories\AlertChannelFactory;
use Domain\Alerts\Enums\AlertChannel as AlertChannelEnum;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Propaganistas\LaravelPhone\PhoneNumber;

class AlertChannel extends Model
{
    use HasUuid;
    use HasFactory;
    use Notifiable;

    protected $casts = [
        'type' => AlertChannelEnum::class,
        'verified_at' => 'datetime',
    ];

    public function routeNotificationFor(string $driver): string
    {
        return $this->value;
    }

    /**
     * @return Attribute<string, string>
     */
    public function value(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => $this->type === AlertChannelEnum::SMS ? PhoneNumber::make($value)->formatE164() : $value,
        );
    }

    public static function newFactory(): AlertChannelFactory
    {
        return AlertChannelFactory::new();
    }

    /**
     * @return BelongsTo<User, AlertChannel>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
