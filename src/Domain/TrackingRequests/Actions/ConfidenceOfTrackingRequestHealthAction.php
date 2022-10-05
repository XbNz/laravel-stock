<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\TrackingRequests\Models\TrackingRequest;
use Support\ValueObjects\Percentage;

class ConfidenceOfTrackingRequestHealthAction
{
    public function __invoke(TrackingRequest $trackingRequest): Percentage
    {
        $percentageA = $this->otherTrackingRequestsWithThisStoreHaveBeenSuccessful($trackingRequest);
        $percentageB = $this->trackingRequestHasAtSomePointHadASuccessfulStockAssociation($trackingRequest);
        return Percentage::from($percentageA->value + $percentageB->value);
    }

    private function trackingRequestHasAtSomePointHadASuccessfulStockAssociation(TrackingRequest $trackingRequest): Percentage
    {
        return $trackingRequest->stocks()->count() > 0 ? Percentage::from(33) : Percentage::from(0);
    }

    private function otherTrackingRequestsWithThisStoreHaveBeenSuccessful(TrackingRequest $trackingRequest): Percentage
    {
        $store = $trackingRequest->store;
        $latestWithGivenStore = TrackingRequest::query()->where('store', $store)->latest()->first();

        if ($latestWithGivenStore === null) {
            return Percentage::from(0);
        }

        if ($latestWithGivenStore->is($trackingRequest)) {
            return Percentage::from(0);
        }

        return $latestWithGivenStore->updated_at->isAfter($trackingRequest->updated_at)
            ? Percentage::from(0)
            : Percentage::from(50);
    }
}
