<?php

declare(strict_types=1);

namespace Domain\Users\Models;

use Database\Factories\UserFactory;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\QueryBuilder\UserQueryBuilder;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasUuid;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * @param Builder $query
     * @return UserQueryBuilder<User>
     */
    public function newEloquentBuilder($query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    /**
     * @return BelongsToMany<Stock>
     */
    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class);
    }

    /**
     * @return HasMany<AlertChannel>
     */
    public function alertChannels(): HasMany
    {
        return $this->hasMany(AlertChannel::class);
    }

    /**
     * @return HasMany<TrackingAlert>
     */
    public function trackingAlerts(): HasMany
    {
        return $this->hasMany(TrackingAlert::class);
    }

    /**
     * @return HasMany<TrackingRequest>
     */
    public function trackingRequests(): HasMany
    {
        return $this->hasMany(TrackingRequest::class);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function canAccessFilament(): bool
    {
        return true;
    }
}
