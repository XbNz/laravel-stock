<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Jobs;

use Domain\Stocks\Actions\CreateOrUpdateStocksForTrackingRequestAction;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Actions\ConfidenceOfTrackingRequestHealthAction;
use Domain\TrackingRequests\Enums\TrackingRequest as TrackingRequestEnum;
use Domain\TrackingRequests\JobMiddleware\EnforceInProgressState;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\FailedState;
use Domain\TrackingRequests\States\InProgressState;
use Domain\TrackingRequests\States\RecoveryState;
use Domain\Users\Models\User;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
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
        private readonly TrackingRequest $trackingRequest,
        private readonly array $storeServices,
    ) {
    }

    public function backoff(): array
    {
        return [5, 100, 300, 900, 4500, 7600, 86400];
    }

    public function middleware(): array
    {
        return [
            new EnforceInProgressState($this->trackingRequest),
        ];
    }

    public function handle()
    {
        Assert::true($this->trackingRequest->status->equals(InProgressState::class));

        $store = $this->trackingRequest->store;

        /** @var StoreContract $storeService */
        $storeService = Collection::make($this->storeServices)->sole(
            fn (StoreContract $storeService)
            => $storeService->supports($store)
        );

        if ($this->trackingRequest->tracking_type === TrackingRequestEnum::Search) {
            $searchDataArray = $storeService->search([new Uri($this->trackingRequest->url)]);
            $this->handleSearch($searchDataArray);
        }

        if ($this->trackingRequest->tracking_type === TrackingRequestEnum::SingleProduct) {
            $stockDataArray = $storeService->product([new Uri($this->trackingRequest->url)]);
            $this->handleSingleProduct($stockDataArray);
        }

        $this->trackingRequest->status->transitionTo(DormantState::class);
    }

    public function failed(Throwable $exception): void
    {
        Log::warning('ProcessStoreServiceCallJob failed', [
            'trackingRequest' => $this->trackingRequest,
            'storeServices' => $this->storeServices,
            'exception' => $exception->getMessage(),
        ]);

        if (app(ConfidenceOfTrackingRequestHealthAction::class)($this->trackingRequest)->lessThan(30)) {
            $this->trackingRequest->status->transitionTo(FailedState::class);
        } else {
            $this->trackingRequest->status->transitionTo(RecoveryState::class);
        }
    }

    private function handleSingleProduct(array $stockData): void
    {
        Assert::allIsInstanceOf($stockData, StockData::class);

        Collection::make($stockData)
            ->each(function (StockData $stock) {
                app(CreateOrUpdateStocksForTrackingRequestAction::class)($stock, $this->trackingRequest);
            });
    }

    private function handleSearch(array $searchData): void
    {
        Assert::allIsInstanceOf($searchData, StockSearchData::class);

        Collection::make($searchData)
            ->each(function (StockSearchData $searchData) {
                app(CreateOrUpdateStocksForTrackingRequestAction::class)($searchData, $this->trackingRequest);
            });
    }
}
