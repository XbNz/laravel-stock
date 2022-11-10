<?php

declare(strict_types=1);

namespace Domain\Stores\Services\NeweggCanada\Mappers;

use Domain\Stores\Collections\StockDataCollection;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
        $allResults = $html
            ->filterXPath('//div[contains(@class, "item-container")]');

        $collection = StockDataCollection::make();

        $allResults
            ->reduce(function (Crawler $crawler) {
                $price = $crawler->filterXPath('//ul[contains(@class, "price")]')
                    ->filterXPath('//li[contains(@class, "price-current")]')
                    ->text();

                return Str::of($price)
                    ->trim()
                    ->replaceMatches('/[^0-9]/', '')
                    ->length() >= 1;
            })
            ->each(function (Crawler $crawler) use ($collection) {
                $price = $this->price($crawler);
                $title = $this->itemName($crawler);
                $sku = $this->sku($crawler);
                $availability = $this->availability($crawler);

                $collection->push(
                    new StockData(
                        $title,
                        new Uri("https://www.newegg.ca/p/{$sku}"),
                        Store::NeweggCanada,
                        $price,
                        $availability,
                        $sku
                    ),
                );
            });

        return $collection;
    }

    private function price(Crawler $rootHtml): Price
    {
        $priceContainer = $rootHtml->filterXPath('//ul[contains(@class, "price")]')
            ->filterXPath('//li[contains(@class, "price-current")]');

        $priceBase = $priceContainer->filterXPath('//strong')->text();
        $priceFractional = $priceContainer->filterXPath('//sup')->text();

        $priceBaseStripped = Str::of($priceBase)->replaceMatches('/[^0-9.]/', '')->value();
        $priceFractionalStripped = Str::of($priceFractional)->replaceMatches('/\D/', '')->value();

        Assert::integerish($priceBaseStripped);
        Assert::integerish($priceFractionalStripped);

        $price = $priceBaseStripped . $priceFractionalStripped;

        return new Price(
            (int) $price,
            Currency::CAD,
        );
    }

    private function itemName(Crawler $rootHtml): string
    {
        $itemName = $rootHtml->filterXPath('//a[contains(@class, "item-title")]')->text();
        Assert::minLength(trim($itemName), 2);

        return trim($itemName);
    }

    private function availability(Crawler $rootHtml): bool
    {
        $promoSection = $rootHtml->filterXPath('//p[contains(@class, "item-promo")]');

        if ($promoSection->count() > 0) {
            $availability = $rootHtml->filterXPath('//p[contains(@class, "item-promo")]')->text();
            $availabilityBoolean = Str::of($availability)->lower()->contains('out of stock');
        }

        return $availabilityBoolean ?? true;
    }

    private function sku(Crawler $rootHtml): string
    {
        $link = $rootHtml->filterXPath('//a[contains(@class, "item-img")]/@href')->text();
        $uri = new Uri($link);

        $path = Collection::make(
            explode(
                '/',
                $uri->getPath()
            )
        );

        $skuPath = $path->search('p', true);
        Assert::integer($skuPath);
        $sku = $path[$skuPath + 1];

        Assert::notNull($sku);
        Assert::minLength($sku, 2);

        return $sku;
    }
}
