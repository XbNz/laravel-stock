<?php

declare(strict_types=1);

namespace Domain\Stocks\Subscribers;

use Domain\Stocks\Actions\CreateHistoryForStockAction;
use Domain\Stocks\Events\StockCreatedEvent;
use Domain\Stocks\Events\StockUpdatedEvent;
use Domain\Stocks\Models\Stock;
use Illuminate\Events\Dispatcher;

class StockSubscriber
{
    public function __construct(private readonly CreateHistoryForStockAction $historyForStockAction)
    {
    }

    public function updated(StockUpdatedEvent $event): void
    {
        if ($this->priceAndAvailabilityNotNull($event->stock->fresh())) {
            ($this->historyForStockAction)($event->stock);
        }
    }

    public function created(StockCreatedEvent $event): void
    {
        if ($this->priceAndAvailabilityNotNull($event->stock->fresh())) {
            ($this->historyForStockAction)($event->stock);
        }
    }

    private function priceAndAvailabilityNotNull(Stock $stock): bool
    {
        return $stock->getRawOriginal('price') !== null && $stock->availability !== null;
    }

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            StockUpdatedEvent::class,
            [self::class, 'updated']
        );

        $dispatcher->listen(
            StockCreatedEvent::class,
            [self::class, 'created']
        );
    }
}
