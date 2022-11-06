<?php

declare(strict_types=1);

namespace Domain\Stores\Services\AmazonCanada\Mappers;

use Domain\Stores\Collections\StockDataCollection;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class SearchMapper implements MapperContract
{
    /**
     * @return StockDataCollection<int, StockData>
     */
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockDataCollection
    {
        $allResults = $html->filterXPath('//div[contains(@class, "s-result-list")]/div[contains(@data-component-type, "s-search-result")]');

        $collection = StockDataCollection::make();

        $allResults->each(function (Crawler $crawler) use ($collection) {
            $price = $this->price($crawler);
            $itemName = $this->itemName($crawler);

            if ($price instanceof Price) {
                $availability = true;
            }

            $sku = $this->sku($crawler);

            $collection->push(
                new StockData(
                    $itemName,
                    new Uri("https://www.amazon.ca/dp/{$sku}"),
                    Store::AmazonCanada,
                    $price ?? new Price(0, Currency::CAD),
                    $availability ?? false,
                    $sku,
                )
            );
        });

        return $collection;
    }

    private function price(Crawler $rootHtml): ?Price
    {
        try {
            $priceWhole = $rootHtml->filterXPath('//span[contains(@class, "price-whole")]')->text();
        } catch (InvalidArgumentException $e) {
            $priceWhole = null;
        }

        try {
            $priceFraction = $rootHtml->filterXPath('//span[contains(@class, "price-fraction")]')->text();
        } catch (InvalidArgumentException $e) {
            $priceFraction = null;
        }

        if ($priceWhole !== null) {
            $priceWholeNumericOnly = preg_replace('/\D/', '', trim($priceWhole));
            Assert::integerish($priceWholeNumericOnly);
        }

        if ($priceFraction !== null) {
            $priceFractionNumericOnly = preg_replace('/\D/', '', trim($priceFraction));
            Assert::integerish($priceFractionNumericOnly);
        }

        if ($priceWhole !== null) {
            $price = $priceWholeNumericOnly;
            $price .= $priceFractionNumericOnly ?? '00';

            $priceObject = new Price(
                (int) $price,
                Currency::CAD,
            );
        }

        return $priceObject ?? null;
    }

    private function itemName(Crawler $rootHtml): string
    {
        $itemName = $rootHtml->filterXPath('//div[contains(@class, "s-title-instructions-style")]')
            ->filterXPath('//span[contains(@class, "a-text-normal")]')
            ->text();

        Assert::minLength($itemName, 2);
        return $itemName;
    }

    private function sku(Crawler $rootHtml): string
    {
        $asin = $rootHtml->filterXPath('//div[contains(@data-asin, "")]')->attr('data-asin');
        Assert::string($asin);
        Assert::minLength($asin, 2);
        return trim($asin);
    }
}
