<?php

declare(strict_types=1);

namespace Domain\Stocks\Actions;

use Domain\Stocks\DTOs\UpdateStockData;
use Domain\Stocks\Models\Stock;

class UpdateStockAction
{
    public function __invoke(Stock $stock, UpdateStockData $newData): Stock
    {
        $stock->update([
            'update_interval' => $newData->updateInterval,
        ]);

        return $stock;
    }
}
