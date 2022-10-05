<?php

declare(strict_types=1);

namespace Domain\Stocks\Actions;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\TrackingRequests\Models\TrackingRequest;
use InvalidArgumentException;

class CreateOrUpdateStocksForTrackingRequestAction
{
    public function __invoke(StockData|StockSearchData $data, TrackingRequest $trackingRequest): void
    {
        match (true) {
            $data instanceof StockSearchData => $this->handleSearchStock($data, $trackingRequest),
            $data instanceof StockData => $this->handleProductStock($data, $trackingRequest),
        };

        $trackingRequest->touch();
    }

    private function handleSearchStock(StockSearchData $data, TrackingRequest $trackingRequest): void
    {
        $data->stocks->each(function (StockData $stockData) use ($trackingRequest, $data) {
            if ($stockData->price !== null) {
                $price = $stockData->price->amount;
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
                    'image' => $data->imagePath,
                    'title' => $stockData->title,
                ]
            );
        });
    }

    private function handleProductStock(StockData $data, TrackingRequest $trackingRequest): void
    {
        if ($data->price !== null) {
            $price = $data->price->amount;
        }

        $trackingRequest->stocks()->updateOrCreate(
            [
                'sku' => $data->sku,
                'store' => $data->store,
            ],
            [
                'price' => $price ?? null,
                'availability' => $data->available,
                'url' => (string) $data->link,
                'image' => $data->imagePath,
                'title' => $data->title,
            ]
        );
    }
}
