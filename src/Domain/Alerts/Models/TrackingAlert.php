<?php

declare(strict_types=1);

namespace Domain\Alerts\Models;

use Database\Factories\TrackingAlertFactory;
use Domain\Alerts\QueryBuilders\TrackingAlertQueryBuilder;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;

class TrackingAlert extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'availability_trigger' => 'boolean',
    ];

    /**
     * @param Builder $query
     * @return TrackingAlertQueryBuilder<TrackingAlert>
     */
    public function newEloquentBuilder($query): TrackingAlertQueryBuilder
    {
        return new TrackingAlertQueryBuilder($query);
    }

    /**
     * @return BelongsToMany<TrackingRequest>
     */
    public function trackingRequests(): BelongsToMany
    {
        return $this->belongsToMany(TrackingRequest::class);
    }

    /**
     * @return BelongsTo<AlertChannel, TrackingAlert>
     */
    public function alertChannel(): BelongsTo
    {
        return $this->belongsTo(AlertChannel::class);
    }

    /**
     * @return BelongsTo<User, TrackingAlert>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): TrackingAlertFactory
    {
        return new TrackingAlertFactory();
    }
}
