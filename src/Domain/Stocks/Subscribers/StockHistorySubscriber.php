<?php

namespace Domain\Stocks\Subscribers;

use Domain\Stocks\Events\StockHistoryUpdatedEvent;
use Illuminate\Events\Dispatcher;

class StockHistorySubscriber
{

    public function updated(StockHistoryUpdatedEvent $event): void
    {

    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            StockHistoryUpdatedEvent::class,
            [self::class, 'updated']
        );
    }
}
