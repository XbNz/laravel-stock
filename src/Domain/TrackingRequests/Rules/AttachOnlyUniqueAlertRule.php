<?php

namespace Domain\TrackingRequests\Rules;

use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class AttachOnlyUniqueAlertRule implements Rule
{
    public function __construct(
        private readonly TrackingRequest $trackingRequest,
        private readonly TrackingAlert $trackingAlert,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        return $this->trackingRequest->whereHas('trackingAlerts', function (Builder $query) {
            return $query->where('id', $this->trackingAlert->id);
        })->exists() === false;
    }

    public function message(): string
    {
        return 'Tracking request already has this alert attached.';
    }
}
