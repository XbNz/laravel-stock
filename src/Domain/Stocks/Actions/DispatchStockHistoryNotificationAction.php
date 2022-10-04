<?php

namespace Domain\Stocks\Actions;

use DASPRiD\Enum\Exception\IllegalArgumentException;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\StockHistory;
use Domain\Stocks\Notifications\StockAvailabilityNotification;
use Domain\Stocks\Notifications\StockPriceNotification;
use Support\ValueObjects\Percentage;
use Webmozart\Assert\Assert;

class DispatchStockHistoryNotificationAction
{
    public function __invoke(StockHistory $stockHistory): void
    {
        $stockHistory = $stockHistory->fresh();

        if ($stockHistory->stock->histories()->count() === 1) {
            return;
        }

        $lastHistoricRecord = $stockHistory->stock
            ->histories()
            ->orderBy('created_at', 'desc')
            ->skip(1)
            ->take(1)
            ->first();

        Assert::notNull($lastHistoricRecord);

        Assert::integer($lastHistoricRecord?->getRawOriginal('price'));
        Assert::integer($stockHistory?->getRawOriginal('price'));
        Assert::integer($lastHistoricRecord?->getRawOriginal('availability'));
        Assert::integer($stockHistory?->getRawOriginal('availability'));

        $priceIsNowLower = $lastHistoricRecord->getRawOriginal('price') > $stockHistory->getRawOriginal('price');
        $availabilityWasFalseAndIsNowTrue = $lastHistoricRecord->availability === false && $stockHistory->availability === true;

        if ($priceIsNowLower === false && $availabilityWasFalseAndIsNowTrue === false) {
            return;
        }

        if ($priceIsNowLower) {
            $this->handleNewPrice($lastHistoricRecord, $stockHistory);
        }

        if ($availabilityWasFalseAndIsNowTrue) {
            $this->handleNewAvailability($stockHistory);
        }
    }

    private function handleNewPrice(StockHistory $oldHistory, StockHistory $newHistory): void
    {
        $difference = Percentage::fromDifference($oldHistory->getRawOriginal('price'), $newHistory->getRawOriginal('price'));

        TrackingAlert::query()->with('alertChannel')
            ->whereInterestedIn($newHistory->stock)
            ->where('percentage_trigger', '<=', $difference->value)
            ->get()
            ->each(fn (TrackingAlert $trackingAlert)
                => $trackingAlert->alertChannel->notify(new StockPriceNotification($oldHistory, $newHistory))
            );
    }

    private function handleNewAvailability(StockHistory $newHistory): void
    {
        TrackingAlert::query()->with('alertChannel')
            ->whereInterestedIn($newHistory->stock)
            ->where('availability_trigger', true)
            ->get()
            ->each(fn (TrackingAlert $trackingAlert)
                => $trackingAlert->alertChannel->notify(new StockAvailabilityNotification($newHistory))
            );
    }

}
