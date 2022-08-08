<?php

namespace Domain\Stores\Services\AmazonCanada\Mappers;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MappableContract;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;

class ProductMapper implements MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockData
    {
        $html = $html->filterXPath('//div[contains(@id, "ppd")]');
        $itemName = $html->filterXPath('//span[contains(@id, "productTitle")]')->text();
        $priceWhole = rescue(
            fn() => $html->filterXPath('//span[contains(@class, "price-whole")]')->text(),
            fn() => null,
        );
        $priceFraction = rescue(
            fn() => $html->filterXPath('//span[contains(@class, "price-fraction")]')->text(),
            fn() => null,
        );


        if (!is_null($priceWhole)) {
            $priceWholeNumericOnly = preg_replace('/\D/', '', trim($priceWhole));
            $trimmedPriceFraction = trim($priceFraction);
            $priceObject = new Price(
                (int) $priceWholeNumericOnly,
                Currency::CAD,
                (int) $trimmedPriceFraction,
            );
        }

        $availability = $html->filterXPath(
            '//div[contains(@id, "availability")]/span[contains(@class, "medium")]'
        )->text();

        $path = explode('/', $searchUri->getPath());
        $positionOfSkuPath = Collection::make($path)->search('dp', true);
        $sku = $path[$positionOfSkuPath + 1];

        $trimmedItemName = trim($itemName);
        $priceWholeNumericOnly = preg_replace('/\D/', '', trim($priceWhole));
        $trimmedPriceFraction = trim($priceFraction);
        $availabilityBoolean = Str::of(rtrim($availability))->length() > 4;

        return new StockData(
            $trimmedItemName,
            $searchUri,
            Store::AmazonCanada,
            $priceObject ?? null,
            $availabilityBoolean,
            $sku,
            $image,
        );
    }
}
