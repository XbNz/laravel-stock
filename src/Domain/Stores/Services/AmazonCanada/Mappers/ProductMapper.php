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
        $itemName = $html->filterXPath('//span[contains(@id, "productTitle")]')->text();
        $priceWhole = $html->filterXPath('//span[contains(@class, "price-whole")]')->text();
        $priceFraction = $html->filterXPath('//span[contains(@class, "price-fraction")]')->text();
        $availability = $html->filterXPath(
            '//div[contains(@id, "availability")]/span[contains(@class, "medium")]'
        )->text();

        $path = explode('/', $searchUri->getPath());
        $positionOfSkuPath = Collection::make($path)->search('dp', true);
        $sku = $path[$positionOfSkuPath + 1];

        $trimmedItemName = trim($itemName);
        $priceWholeNumericOnly = preg_replace('/\D/', '', trim($priceWhole));
        $trimmedPriceFraction = trim($priceFraction);
        $availabilityBoolean = Str::of(rtrim($availability))->lower()->value() === 'in stock.';

        return new StockData(
            $trimmedItemName,
            $searchUri,
            Store::AmazonCanada,
            $image,
            new Price(
                (int) $priceWholeNumericOnly,
                Currency::CAD,
                (int) $trimmedPriceFraction,
            ),
            $availabilityBoolean,
            $sku,
        );
    }
}
