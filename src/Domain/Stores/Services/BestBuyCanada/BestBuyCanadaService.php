<?php

declare(strict_types=1);

namespace Domain\Stores\Services\BestBuyCanada;

use Carbon\CarbonInterval;
use Domain\Browser\Browser;
use Domain\Browser\DTOs\BrowserSetupData;
use Domain\Browser\DTOs\TargetData;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Domain\Stores\Exceptions\MapperException;
use Domain\Stores\Services\BestBuyCanada\Mappers\ProductMapper;
use Domain\Stores\Services\BestBuyCanada\Mappers\SearchMapper;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Support\Contracts\StoreContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class BestBuyCanadaService implements StoreContract
{
    public function __construct(
        private readonly Browser $client,
        private readonly ProductMapper $productMapper,
        private readonly SearchMapper $searchMapper,
    ) {
    }

    /**
     * @param array<UriInterface> $uris
     * @return array<StockData>
     */
    public function product(array $uris): array
    {
        Assert::allContains($uris, 'bestbuy.ca');
        $prefix = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_prefix');
        $extension = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_format');
        $timeout = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.timeout');

        $targets = Collection::make($uris)
            ->map(function (UriInterface $uri) use ($prefix, $extension, $timeout): TargetData {
                $screenshotPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.' . $extension;
                $htmlPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.html';

                return new TargetData(
                    $screenshotPath,
                    $htmlPath,
                    $uri,
                    CarbonInterval::seconds($timeout),
                    '//*[contains(@class, "productPricingContainer")]',
                );
            })
            ->toArray();

        $browser = $this->client
            ->setup(new BrowserSetupData([
                '--headless',
                '--window-size=1920,1080',
            ], false))
            ->addTargets($targets);

        $browser->execute();

        return Collection::make($targets)
            ->map(function (TargetData $targetData) {
                $crawler = new Crawler(file_get_contents($targetData->htmlFileName));

                try {
                    $product = $this->productMapper->map($crawler, $targetData->url, $targetData->screenShotFileName);
                } catch (Exception $e) {
                    throw new MapperException(
                        "Failed to map product info for {$targetData->url}",
                        previous: $e,
                    );
                }

                return $product;
            })->toArray();
    }

    /**
     * @param array<UriInterface> $uris
     * @return array<StockSearchData>
     */
    public function search(array $uris): array
    {
        Assert::allContains($uris, 'bestbuy.ca');
        $prefix = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_prefix');
        $extension = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.image_format');
        $timeout = Config::get('store.Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService.timeout');

        $targets = Collection::make($uris)
            ->map(function (UriInterface $uri) use ($prefix, $extension, $timeout): TargetData {
                $screenshotPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.' . $extension;
                $htmlPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.html';

                return new TargetData(
                    $screenshotPath,
                    $htmlPath,
                    $uri,
                    CarbonInterval::seconds($timeout),
                    '//*[contains(text(), "Available to ship") or contains(text(), "Available online only")]',
                );
            })
            ->toArray();

        $browser = $this->client
            ->setup(new BrowserSetupData([
                '--headless',
                '--window-size=1920,1080',
            ], true))
            ->addTargets($targets);

        $browser->execute();

        return Collection::make($targets)
            ->map(function (TargetData $targetData) {
                $crawler = new Crawler(file_get_contents($targetData->htmlFileName));

                try {
                    $stockDataCollection = $this->searchMapper->map($crawler, $targetData->url, $targetData->screenShotFileName);
                } catch (Exception $e) {
                    throw new MapperException(
                        "Failed to map search results for {$targetData->url}",
                        previous: $e,
                    );
                }

                return new StockSearchData(
                    $targetData->url,
                    $stockDataCollection,
                    $targetData->screenShotFileName,
                );
            })->toArray();
    }

    public function supports(Store $store): bool
    {
        return $store === Store::BestBuyCanada;
    }
}
