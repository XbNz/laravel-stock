<?php

declare(strict_types=1);

namespace Domain\Stocks\Actions;

use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;

class CreateHistoryForStockAction
{
    public function __invoke(Stock $stock): Stock
    {
        if ($stock->histories()->count() === 0) {
            $this->createHistory($stock);
            return $stock;
        }

        $latestHistory = $stock->histories()->latest()->first();

        if ($this->priceOrAvailabilityHasChanged($stock, $latestHistory)) {
            $this->createHistory($stock);
            return $stock;
        }

        return $stock;
    }

    private function createHistory(Stock $stock): void
    {
        $stock->histories()->create([
            'price' => $stock->getRawOriginal('price'),
            'availability' => $stock->availability,
        ]);
    }

    private function priceOrAvailabilityHasChanged(Stock $stock, StockHistory $history): bool
    {
        return $history->getRawOriginal('price') !== $stock->getRawOriginal('price')
            || $history->availability !== $stock->availability;
    }
}
