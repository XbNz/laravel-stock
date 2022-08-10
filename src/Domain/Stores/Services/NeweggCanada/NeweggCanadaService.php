<?php

declare(strict_types=1);

namespace Domain\Stores\Services\NeweggCanada;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Exceptions\MapperException;
use Domain\Stores\Services\NeweggCanada\Mappers\ProductMapper;
use Domain\Stores\Services\NeweggCanada\Mappers\SearchMapper;
use Exception;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Support\Contracts\StoreContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class NeweggCanadaService implements StoreContract
{
    public function __construct(
        private readonly BrowserShot $client,
        private readonly ProductMapper $productMapper,
        private readonly SearchMapper $searchMapper,
    ) {
    }

    public function product(UriInterface $uri): StockData
    {
        Assert::contains($uri->getHost(), 'newegg.ca');
        $link = (string) $uri;
        $browserShot = $this->client->setUrl($link);
        $screenshot = $browserShot->screenshot();
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
        Assert::contains($uri->getHost(), 'newegg.ca');
        $link = (string) $uri;
        $browserShot = $this->client->setUrl($link);
        $screenshot = $browserShot->fullPage()->screenshot();
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
