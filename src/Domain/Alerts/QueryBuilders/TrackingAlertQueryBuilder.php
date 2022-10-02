<?php

namespace Domain\Alerts\QueryBuilders;


use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Illuminate\Database\Eloquent\Builder;


/**
 * @template TModelClass of TrackingAlert
 * @extends Builder<TrackingAlert>
 */
class TrackingAlertQueryBuilder extends Builder
{

    /**
     * @return self<TModelClass>
     */
    public function whereInterestedIn(Stock $stock): self
    {
        return $this->whereHas('trackingRequests', function (Builder $query) use ($stock) {
            $query->whereHas('stocks', function ($query) use ($stock) {
                $query->where('stocks.id', $stock->id);
            });
        });
    }

}
