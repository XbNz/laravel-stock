<?php

namespace Domain\Stocks\Models;

use Domain\Stocks\Actions\FormatPriceAction;
use Domain\Users\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    use HasUuid;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'availability' => 'boolean',
    ];

    protected function price(): Attribute
    {
        $formatPriceAction = app(FormatPriceAction::class);

        return Attribute::make(
            get: fn (int $value) => ($formatPriceAction)($value, $this->stock->store->currency()),
        );
    }

    /**
     * @return BelongsTo<Stock, StockHistory>
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
