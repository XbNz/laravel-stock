<?php

namespace Domain\Stocks\Events;

use Domain\Stocks\Models\Stock;

class StockCreatedEvent
{
    public function __construct(public readonly Stock $stock)
    {
    }
}
