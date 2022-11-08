<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class FulfillTrackingRequestAction
{
    public function __construct(
        private readonly array $storeServices,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function __invoke(TrackingRequest $trackingRequest): void
    {
        $this->dispatcher->dispatch(new ProcessStoreServiceCallJob(
            $trackingRequest,
            $this->storeServices,
        ));
    }
}
