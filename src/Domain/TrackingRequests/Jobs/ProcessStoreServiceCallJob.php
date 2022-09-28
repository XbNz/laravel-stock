<?php

namespace Domain\TrackingRequests\Jobs;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\FailedState;
use Domain\TrackingRequests\States\InProgressState;
use Domain\Users\Models\User;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Support\Contracts\StoreContract;
use Webmozart\Assert\Assert;

class ProcessStoreServiceCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<StoreContract> $storeServices
     */
    public function __construct(
        private readonly EloquentCollection $trackingRequests,
        private readonly User $user,
        private readonly array $storeServices,
    ) {
    }

    public function handle()
    {
        $trackingRequests = $this->trackingRequests
            ->reject(fn(TrackingRequest $trackingRequest) => $trackingRequest->status->equals(InProgressState::class))
            ->reject(fn(TrackingRequest $trackingRequest) => $trackingRequest->status->equals(FailedState::class));

        $trackingRequests
            ->each(fn(TrackingRequest $trackingRequest)
                => $trackingRequest->status->transitionTo(InProgressState::class)
            );

        $trackingRequests
            ->groupBy([
                fn (TrackingRequest $trackingRequest) => $trackingRequest->store->value,
                fn (TrackingRequest $trackingRequest) => $trackingRequest->tracking_type->value,
            ])
            ->each(function (Collection $trackingRequests, string $store) {

                $storeService = Collection::make($this->storeServices)->sole(fn (StoreContract $storeService)
                    => $storeService->supports(Store::from($store))
                );

                [$search, $singleProduct] = $trackingRequests->partition(fn (EloquentCollection $trackingRequests, string $trackingType)
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


        $trackingRequests->each(fn(TrackingRequest $trackingRequest) => $trackingRequest
            ->status
            ->transitionTo(DormantState::class)
        );
    }

    private function handleSingleProduct(array $stockData, User $user): void
    {
        Assert::allIsInstanceOf($stockData, StockData::class);

        \Illuminate\Support\Collection::make($stockData)
            ->each(function (StockData $stockData) use ($user) {
                $trackingRequest = $user->trackingRequests()->where('url', (string) $stockData->link)->sole();

                if ($stockData->price !== null) {
                    $basePrice = $stockData->price->baseAmount;
                    $fractionalPrice = $stockData->price->fractionalAmount ?? 00; //todo: find a better way to handle this
                    $price = $basePrice . $fractionalPrice;
                }

                $trackingRequest->stocks()->updateOrCreate(
                    [
                        'sku' => $stockData->sku,
                        'store' => $stockData->store,
                    ],
                    [
                        'price' => $price ?? null,
                        'availability' => $stockData->available,
                        'url' => (string) $stockData->link,
                        'image' => $stockData->imagePath,
                        'title' => $stockData->title,
                    ]
                );
            });
    }

    private function handleSearch(array $searchData, User $user): void
    {
        Assert::allIsInstanceOf($searchData, StockSearchData::class);
    }


    public function failed(Exception $exception): void
    {
        $this->trackingRequests->loadCount('stocks');

        $this->trackingRequests
            ->filter(fn (TrackingRequest $trackingRequest) => $trackingRequest->stocks_count === 0)
            ->each(fn (TrackingRequest $trackingRequest) => $trackingRequest->status->transitionTo(FailedState::class));
    }

}
