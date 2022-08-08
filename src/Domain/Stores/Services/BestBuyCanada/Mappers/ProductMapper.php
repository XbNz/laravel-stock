<?php

declare(strict_types=1);

namespace Domain\Stores\Services\BestBuyCanada\Mappers;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MapperContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class ProductMapper implements MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): StockData
    {
        $price = $html->filterXPath('//span[contains(@data-automation, "product-price")]')
            ->filterXPath('//span[contains(@class, "screenReaderOnly")]')
            ->text();

        [$basePrice, $fractionalPrice] = explode('.', Str::of($price)->replaceMatches('/[^0-9.]/', '')->value());
        $title = trim($html->filterXPath('//h1[contains(@class, "productName")]')->text());

        $modelInfo = $html->filterXPath('//div[contains(@class, "modelInformation")]')->text();
        $sku = Str::of($modelInfo)->lower()->after('web code:')->trim()->value();
        Assert::length($sku, 8);

        $onlineAvailability = $html->filterXPath('//div[contains(@class, "onlineAvailabilityContainer")]')
            ->filterXPath('//span[contains(@class, "availabilityMessage")]')
            ->text();

        $onlineAvailabilityBoolean = Str::of($onlineAvailability)->lower()->contains('available');

        return new StockData(
            $title,
            $searchUri,
            Store::BestBuyCanada,
            new Price(
                (int) $basePrice,
                Currency::CAD,
                (int) $fractionalPrice,
            ),
            $onlineAvailabilityBoolean,
            $sku,
            $image,
        );
    }
}
