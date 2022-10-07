<?php

declare(strict_types=1);

namespace Domain\Stocks\Actions;

use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Webmozart\Assert\Assert;

class CreateHistoryForStockAction
{
    public function __invoke(Stock $stock): Stock
    {
        $stock = $stock->fresh();

        if ($stock->histories()->count() === 0) {
            $this->createHistory($stock);
            return $stock;
        }

        $latestHistory = $stock->histories()->latest()->first();
        Assert::notNull($latestHistory);

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
