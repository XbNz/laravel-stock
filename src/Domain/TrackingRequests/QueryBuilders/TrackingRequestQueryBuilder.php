<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\QueryBuilders;

use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Illuminate\Database\Eloquent\Builder;
use Psr\Http\Message\UriInterface;

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

    /**
     * @return self<TModelClass>
     */
    public function whereStock(Stock $stock): self
    {
        return $this->whereHas('stocks', function (Builder $query) use ($stock) {
            $query->where('id', $stock->id);
        });
    }

    //url

    /**
     * @return self<TModelClass>
     */
    public function whereUrl(UriInterface $url): self
    {
        return $this->where('url', $url);
    }

    public function needsUpdate(): self
    {
        return $this->where('status', DormantState::class)
            ->whereColumn('updated_at', '<', $this->raw('DATE_SUB(NOW(), INTERVAL update_interval SECOND)'));
    }
}
