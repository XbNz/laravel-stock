<?php

namespace App\Api\TrackingRequests\Controllers;

use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Domain\TrackingRequests\Actions\ToggleTrackingRequestStatusAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\PausedState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Webmozart\Assert\Assert;

class TogglePauseTrackingRequestController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function __invoke(
        TrackingRequest $trackingRequest,
        ToggleTrackingRequestStatusAction $toggleAction,
    ): JsonResource {
        $gate = $this->gate->inspect('update', $trackingRequest);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        try {
            $trackingRequest = ($toggleAction)($trackingRequest);
        } catch (Throwable) {
            abort(Response::HTTP_PRECONDITION_FAILED, 'Status may not be transitioned');
        }

        return TrackingRequestResource::make($trackingRequest->fresh());
    }

}
