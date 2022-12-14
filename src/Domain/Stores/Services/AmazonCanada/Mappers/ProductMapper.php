<?php

declare(strict_types=1);

namespace Domain\Stores\Services\AmazonCanada\Mappers;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class ProductMapper implements MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockData
    {
        $price = $this->price($html);
        $sku = $this->sku($searchUri);
        $itemName = $this->itemName($html);
        $availability = $this->availability($html);

        return new StockData(
            $itemName,
            $searchUri,
            Store::AmazonCanada,
            $price ?? new Price(0, Currency::CAD),
            $availability,
            $sku,
            $image,
        );
    }

    private function price(Crawler $rootHtml): ?Price
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@id, "ppd")]');

        try {
            $priceWhole = $productFrame->filterXPath('//span[contains(@class, "price-whole")]')->text();
        } catch (InvalidArgumentException $e) {
            $priceWhole = null;
        }

        try {
            $priceFraction = $productFrame->filterXPath('//span[contains(@class, "price-fraction")]')->text();
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
        $productFrame = $rootHtml->filterXPath('//div[contains(@id, "ppd")]');

        $itemName = $productFrame->filterXPath('//span[contains(@id, "productTitle")]')->text();
        Assert::minLength($itemName, 2);
        return $itemName;
    }

    private function availability(Crawler $rootHtml): bool
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@id, "ppd")]');

        $availability = $productFrame->filterXPath(
            '//div[contains(@id, "availability")]/span[contains(@class, "medium")]'
        );

        if ($availability->count() === 0) {
            return false;
        }

        return Str::of(trim($availability->text()))->length() > 4;
    }

    private function sku(UriInterface $searchUri): string
    {
        $path = explode('/', $searchUri->getPath());
        $positionOfSkuPath = Collection::make($path)->search('dp', true);
        Assert::integer($positionOfSkuPath);
        $sku = $path[$positionOfSkuPath + 1];
        $sku = trim($sku);
        Assert::minLength($sku, 2);
        return $sku;
    }
}
