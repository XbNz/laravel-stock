<?php

namespace Domain\Stocks\Models;

use Database\Factories\StockFactory;
use Domain\Stocks\QueryBuilders\StockQueryBuilder;
use Domain\Stores\Enums\Store;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'store' => Store::class
    ];

    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }

    public function newEloquentBuilder($query): StockQueryBuilder
    {
        return new StockQueryBuilder($query);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
