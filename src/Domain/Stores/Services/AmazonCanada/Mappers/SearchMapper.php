<?php

namespace Domain\Stores\Services\AmazonCanada\Mappers;

use Domain\Stores\Collections\StockDataCollection;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MappableContract;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;

class SearchMapper implements MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockDataCollection
    {
        $allResults = $html->filterXPath('//div[contains(@class, "s-result-list")]/div[contains(@data-component-type, "s-search-result")]');

        $collection = StockDataCollection::make();

        $allResults->each(function (Crawler $crawler) use ($collection) {
            $itemName = $crawler->filterXPath('//div[contains(@class, "s-title-instructions-style")]')
                ->filterXPath('//span[contains(@class, "a-text-normal")]')
                ->text();

            $priceWhole = rescue(
                fn() => $crawler->filterXPath('//span[contains(@class, "price-whole")]')->text(),
                fn() => null,
            );
            $priceFraction = rescue(
                fn() => $crawler->filterXPath('//span[contains(@class, "price-fraction")]')->text(),
                fn() => null,
            );

            $sku = $crawler->filterXPath('//div[contains(@data-asin, "")]')->attr('data-asin');

            $trimmedItemName = trim($itemName);

            if (!is_null($priceWhole)) {
                $priceWholeNumericOnly = preg_replace('/\D/', '', trim($priceWhole));
                $trimmedPriceFraction = trim($priceFraction);
                $availability = true;
                $priceObject = new Price(
                    (int) $priceWholeNumericOnly,
                    Currency::CAD,
                    (int) $trimmedPriceFraction,
                );
            }

            $trimmedSku = trim($sku);

            $collection->push(
                new StockData(
                    $itemName,
                    new Uri("https://www.amazon.ca/dp/{$trimmedSku}"),
                    Store::AmazonCanada,
                    $availability ?? false,
                    $priceObject ?? null,
                    sku: $trimmedSku
                )
            );
        });

        return $collection;
    }
}
