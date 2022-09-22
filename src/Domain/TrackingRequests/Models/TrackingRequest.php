<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Models;

use Database\Factories\TrackingRequestFactory;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
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

    protected $casts = [
        'tracking_type' => TrackingRequestEnum::class,
    ];

    /**
     * @return BelongsTo<User, TrackingRequest>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Stock>
     */
    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class);
    }

    /**
     * @return BelongsToMany<TrackingAlert>
     */
    public function trackingAlerts(): BelongsToMany
    {
        return $this->belongsToMany(TrackingAlert::class);
    }

    protected static function newFactory(): TrackingRequestFactory
    {
        return TrackingRequestFactory::new();
    }
}