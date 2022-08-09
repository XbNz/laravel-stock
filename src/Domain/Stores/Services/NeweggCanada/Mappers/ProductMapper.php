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
        $price = $html->filterXPath('//ul[contains(@class, "price")]')
            ->filterXPath('//li[contains(@class, "price-current")]')
            ->text();

        $title = $html->filterXPath('//h1[contains(@class, "product-title")]')->text();

        $availability = $html->filterXPath('//div[contains(@class, "product-inventory")]')
            ->filterXPath('//strong')
            ->text();

        $availabilityBoolean = Str::of($availability)->lower()->contains('in stock');

        [$priceBase, $priceFractional] = explode('.', Str::of($price)->replaceMatches('/[^0-9.]/', '')->value());

        $path = explode('/', $searchUri->getPath());
        $positionOfSkuPath = Collection::make($path)->search('p', true);
        Assert::integer($positionOfSkuPath);
        $sku = $path[$positionOfSkuPath + 1];

        return new StockData(
            trim($title),
            $searchUri,
            Store::NeweggCanada,
            new Price(
                (int) $priceBase,
                Currency::CAD,
                (int) $priceFractional,
            ),
            $availabilityBoolean,
            $sku,
            $image,
        );
    }
}
