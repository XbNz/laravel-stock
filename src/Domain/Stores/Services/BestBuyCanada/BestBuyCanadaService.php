<?php

declare(strict_types=1);

namespace Domain\Stores\Services\BestBuyCanada;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Exceptions\MapperException;
use Domain\Stores\Services\BestBuyCanada\Mappers\ProductMapper;
use Domain\Stores\Services\BestBuyCanada\Mappers\SearchMapper;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Support\Contracts\StoreContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class BestBuyCanadaService implements StoreContract
{
    public function __construct(
        private readonly BrowserShot $client,
        private readonly ProductMapper $productMapper,
        private readonly SearchMapper $searchMapper,
    ) {
    }

    public function product(UriInterface $uri): StockData
    {
        Assert::contains($uri->getHost(), 'bestbuy.ca');
        $link = (string) $uri;
        $browserShot = $this->client->setUrl($link);
        $prefix = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_prefix');
        $extension = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_format');
        $screenshot = storage_path('app/tmp/' . $prefix . Str::random(10) . '.' . $extension);
        $browserShot->save($screenshot);
        $html = new Crawler($browserShot->bodyHtml());

        try {
            $product = $this->productMapper->map($html, $uri, $screenshot);
        } catch (Exception $e) {
            throw new MapperException(
                "Failed to map product info for {$uri}",
                previous: $e,
            );
        }

        return $product;
    }

    public function search(UriInterface $uri): StockSearchData
    {
        Assert::contains($uri->getHost(), 'bestbuy.ca');
        $link = (string) $uri;
        $browserShot = $this->client->setUrl($link);
        $prefix = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_prefix');
        $extension = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_format');
        $screenshot = storage_path('app/tmp/' . $prefix . Str::random(10) . '.' . $extension);
        $browserShot->fullPage()->save($screenshot);
        $html = new Crawler($browserShot->bodyHtml());

        try {
            $stockDataCollection = $this->searchMapper->map($html, $uri, $screenshot);
        } catch (Exception $e) {
            throw new MapperException(
                "Failed to map search results for {$uri}",
                previous: $e,
            );
        }

        return new StockSearchData(
            $uri,
            $stockDataCollection,
            $screenshot
        );
    }
}
