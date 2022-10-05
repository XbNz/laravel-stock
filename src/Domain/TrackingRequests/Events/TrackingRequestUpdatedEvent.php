<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Events;

use Domain\TrackingRequests\Models\TrackingRequest;

class TrackingRequestUpdatedEvent
{
    public function __construct(public TrackingRequest $trackingRequest)
    {
    }
}
