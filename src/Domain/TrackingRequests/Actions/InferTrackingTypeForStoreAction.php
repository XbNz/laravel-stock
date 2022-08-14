<?php

namespace Domain\TrackingRequests\Actions;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Enums\TrackingRequest;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class InferTrackingTypeForStoreAction
{
    public function __invoke(Store $store, UriInterface $uri): TrackingRequest
    {
        return match ($store) {
            Store::AmazonCanada => $this->inferTrackingTypeForAmazonCanada($uri),
            Store::BestBuyCanada => $this->inferTrackingTypeForBestBuyCanada($uri),
            Store::NeweggCanada => $this->inferTrackingTypeForNeweggCanada($uri),
            default => throw new InvalidArgumentException("Cannot infer tracking type for {$uri}"),
        };
    }

    private function inferTrackingTypeForAmazonCanada(UriInterface $uri): TrackingRequest
    {
        $path = $uri->getPath();
        $explodedPath = explode('/', $path);

        if (Collection::make($explodedPath)->search('dp', true) === false) {
            return TrackingRequest::Search;
        }

        return TrackingRequest::SingleProduct;
    }

    private function inferTrackingTypeForBestBuyCanada(UriInterface $uri): TrackingRequest
    {
        $path = $uri->getPath();
        $explodedPath = explode('/', $path);

        if (Collection::make($explodedPath)->search('product', true) === false) {
            return TrackingRequest::Search;
        }

        return TrackingRequest::SingleProduct;
    }

    private function inferTrackingTypeForNeweggCanada(UriInterface $uri): TrackingRequest
    {
        $path = $uri->getPath();
        $explodedPath = explode('/', $path);

        if (Collection::make($explodedPath)->search('p', true) === false) {
            return TrackingRequest::Search;
        }

        return TrackingRequest::SingleProduct;
    }

}
