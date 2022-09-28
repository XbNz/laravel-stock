<?php

namespace Domain\TrackingRequests\Actions;

use Domain\TrackingRequests\DTOs\UpdateTrackingRequestData;
use Domain\TrackingRequests\Models\TrackingRequest;

class UpdateTrackingRequestAction
{
    public function __invoke(UpdateTrackingRequestData $data, TrackingRequest $trackingRequest): TrackingRequest
    {
        $trackingRequest->update([
            'name' => $data->name,
            'update_interval' => $data->updateInterval,
        ]);

        return $trackingRequest->fresh();
    }
}
