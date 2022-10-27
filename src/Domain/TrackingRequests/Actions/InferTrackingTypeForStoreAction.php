<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\Stores\Enums\Store;
use Domain\TrackingRequests\Enums\TrackingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class InferTrackingTypeForStoreAction
{
    public function __invoke(Store $store, UriInterface $uri): TrackingRequest
    {
        return match ($store) {
            Store::AmazonCanada => $this->inferTrackingTypeForAmazonCanada($uri),
            Store::AmazonUs => $this->inferTrackingTypeForAmazonUs($uri),
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

    private function inferTrackingTypeForAmazonUs(UriInterface $uri): TrackingRequest
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

        $path = Collection::make($explodedPath)->search('p', true);

        if ($path === false) {
            return TrackingRequest::Search;
        }

        if (array_key_exists($path + 1, $explodedPath) === false) {
            return TrackingRequest::Search;
        }

        $hasSkuAfterPath = Str::of($explodedPath[$path + 1])->length() > 6;

        if ($hasSkuAfterPath === false) {
            return TrackingRequest::Search;
        }

        return TrackingRequest::SingleProduct;
    }
}
