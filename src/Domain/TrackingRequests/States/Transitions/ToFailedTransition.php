<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\States\Transitions;

use Domain\TrackingRequests\Actions\SendTrackingRequestFailedNotificationAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\FailedState;
use Spatie\ModelStates\Transition;

class ToFailedTransition extends Transition
{
    public function __construct(private readonly TrackingRequest $trackingRequest)
    {
    }

    public function handle(SendTrackingRequestFailedNotificationAction $failedNotification): TrackingRequest
    {
        $channel = $this->trackingRequest->user->alertChannels()->inRandomOrder()->take(1)->first();

        if ($channel !== null) {
            ($failedNotification)($this->trackingRequest, $channel);
        }

        $this->trackingRequest->status = new FailedState($this->trackingRequest);
        $this->trackingRequest->save();

        return $this->trackingRequest;
    }
}
