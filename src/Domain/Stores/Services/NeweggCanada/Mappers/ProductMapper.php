<?php

declare(strict_types=1);

namespace Domain\Stores\Services\NeweggCanada\Mappers;

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
        $price = $this->price($html);
        $title = $this->itemName($html);
        $availability = $this->availability($html);
        $sku = $this->sku($searchUri);

        return new StockData(
            $title,
            $searchUri,
            Store::NeweggCanada,
            $price,
            $availability,
            $sku,
            $image,
        );
    }

    private function price(Crawler $rootHtml): Price
    {
        $price = $rootHtml->filterXPath('//ul[contains(@class, "price")]')
            ->filterXPath('//li[contains(@class, "price-current")]')
            ->text();

        [$priceBase, $priceFractional] = explode('.', Str::of($price)->replaceMatches('/[^0-9.]/', '')->value());

        Assert::integerish($priceBase);
        Assert::integerish($priceFractional);

        return new Price(
            (int) $priceBase,
            Currency::CAD,
            (int) $priceFractional,
        );
    }

    private function itemName(Crawler $rootHtml): string
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "row-body")]');

        $title = $productFrame->filterXPath('//h1[contains(@class, "product-title")]')->text();

        Assert::minLength(trim($title), 2);

        return trim($title);
    }

    private function availability(Crawler $rootHtml): bool
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "row-body")]');

        $availability = $productFrame->filterXPath('//div[contains(@class, "product-inventory")]')
            ->filterXPath('//strong')
            ->text();

        return Str::of($availability)->lower()->contains('in stock');
    }

    private function sku(UriInterface $searchUri): string
    {
        $path = explode('/', $searchUri->getPath());
        $positionOfSkuPath = Collection::make($path)->search('p', true);
        Assert::integer($positionOfSkuPath);
        $sku = $path[$positionOfSkuPath + 1];

        Assert::minLength($sku, 2);

        return $sku;
    }
}
