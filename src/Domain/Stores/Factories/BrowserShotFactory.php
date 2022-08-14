<?php

declare(strict_types=1);

namespace Domain\Stores\Factories;

use Domain\Stores\Exceptions\StoreNotFoundException;
use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Arr;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;
use Support\Contracts\StoreContract;
use Webmozart\Assert\Assert;

class BrowserShotFactory
{
    public function __construct(
        private readonly Browsershot $client,
        private readonly Config $config,
    ) {
    }

    /**
     * @param class-string<StoreContract> $storeServiceClass
     */
    public function for(string $storeServiceClass): Browsershot
    {
        return match ($storeServiceClass) {
            AmazonCanadaService::class => $this->amazonCanada(),
            BestBuyCanadaService::class => $this->bestBuyCanada(),
            NeweggCanadaService::class => $this->neweggCanada(),
            default => throw new StoreNotFoundException("{$storeServiceClass} is not a valid store service FQCN")
        };
    }

    private function amazonCanada(): Browsershot
    {
        $quality = $this->getConfigOptionForService(AmazonCanadaService::class, 'image_quality');
        Assert::integerish($quality);
        $format = $this->getConfigOptionForService(AmazonCanadaService::class, 'image_format');
        Assert::string($format);
        $width = $this->getConfigOptionForService(AmazonCanadaService::class, 'screenshot_width');
        Assert::integerish($width);
        $height = $this->getConfigOptionForService(AmazonCanadaService::class, 'screenshot_height');
        Assert::integerish($height);
        $userAgent = $this->getConfigOptionForService(AmazonCanadaService::class, 'user_agent');
        Assert::string($userAgent);
        $shouldUseProxies = $this->getConfigOptionForService(AmazonCanadaService::class, 'proxy');
        Assert::boolean($shouldUseProxies);
        $timeout = $this->getConfigOptionForService(AmazonCanadaService::class, 'timeout');
        Assert::integerish($timeout);

        $manipulations = new Manipulations();
        $manipulations->quality((int) $quality)->format($format);

        $client = $this->client
            ->timeout((int) $timeout)
            ->windowSize((int) $width, (int) $height)
            ->userAgent($userAgent)
            ->disableJavascript()
            ->waitUntilNetworkIdle()
            ->mergeManipulations($manipulations);

        if ($shouldUseProxies) {
            $client->setProxyServer($this->getRandomProxy());
        }

        return $client;
    }

    private function bestBuyCanada(): Browsershot
    {
        $quality = $this->getConfigOptionForService(BestBuyCanadaService::class, 'image_quality');
        Assert::integerish($quality);
        $format = $this->getConfigOptionForService(BestBuyCanadaService::class, 'image_format');
        Assert::string($format);
        $width = $this->getConfigOptionForService(BestBuyCanadaService::class, 'screenshot_width');
        Assert::integerish($width);
        $height = $this->getConfigOptionForService(BestBuyCanadaService::class, 'screenshot_height');
        Assert::integerish($height);
        $userAgent = $this->getConfigOptionForService(BestBuyCanadaService::class, 'user_agent');
        Assert::string($userAgent);
        $shouldUseProxies = $this->getConfigOptionForService(BestBuyCanadaService::class, 'proxy');
        Assert::boolean($shouldUseProxies);
        $timeout = $this->getConfigOptionForService(BestBuyCanadaService::class, 'timeout');
        Assert::integerish($timeout);

        $manipulations = new Manipulations();
        $manipulations->quality((int) $quality)->format($format);

        $client = $this->client
            ->timeout((int) $timeout)
            ->windowSize((int) $width, (int) $height)
            ->userAgent($userAgent)
            ->waitUntilNetworkIdle()
            ->mergeManipulations($manipulations);

        if ($shouldUseProxies) {
            $client->setProxyServer($this->getRandomProxy());
        }

        return $client;
    }

    private function neweggCanada(): Browsershot
    {
        $quality = $this->getConfigOptionForService(NeweggCanadaService::class, 'image_quality');
        Assert::integerish($quality);
        $format = $this->getConfigOptionForService(NeweggCanadaService::class, 'image_format');
        Assert::string($format);
        $width = $this->getConfigOptionForService(NeweggCanadaService::class, 'screenshot_width');
        Assert::integerish($width);
        $height = $this->getConfigOptionForService(NeweggCanadaService::class, 'screenshot_height');
        Assert::integerish($height);
        $userAgent = $this->getConfigOptionForService(NeweggCanadaService::class, 'user_agent');
        Assert::string($userAgent);
        $shouldUseProxies = $this->getConfigOptionForService(NeweggCanadaService::class, 'proxy');
        Assert::boolean($shouldUseProxies);
        $timeout = $this->getConfigOptionForService(NeweggCanadaService::class, 'timeout');
        Assert::integerish($timeout);

        $manipulations = new Manipulations();
        $manipulations->quality((int) $quality)->format($format);

        $client = $this->client
            ->timeout((int) $timeout)
            ->windowSize((int) $width, (int) $height)
            ->userAgent($userAgent)
            ->disableJavascript()
            ->waitUntilNetworkIdle()
            ->mergeManipulations($manipulations);

        if ($shouldUseProxies) {
            $client->setProxyServer($this->getRandomProxy());
        }

        return $client;
    }

    private function getRandomProxy(): string
    {
        $proxies = $this->config->get('proxy.proxies');
        Assert::isArray($proxies);
        Assert::minCount($proxies, 1);
        $random = Arr::random($proxies);
        Assert::string($random);
        return $random;
    }

    /**
     * @param class-string<StoreContract> $service
     */
    private function getConfigOptionForService(string $service, string $configOption): mixed
    {
        return $this->config->get('store.' . $service . ".{$configOption}");
    }
}
