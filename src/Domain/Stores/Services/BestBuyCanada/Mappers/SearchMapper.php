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
                $href = $crawler->filterXPath('//a[contains(@class, "link")]/@href')->text();

                $sku = Collection::make(explode('/', $href))
                    ->filter(fn (string $segment) => is_numeric($segment) && Str::of($segment)->length() === 8)
                    ->sole();

                $stockUri = new Uri("https://www.bestbuy.ca/en-ca/product/{$sku}");

                $crawler = $crawler->filterXPath('//div[contains(@class, "productItemTextContainer")]');

                $price = $crawler->filterXPath('//div[contains(@class, "productPricingContainer")]')
                    ->filterXPath('//span[contains(@class, "screenReaderOnly")]')
                    ->text();

                [$basePrice, $fractionalPrice] = explode('.', Str::of($price)->replaceMatches('/[^0-9.]/', '')->value());

                $itemName = $crawler->filterXPath('//div[contains(@class, "productItemName")]')->text();

                $availability = $crawler->filterXPath('//p[contains(@class, "shippingAvailability")]')->text();
                $availabilityBoolean = Str::of($availability)->lower()->contains('available');

                $collection->push(
                    new StockData(
                        $itemName,
                        $stockUri,
                        Store::BestBuyCanada,
                        new Price(
                            (int) $basePrice,
                            Currency::CAD,
                            (int) $fractionalPrice
                        ),
                        $availabilityBoolean,
                        $sku,
                    )
                );
            });

        return $collection;
    }
}
