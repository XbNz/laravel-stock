<?php

namespace Domain\TrackingRequests\QueryBuilders;

use Domain\Alerts\Models\TrackingAlert;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of TrackingRequest
 * @extends Builder<TrackingRequest>
 */
class TrackingRequestQueryBuilder extends Builder
{
    /**
     * @return self<TModelClass>
     */
    public function whereHasAlert(TrackingAlert $trackingAlert): self
    {
        return $this->whereHas('trackingAlerts', function (Builder $query) use ($trackingAlert) {
            $query->where('id', $trackingAlert->id);
        });
    }
}
