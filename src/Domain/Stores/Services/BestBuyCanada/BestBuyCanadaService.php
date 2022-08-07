<?php

namespace Domain\Stores\Services\BestBuyCanada;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Services\BestBuyCanada\Mappers\ProductMapper;
use Domain\Stores\Services\BestBuyCanada\Mappers\SearchMapper;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\File;
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
        $screenshot = $browserShot->screenshot();
        $html = new Crawler($browserShot->bodyHtml());

        return $this->productMapper->map($html, $uri, $screenshot);
    }

    public function search(string $term): StockSearchData
    {
        $uri = new Uri("https://www.bestbuy.ca/en-ca/search?search={$term}");
        $browserShot = $this->client->setUrl((string) $uri);
        $screenshot = $browserShot->fullPage()->screenshot();
        $html = new Crawler($browserShot->bodyHtml());
        $stockDataCollection = $this->searchMapper->map($html, $uri, $screenshot);

        return new StockSearchData(
            $uri,
            $term,
            $stockDataCollection,
            $screenshot
        );
    }
}
