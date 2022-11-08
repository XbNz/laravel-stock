<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\InProgressState;
use Domain\Users\Models\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class FulfillTrackingRequestAction
{
    public function __construct(
        private readonly array $storeServices,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function __invoke(TrackingRequest $trackingRequest): void
    {
        if (! $trackingRequest->status->canTransitionTo(InProgressState::class)) {
            throw new InvalidArgumentException('Tracking request may not be sent to job for processing because it is not in a valid state.');
        }

        $trackingRequest->status->transitionTo(InProgressState::class);

        $this->dispatcher->dispatch(new ProcessStoreServiceCallJob(
            $trackingRequest,
            $this->storeServices,
        ));
    }
}
