<?php

namespace Domain\Alerts\Models;

use Database\Factories\TrackingAlertFactory;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Concerns\HasUuid;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrackingAlert extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'availability_trigger' => 'boolean',
    ];

    protected static function newFactory(): TrackingAlertFactory
    {
        return new TrackingAlertFactory;
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
}