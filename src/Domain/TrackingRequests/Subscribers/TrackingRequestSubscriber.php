<?php

namespace Domain\TrackingRequests\Subscribers;

use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Events\TrackingRequestCreatedEvent;
use Domain\TrackingRequests\Events\TrackingRequestUpdatedEvent;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Events\Dispatcher;

class TrackingRequestSubscriber
{

    public function updated(TrackingRequestUpdatedEvent $trackingRequestUpdatedEvent): void
    {
        $arrayOfStockIds = $trackingRequestUpdatedEvent->trackingRequest->stocks()->select('id')->pluck('id')->toArray();
        $trackingRequestUpdatedEvent->trackingRequest->user->stocks()->syncWithoutDetaching($arrayOfStockIds);
    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            TrackingRequestUpdatedEvent::class,
            [self::class, 'updated']
        );
    }
}
