<?php

namespace Domain\TrackingRequests\Jobs;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Support\Contracts\StoreContract;
use Webmozart\Assert\Assert;

class ProcessStoreServiceCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Collection $searchTrackingRequests,
        private readonly Collection $productTrackingRequests,
        private readonly User $user,
        private readonly StoreContract $storeService,
    ) {
    }

    public function handle()
    {
        $trackingRequests = $this->searchTrackingRequests->merge($this->productTrackingRequests);

        $trackingRequests->each(fn(TrackingRequest $trackingRequest) => $trackingRequest
            ->status
            ->transitionTo(InProgressState::class)
        );

        $this->searchTrackingRequests->each(function (EloquentCollection $trackingRequests) {
            $urls = $trackingRequests->pluck('url')->map(fn (string $url) => new Uri($url));
            $stockSearchData = $this->storeService->search($urls->toArray());
            $this->handleSearch($stockSearchData, $this->user);
        });

        // TODO: Test this job class. Clean up imports

        $this->productTrackingRequests->each(function (EloquentCollection $trackingRequests) {
            $urls = $trackingRequests->pluck('url')->map(fn (string $url) => new Uri($url));
            $stockData = $this->storeService->product($urls->toArray());
            $this->handleSingleProduct($stockData, $this->user);
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
