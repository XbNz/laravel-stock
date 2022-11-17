<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\JobMiddleware;

use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\InProgressState;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class EnforceInProgressState
{
    public function __construct(private readonly TrackingRequest $trackingRequest)
    {
    }

    public function handle(ProcessStoreServiceCallJob $job, callable $next): void
    {
        if (! $this->trackingRequest->status->equals(InProgressState::class)) {
            $job->delete();
        }

        $next($job);
    }
}
