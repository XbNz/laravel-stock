<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Jobs;

use Domain\Stocks\Actions\CreateOrUpdateStocksForTrackingRequestAction;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\ConfidenceOfTrackingRequestHealthAction;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\TrackingRequests\JobMiddleware\EnforceDormantStatusIfJobIsNotRetryMiddleware;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\FailedState;
use Domain\TrackingRequests\States\InProgressState;
use Domain\TrackingRequests\States\RecoveryState;
use Domain\Users\Models\User;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Support\Contracts\StoreContract;
use Throwable;
use Webmozart\Assert\Assert;

class ProcessStoreServiceCallJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 8;

    public int $timeout = 600;

    /**
     * @param array<StoreContract> $storeServices
     */
    public function __construct(
        private EloquentCollection $trackingRequests,
        private readonly User $user,
        private readonly array $storeServices,
    ) {
    }

    public function middleware(): array
    {
        return [
            new EnforceDormantStatusIfJobIsNotRetryMiddleware($this->trackingRequests),
        ];
    }

    public function backoff(): array
    {
        return [5, 15, 50, 100, 600, 3600, 86400];
    }

    public function handle()
    {
        $trackingRequests = $this->trackingRequests;

        $trackingRequests
            ->filter(fn (TrackingRequest $trackingRequest) => $trackingRequest->status->canTransitionTo(InProgressState::class))
            ->each(
                fn (TrackingRequest $trackingRequest) => $trackingRequest->status->transitionTo(InProgressState::class)
            );

        $trackingRequests
            ->groupBy([
                fn (TrackingRequest $trackingRequest) => $trackingRequest->store->value,
                fn (TrackingRequest $trackingRequest) => $trackingRequest->tracking_type->value,
            ])
            ->each(function (Collection $trackingRequests, string $store) {
                $storeService = Collection::make($this->storeServices)->sole(
                    fn (StoreContract $storeService)
                    => $storeService->supports(Store::from($store))
                );

                [$search, $singleProduct] = $trackingRequests->partition(
                    fn (EloquentCollection $trackingRequests, string $trackingType)
                    => TrackingRequestEnum::from($trackingType) === TrackingRequestEnum::Search
                );

                $search->each(function (EloquentCollection $trackingRequests) use ($storeService) {
                    $urls = $trackingRequests->pluck('url')->map(fn (string $url) => new Uri($url));
                    $stockSearchData = $storeService->search($urls->toArray());
                    $this->handleSearch($stockSearchData, $this->user);
                });

                $singleProduct->each(function (EloquentCollection $trackingRequests) use ($storeService) {
                    $urls = $trackingRequests->pluck('url')->map(fn (string $url) => new Uri($url));
                    $stockData = $storeService->product($urls->toArray());
                    $this->handleSingleProduct($stockData, $this->user);
                });
            });

        $trackingRequests->each(
            fn (TrackingRequest $trackingRequest) => $trackingRequest
                ->status
                ->transitionTo(DormantState::class)
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::warning('ProcessStoreServiceCallJob failed', [
            'trackingRequests' => $this->trackingRequests->toArray(),
            'user' => $this->user->toArray(),
            'storeServices' => $this->storeServices,
            'exception' => $exception->getMessage(),
        ]);

        [$toBeFailed, $sentToRecovery] = $this->trackingRequests
            ->partition(
                fn (TrackingRequest $trackingRequest)
                => app(ConfidenceOfTrackingRequestHealthAction::class)($trackingRequest)->lessThan(30)
            );

        $toBeFailed->each(function (TrackingRequest $trackingRequest) {
            $trackingRequest->status->transitionTo(FailedState::class);
        });

        $sentToRecovery->each(fn (TrackingRequest $trackingRequest) => $trackingRequest->status->transitionTo(RecoveryState::class));
    }

    private function handleSingleProduct(array $stockData, User $user): void
    {
        Assert::allIsInstanceOf($stockData, StockData::class);

        Collection::make($stockData)
            ->each(function (StockData $stock) use ($user) {
                $trackingRequest = $user->trackingRequests()->whereUrl($stock->link)->sole();
                app(CreateOrUpdateStocksForTrackingRequestAction::class)($stock, $trackingRequest);
            });
    }

    private function handleSearch(array $searchData, User $user): void
    {
        Assert::allIsInstanceOf($searchData, StockSearchData::class);

        Collection::make($searchData)
            ->each(function (StockSearchData $searchData) use ($user) {
                $trackingRequest = $user->trackingRequests()->where('url', $searchData->uri)->sole();
                app(CreateOrUpdateStocksForTrackingRequestAction::class)($searchData, $trackingRequest);
            });
    }
}
