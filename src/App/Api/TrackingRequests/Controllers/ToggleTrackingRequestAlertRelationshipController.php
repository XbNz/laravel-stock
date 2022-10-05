<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Controllers;

use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ToggleTrackingRequestAlertRelationshipController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function __invoke(
        TrackingRequest $trackingRequest,
        TrackingAlert $trackingAlert,
    ): JsonResource {
        $gateA = $this->gate->inspect('update', $trackingRequest);
        $gateB = $this->gate->inspect('update', $trackingAlert);

        if ($gateA->denied() || $gateB->denied()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $trackingRequest->trackingAlerts()->toggle($trackingAlert);

        return TrackingRequestResource::make($trackingRequest->load('trackingAlerts'));
    }
}
