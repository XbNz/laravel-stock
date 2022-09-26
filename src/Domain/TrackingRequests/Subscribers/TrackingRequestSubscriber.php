<?php

namespace Domain\TrackingRequests\Subscribers;

use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Events\TrackingRequestCreatedEvent;
use Domain\TrackingRequests\Events\TrackingRequestUpdatedEvent;
use Illuminate\Events\Dispatcher;

class TrackingRequestSubscriber
{

    public function updated()
    {
        // TODO: After an update, sync the tracking request's stocks with the user's stocks
        // todo: test it as well
    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            TrackingRequestUpdatedEvent::class,
            [self::class, 'updated']
        );
    }
}
