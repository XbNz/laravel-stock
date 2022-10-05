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

    /**
     * @param EloquentCollection<TrackingRequest> $trackingRequests
     */
    public function __invoke(EloquentCollection $trackingRequests): void
    {
        $trackingRequests->load('user');

        $trackingRequests
            ->groupBy(fn (TrackingRequest $trackingRequest) => $trackingRequest->user->id)
            ->each(function (EloquentCollection $trackingRequests, int $userId) {
                $user = User::query()->findOrFail($userId);

                $this->dispatcher->dispatch(new ProcessStoreServiceCallJob(
                    $trackingRequests,
                    $user,
                    $this->storeServices,
                ));
            });
    }
}
