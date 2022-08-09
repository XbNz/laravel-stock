<?php

declare(strict_types=1);

namespace Domain\Stores\Services\AmazonCanada\Mappers;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class ProductMapper implements MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockData
    {
        $html = $html->filterXPath('//div[contains(@id, "ppd")]');
        $itemName = $html->filterXPath('//span[contains(@id, "productTitle")]')->text();

        $priceWhole = rescue(
            fn () => $html->filterXPath('//span[contains(@class, "price-whole")]')->text(),
            fn () => null,
        );

        $priceFraction = rescue(
            fn () => $html->filterXPath('//span[contains(@class, "price-fraction")]')->text(),
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
            $priceObject = new Price(
                (int) $priceWholeNumericOnly,
                Currency::CAD,
                isset($priceFractionNumericOnly) ? (int) $priceFractionNumericOnly : null,
            );
        }

        $availability = $html->filterXPath(
            '//div[contains(@id, "availability")]/span[contains(@class, "medium")]'
        )->text();

        $path = explode('/', $searchUri->getPath());
        $positionOfSkuPath = Collection::make($path)->search('dp', true);
        Assert::integer($positionOfSkuPath);
        $sku = $path[$positionOfSkuPath + 1];

        $trimmedItemName = trim($itemName);
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
