<?php

declare(strict_types=1);

namespace Domain\Stores\Services\AmazonCanada\Mappers;

use Domain\Stores\Collections\StockDataCollection;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

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
                fn () => $crawler->filterXPath('//span[contains(@class, "price-whole")]')->text(),
                fn () => null,
            );

            $priceFraction = rescue(
                fn () => $crawler->filterXPath('//span[contains(@class, "price-fraction")]')->text(),
                fn () => null,
            );

            if ($priceWhole !== null) {
                $priceWholeNumericOnly = preg_replace('/\D/', '', trim($priceWhole));
                Assert::integerish($priceWholeNumericOnly);
            }

            if ($priceFraction !== null) {
                $priceFractionNumericOnly = preg_replace('/\D/', '', trim($priceFraction));
                Assert::integerish($priceFractionNumericOnly);
            }

            if ($priceWhole !== null) {
                $availability = true;
                $priceObject = new Price(
                    (int) $priceWholeNumericOnly,
                    Currency::CAD,
                    isset($priceFractionNumericOnly) ? (int) $priceFractionNumericOnly : null,
                );
            }

            $asin = $crawler->filterXPath('//div[contains(@data-asin, "")]')->attr('data-asin');
            Assert::string($asin);
            Assert::length($asin, 2);
            $sku = trim($asin);

            $collection->push(
                new StockData(
                    trim($itemName),
                    new Uri("https://www.amazon.ca/dp/{$sku}"),
                    Store::AmazonCanada,
                    $priceObject ?? null,
                    $availability ?? false,
                    $sku,
                )
            );
        });

        return $collection;
    }
}
