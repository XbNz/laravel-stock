<?php

declare(strict_types=1);

namespace Domain\Stores\Services\AmazonCanada;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Services\AmazonCanada\Mappers\ProductMapper;
use Domain\Stores\Services\AmazonCanada\Mappers\SearchMapper;
use Domain\Stores\ValueObjects\Price;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Support\Contracts\StoreContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class AmazonCanadaService implements StoreContract
{
    public function __construct(
        private readonly BrowserShot $client,
        private readonly ProductMapper $productMapper,
        private readonly SearchMapper $searchMapper,
    ) {
    }

    public function product(UriInterface $uri): StockData
    {
        Assert::contains($uri->getHost(), 'amazon.ca');
        $link = (string) $uri;
        $browserShot = $this->client->setUrl($link);
        $screenshot = $browserShot->screenshot();
        $html = new Crawler($browserShot->bodyHtml());

        return $this->productMapper->map($html, $uri, $screenshot);
    }

    public function search(UriInterface $uri): StockSearchData
    {
        Assert::contains($uri->getHost(), 'amazon.ca');
        $link = (string) $uri;
        $browserShot = $this->client->setUrl($link);
        $screenshot = $browserShot->fullPage()->screenshot();
        $html = new Crawler($browserShot->bodyHtml());

        $stockDataCollection = $this->searchMapper->map($html, $uri, $screenshot);

        return new StockSearchData(
            $uri,
            $stockDataCollection,
            $screenshot
        );
    }
}
