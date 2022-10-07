<?php

declare(strict_types=1);

namespace Domain\Stocks\Models;

use Database\Factories\StockHistoryFactory;
use Domain\Stocks\Actions\FormatPriceAction;
use Domain\Stocks\Events\StockHistoryCreatedEvent;
use Domain\Stocks\QueryBuilders\StockHistoryQueryBuilder;
use Domain\Users\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

class StockHistory extends Model
{
    use HasUuid;
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'availability' => 'boolean',
    ];

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => StockHistoryCreatedEvent::class,
    ];

    /**
     * @param Builder $query
     * @return StockHistoryQueryBuilder<StockHistory>
     */
    public function newEloquentBuilder($query): StockHistoryQueryBuilder
    {
        return new StockHistoryQueryBuilder($query);
    }

    /**
     * @return BelongsTo<Stock, StockHistory>
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    protected static function newFactory(): StockHistoryFactory
    {
        return StockHistoryFactory::new();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function price(): Attribute
    {
        $formatPriceAction = app(FormatPriceAction::class);

        return Attribute::make(
            get: fn (int $value) => ($formatPriceAction)($value, $this->stock->store->currency()),
        );
    }
}
