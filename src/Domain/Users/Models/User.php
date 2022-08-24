<?php

declare(strict_types=1);

namespace Domain\Users\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Domain\Alerts\Models\AlertChannel;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\QueryBuilders\StockQueryBuilder;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\QueryBuilder\UserQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class);
    }

    public function alertChannels(): HasMany
    {
        return $this->hasMany(AlertChannel::class);
    }

    public function trackingRequests(): HasMany
    {
        return $this->hasMany(TrackingRequest::class);
    }


}
