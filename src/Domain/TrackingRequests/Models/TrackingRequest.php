<?php

namespace Domain\TrackingRequests\Models;

use Database\Factories\TrackingRequestFactory;
use Domain\Stocks\Models\Stock;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrackingRequest extends Model
{
    use HasUuid;
    use HasFactory;

    protected static function newFactory(): TrackingRequestFactory
    {
        return TrackingRequestFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class);
    }
}
