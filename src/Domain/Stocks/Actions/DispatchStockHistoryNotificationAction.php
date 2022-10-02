<?php

namespace Domain\Stocks\Actions;

use DASPRiD\Enum\Exception\IllegalArgumentException;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Support\ValueObjects\Percentage;

class DispatchStockHistoryNotificationAction
{
    public function __invoke(StockHistory $stockHistory): void
    {
        if ($stockHistory->stock->histories()->count() === 1) {
            return;
        }

        $lastHistoricRecord = $stockHistory->stock
            ->histories()
            ->orderBy('created_at', 'desc')
            ->skip(1)
            ->take(1)
            ->sole();

        $isPriceChange = $lastHistoricRecord->getRawOriginal('price') !== $stockHistory->getRawOriginal('price');
        $isAvailabilityChange = $lastHistoricRecord->availability !== $stockHistory->availability;

        match (true) {
            $isPriceChange => $this->handleNewPrice($lastHistoricRecord, $stockHistory),
            $isAvailabilityChange => $this->handleNewAvailability($lastHistoricRecord, $stockHistory),
            default => throw new IllegalArgumentException('No change in price or availability. Why was this state reached?'),
        };
    }

    private function handleNewPrice(StockHistory $oldHistory, StockHistory $newHistory): void
    {
        $priceIsNowHigher = $oldHistory->getRawOriginal('price') <= $newHistory->getRawOriginal('price');

        if ($priceIsNowHigher) {
            return;
        }

        $difference = Percentage::fromDifference($oldHistory->price, $newHistory->price);

        TrackingAlert::query()->with('alertChannel')
            ->whereInterestedIn($newHistory->stock)
            ->where('percentage_trigger', '>=', $difference->value)
            ->get()
            ->each(fn(TrackingAlert $trackingAlert)
                => $trackingAlert->alertChannel->notify() // TODO: Create a notification class
            );

    }

    private function handleNewAvailability(StockHistory $oldHistory, StockHistory $newHistory): void
    {
        $itemIsNowUnavailable = $newHistory->availability === false;

    }

}
