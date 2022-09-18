<?php

declare(strict_types=1);

namespace Domain\Stores\Services\BestBuyCanada\Mappers;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\Enums\Currency;
use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
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
        $title = $this->itemName($html);
        $sku = $this->sku($html);
        $availability = $this->availability($html);

        return new StockData(
            $title,
            $searchUri,
            Store::BestBuyCanada,
            $price,
            $availability,
            $sku,
            $image,
        );
    }

    private function price(Crawler $rootHtml): Price
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "x-page-content")]');

        $price = $productFrame->filterXPath('//span[contains(@data-automation, "product-price")]')
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
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "x-page-content")]');

        $title = $productFrame->filterXPath('//h1[contains(@class, "productName")]')->text();
        Assert::minLength(trim($title), 2);

        return trim($title);
    }

    private function availability(Crawler $rootHtml): bool
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "x-product-detail-page")]');

        try {
            $onlineAvailability = $productFrame->filterXPath('//div[contains(@class, "onlineAvailabilityContainer")]')
                ->filterXPath('//span[contains(@class, "availabilityMessage")]')
                ->text();

            Assert::minLength($onlineAvailability, 2);

            return Str::of($onlineAvailability)->lower()->contains('available');
        } catch (InvalidArgumentException $e) {
            return $this->fallBackToSecondaryAvailabilityDetectionMethod($rootHtml);
        }
    }

    private function sku(Crawler $rootHtml): string
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "x-page-content")]');
        $modelInfo = $productFrame->filterXPath('//div[contains(@class, "modelInformation")]')->text();
        $sku = Str::of($modelInfo)->lower()->after('web code:')->trim()->value();
        Assert::length($sku, 8);

        return $sku;
    }

    private function fallBackToSecondaryAvailabilityDetectionMethod(Crawler $rootHtml): bool
    {
        $productFrame = $rootHtml->filterXPath('//div[contains(@class, "x-product-detail-page")]');

        $addToCartContainer = $productFrame->filterXPath('//div[contains(@class, "addToCartContainer")]');
        $disabledButton = $addToCartContainer->filterXPath('//button[@disabled]');

        return $disabledButton->count() === 0;
    }
}
