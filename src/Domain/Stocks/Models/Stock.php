<?php

declare(strict_types=1);

namespace Domain\Stocks\Models;

use Database\Factories\StockFactory;
use Domain\Stocks\Actions\FormatPriceAction;
use Domain\Stocks\Events\StockCreatedEvent;
use Domain\Stocks\Events\StockUpdatedEvent;
use Domain\Stocks\QueryBuilders\StockQueryBuilder;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

class Stock extends Model
{
    use HasFactory;
    use HasUuid;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => StockCreatedEvent::class,
        'updated' => StockUpdatedEvent::class,
    ];

    protected $casts = [
        'store' => Store::class,
        'availability' => 'boolean',
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

    /**
     * @return Attribute<string|null, never>
     */
    protected function price(): Attribute
    {
        $formatPriceAction = app(FormatPriceAction::class);

        return Attribute::make(
            get: function (int|null $price) use ($formatPriceAction): string|null {
                if ($price === null) {
                    return null;
                }

                return ($formatPriceAction)($price, $this->store->currency());
            },
        );
    }

    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }
}
