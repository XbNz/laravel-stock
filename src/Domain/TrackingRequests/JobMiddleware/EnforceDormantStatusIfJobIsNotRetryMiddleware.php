<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\JobMiddleware;

use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class EnforceDormantStatusIfJobIsNotRetryMiddleware
{
    public function __construct(public readonly TrackingRequest $trackingRequest)
    {
    }

    public function handle(ProcessStoreServiceCallJob $job, callable $next): void
    {
        if ($job->attempts() !== 1) {
            $next($job);
        }

        if (! $this->trackingRequest->status->equals(DormantState::class)) {
            Log::warning('Tracking request in non dormant state was attempted to be processed by job. Deleting', [
                'trackingRequest' => $this->trackingRequest->id,
            ]);
            $job->delete();
            throw new Exception('Tracking request in non dormant state was attempted to be processed by job');
        }
    }
}
