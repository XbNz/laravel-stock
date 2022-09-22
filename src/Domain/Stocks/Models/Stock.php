<?php

declare(strict_types=1);

namespace Domain\Stocks\Models;

use Database\Factories\StockFactory;
use Domain\Stocks\QueryBuilders\StockQueryBuilder;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

class Stock extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'store' => Store::class,
    ];

    /**
     * @param Builder $query
     * @return StockQueryBuilder<Stock>
     */
    public function newEloquentBuilder($query): StockQueryBuilder
    {
        return new StockQueryBuilder($query);
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsToMany<TrackingRequest>
     */
    public function trackingRequests(): BelongsToMany
    {
        return $this->belongsToMany(TrackingRequest::class);
    }

    /**
     * @return HasMany<StockHistory>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }

    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }
}
