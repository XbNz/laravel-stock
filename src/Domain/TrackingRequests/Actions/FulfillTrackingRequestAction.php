<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Jobs\ProcessStoreServiceCallJob;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\Users\Models\User;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Support\Contracts\StoreContract;
use Webmozart\Assert\Assert;

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
        $trackingRequests
            ->groupBy(fn (TrackingRequest $trackingRequest) => $trackingRequest->user->id)
            ->each(function (Collection $trackingRequests, int $userId) {

                $user = User::query()->findOrFail($userId);

                $this->dispatcher->dispatch(new ProcessStoreServiceCallJob(
                    $trackingRequests,
                    $user,
                    $this->storeServices,
                ));

            });
    }

}
