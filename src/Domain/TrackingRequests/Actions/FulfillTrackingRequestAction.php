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
            ->groupBy([
                fn (TrackingRequest $trackingRequest) => $trackingRequest->user->id,
                fn (TrackingRequest $trackingRequest) => $trackingRequest->store->value,
                fn (TrackingRequest $trackingRequest) => $trackingRequest->tracking_type->value,
            ])
            ->each(function (Collection $trackingRequests, int $userId) {

                $user = User::query()->findOrFail($userId);

                Collection::make($trackingRequests)
                    ->each(function (Collection $trackingRequests, string $store) use ($user) {

                        $storeService = Collection::make($this->storeServices)
                            ->sole(fn (StoreContract $storeService) => $storeService->supports(Store::from($store)));

                        [$search, $singleProduct] = $trackingRequests->partition(fn (EloquentCollection $trackingRequests, string $key)
                            => TrackingRequestEnum::from($key) === TrackingRequestEnum::Search
                        );

                        $this->dispatcher->dispatch(new ProcessStoreServiceCallJob(
                            $search,
                            $singleProduct,
                            $user,
                            $storeService,
                        ));
                    });
            });
    }

    private function handleSingleProduct(array $stockData, User $user): void
    {
        Assert::allIsInstanceOf($stockData, StockData::class);

        Collection::make($stockData)
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
}
