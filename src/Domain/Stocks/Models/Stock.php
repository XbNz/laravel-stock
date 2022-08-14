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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function trackingRequests(): BelongsToMany
    {
        return $this->belongsToMany(TrackingRequest::class);
    }


}
