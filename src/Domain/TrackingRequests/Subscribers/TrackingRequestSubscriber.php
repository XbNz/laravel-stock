<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Subscribers;

use Domain\TrackingRequests\Events\TrackingRequestUpdatedEvent;
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
