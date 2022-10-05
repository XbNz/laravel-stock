<?php

declare(strict_types=1);

namespace Domain\Stocks\Subscribers;

use Domain\Stocks\Actions\CreateHistoryForStockAction;
use Domain\Stocks\Events\StockUpdatedEvent;
use Illuminate\Events\Dispatcher;

class StockSubscriber
{
    public function __construct(private readonly CreateHistoryForStockAction $historyForStockAction)
    {
    }

    public function updated(StockUpdatedEvent $event): void
    {
        ($this->historyForStockAction)($event->stock);
    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            StockUpdatedEvent::class,
            [self::class, 'updated']
        );
    }
}
