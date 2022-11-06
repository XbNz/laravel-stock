<?php

declare(strict_types=1);

namespace Domain\Stocks\Subscribers;

use Domain\Stocks\Actions\DispatchStockHistoryNotificationAction;
use Domain\Stocks\Events\StockHistoryCreatedEvent;
use Illuminate\Events\Dispatcher;

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
