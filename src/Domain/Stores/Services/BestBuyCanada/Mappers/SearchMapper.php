<?php

declare(strict_types=1);

namespace Domain\Stores\Services\BestBuyCanada\Mappers;

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
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockDataCollection
    {
        $allResults = $html
            ->filterXPath('//div[contains(@class, "productsRow")]')
            ->filterXPath('//div[contains(@class, "productLine")]');

        $collection = StockDataCollection::make();

        $allResults
            ->reduce(function (Crawler $crawler) {
                $href = $crawler->filterXPath('//a[contains(@class, "link")]/@href')->text();
                return Collection::make(explode('/', $href))
                    ->filter(fn (string $segment) => is_numeric($segment) && Str::of($segment)->length() === 8)
                    ->count() === 1;
            })
            ->each(function (Crawler $crawler) use ($collection) {
                $price = $this->price($crawler);
                $itemName = $this->itemName($crawler);
                $sku = $this->sku($crawler);
                $availability = $this->availability($crawler);

                $collection->push(
                    new StockData(
                        $itemName,
                        new Uri("https://www.bestbuy.ca/en-ca/product/{$sku}"),
                        Store::BestBuyCanada,
                        $price,
                        $availability,
                        $sku,
                    )
                );
            });

        return $collection;
    }

    private function price(Crawler $rootHtml): Price
    {
        $price = $rootHtml->filterXPath('//div[contains(@class, "productPricingContainer")]')
            ->filterXPath('//span[contains(@class, "screenReaderOnly")]')
            ->text();

        $exploded = explode('.', Str::of($price)->replaceMatches('/[^0-9.]/', '')->value());
        $basePrice = $exploded[0];
        Assert::integerish($basePrice);

        if (count($exploded) === 2) {
            $fractionalPrice = $exploded[1];
            Assert::integerish($fractionalPrice);
        }

        return new Price(
            (int) $basePrice,
            Currency::CAD,
            isset($fractionalPrice) ? (int) $fractionalPrice : null,
        );
    }

    private function itemName(Crawler $rootHtml): string
    {
        $itemName = $rootHtml->filterXPath('//div[contains(@class, "productItemName")]')->text();

        Assert::minLength(trim($itemName), 2);

        return trim($itemName);
    }

    private function availability(Crawler $rootHtml): bool
    {
        $availability = $rootHtml->filterXPath('//div[contains(@class, "productItemTextContainer")]')
            ->filterXPath('//p[contains(@class, "shippingAvailability")]')->text();

        return Str::of($availability)->lower()->contains('available');
    }

    private function sku(Crawler $rootHtml): string
    {
        $href = $rootHtml->filterXPath('//a[contains(@class, "link")]/@href')->text();

        return Collection::make(explode('/', $href))
            ->filter(fn (string $segment) => is_numeric($segment) && Str::of($segment)->length() === 8)
            ->sole();
    }
}
