<?php

namespace Domain\Stores\Services\AmazonUs;

use Carbon\CarbonInterval;
use Domain\Browser\Browser;
use Domain\Browser\DTOs\BrowserSetupData;
use Domain\Browser\DTOs\TargetData;
use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Domain\Stores\Exceptions\MapperException;
use Domain\Stores\Services\AmazonUs\Mappers\ProductMapper;
use Domain\Stores\Services\AmazonUs\Mappers\SearchMapper;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use Support\Actions\ValidateProxiesAction;
use Support\Contracts\StoreContract;
use Symfony\Component\DomCrawler\Crawler;
use Webmozart\Assert\Assert;

class AmazonUsService implements StoreContract
{
    public function __construct(
        private readonly Browser $client,
        private readonly ProductMapper $productMapper,
        private readonly SearchMapper $searchMapper,
        private readonly ValidateProxiesAction $validateProxies,
    ) {
    }

    /**
     * @param array<UriInterface> $uris
     * @return array<StockData>
     */
    public function product(array $uris): array
    {
        Assert::allContains($uris, 'amazon.com');
        $prefix = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.image_prefix');
        $extension = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.image_format');
        $timeout = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.timeout');
        $useProxy = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.proxy');

        $targets = Collection::make($uris)
            ->map(function (UriInterface $uri) use ($prefix, $extension, $timeout): TargetData {
                $screenshotPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.' . $extension;
                $htmlPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.html';

                return new TargetData(
                    $screenshotPath,
                    $htmlPath,
                    $uri,
                    CarbonInterval::seconds($timeout),
                );
            })
            ->toArray();

        $arguments = [
            '--headless',
            '--window-size=1920,1080',
            '--disable-extensions',
            '--incognito',
        ];

        if ($useProxy) {
            $proxies = ($this->validateProxies)();
            $arguments[] = "--proxy-server={$proxies->random()}";
        }

        $browser = $this->client
            ->setup(new BrowserSetupData($arguments, false))
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
        Assert::allContains($uris, 'amazon.com');
        $prefix = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.image_prefix');
        $extension = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.image_format');
        $timeout = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.timeout');
        $useProxy = Config::get('store.Domain\Stores\Services\AmazonUs\AmazonUsService.proxy');

        $targets = Collection::make($uris)
            ->map(function (UriInterface $uri) use ($prefix, $extension, $timeout): TargetData {
                $screenshotPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.' . $extension;
                $htmlPath = storage_path('app/tmp') . '/' . $prefix . Str::random(10) . '.html';

                return new TargetData(
                    $screenshotPath,
                    $htmlPath,
                    $uri,
                    CarbonInterval::seconds($timeout),
                );
            })
            ->toArray();

        $arguments = [
            '--headless',
            '--window-size=1920,1080',
            '--disable-extensions',
            '--incognito',
        ];

        if ($useProxy) {
            $proxies = ($this->validateProxies)();
            $arguments[] = "--proxy-server={$proxies->random()}";
        }

        $browser = $this->client
            ->setup(new BrowserSetupData($arguments, true))
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
        return $store === Store::AmazonUs;
    }
}
