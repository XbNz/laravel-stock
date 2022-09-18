<?php

namespace Domain\TrackingRequests\Actions;

use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Database\Eloquent\Builder;

class DestroyTrackingRequestAction
{
    public function __invoke(TrackingRequest $trackingRequest): void
    {
        $this->detachStockFromUserIfOnlyOneRemainingTrackingRequestIsAttached($trackingRequest);

        $trackingRequest->stocks()->detach();
        $trackingRequest->delete();
    }

    public function detachStockFromUserIfOnlyOneRemainingTrackingRequestIsAttached(TrackingRequest $trackingRequest): void
    {
        $stocks = $trackingRequest->stocks()
            ->withCount('trackingRequests')
            ->where('tracking_requests_count', '=', 1)
            ->get();

        $stocks->each(fn (Stock $stock) => $stock->users()->detach($trackingRequest->user->id));
    }


}
