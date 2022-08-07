<?php

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

class SearchMapper implements MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockDataCollection
    {
        $allResults = $html
            ->filterXPath('//div[contains(@class, "item-container")]');

        $collection = StockDataCollection::make();

        $allResults
            ->each(function (Crawler $crawler) use ($collection) {

                $title = $crawler->filterXPath('//a[contains(@class, "item-title")]')->text();
                $priceContainer = $crawler->filterXPath('//ul[contains(@class, "price")]')
                    ->filterXPath('//li[contains(@class, "price-current")]');

                $priceBase = $priceContainer->filterXPath('//strong')->text();
                $priceFractional = $priceContainer->filterXPath('//sup')->text();

                $priceBaseStripped = Str::of($priceBase)->replaceMatches('/[^0-9.]/', '')->value();
                $priceFractionalStripped = Str::of($priceFractional)->replaceMatches('/\D/', '')->value();

                $uri = new Uri($crawler->filterXPath('//a[contains(@class, "item-img")]/@href')->text());
                $path = Collection::make(
                    explode(
                        '/',
                        $uri->getPath()
                    )
                );

                $promoSection = $crawler->filterXPath('//p[contains(@class, "item-promo")]');

                if ($promoSection->count() > 0) {
                    $availability = $crawler->filterXPath('//p[contains(@class, "item-promo")]')->text();
                    $availabilityBoolean = Str::of($availability)->lower()->contains('out of stock');
                }

                $skuPath = $path->search('p', true);
                $sku = $path[$skuPath + 1];

                $collection->push(
                    new StockData(
                        trim($title),
                        $uri,
                        Store::NeweggCanada,
                        new Price(
                            (int) $priceBaseStripped,
                            Currency::CAD,
                            (int) $priceFractionalStripped,
                        ),
                        $availabilityBoolean ?? true,
                        trim($sku),
                    ),
                );
            });

        return $collection;
    }
}
