<?php

declare(strict_types=1);

namespace Domain\Stocks\Events;

use Domain\Stocks\Models\Stock;

class StockCreatedEvent
{
    public function __construct(public readonly Stock $stock)
    {
    }
}
