<?php

namespace Domain\Stocks\Events;

use Domain\Stocks\Models\StockHistory;

class StockHistoryCreatedEvent
{
    public function __construct(public readonly StockHistory $history)
    {
    }
}
