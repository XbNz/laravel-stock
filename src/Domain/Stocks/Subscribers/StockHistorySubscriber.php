<?php

namespace Domain\Stocks\Subscribers;

use Domain\Stocks\Actions\DispatchStockHistoryNotificationAction;
use Domain\Stocks\Events\StockHistoryCreatedEvent;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\App;

class StockHistorySubscriber
{

    public function __construct(private readonly DispatchStockHistoryNotificationAction $historyNotificationAction)
    {
    }

    public function created(StockHistoryCreatedEvent $event): void
    {
        ($this->historyNotificationAction)($event->history);
    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            StockHistoryCreatedEvent::class,
            [self::class, 'created']
        );
    }
}
