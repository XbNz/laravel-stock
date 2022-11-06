<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\TrackingRequests\DTOs\UpdateTrackingRequestData;
use Domain\TrackingRequests\Models\TrackingRequest;

class UpdateTrackingRequestAction
{
    public function __invoke(UpdateTrackingRequestData $data, TrackingRequest $trackingRequest): TrackingRequest
    {
        if ($data->name !== null) {
            $trackingRequest->name = $data->name;
        }

        if ($data->updateInterval !== null) {
            $trackingRequest->update_interval = $data->updateInterval;
        }

        $trackingRequest->saveOrFail();

        return $trackingRequest->fresh();
    }
}
