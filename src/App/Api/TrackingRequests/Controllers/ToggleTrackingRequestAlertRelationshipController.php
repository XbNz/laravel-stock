<?php

namespace App\Api\TrackingRequests\Controllers;

use App\Api\Alerts\Resources\TrackingAlertResource;
use App\Api\TrackingRequests\Requests\AttachRequest;
use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Actions\AttachTrackingRequestAndTrackingAlertAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Rules\AttachOnlyUniqueAlertRule;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

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
