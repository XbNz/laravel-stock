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
            default => new StoreNotFoundException("{$storeServiceClass} is not a valid store service FQCN")
        };
    }

    private function amazonCanada(): Browsershot
    {
        $manipulations = new Manipulations();
        $manipulations->quality(
            $this->config->get('store.' . AmazonCanadaService::class . '.image_quality', 20)
        )->format(
            $this->config->get('store.' . AmazonCanadaService::class . '.image_format', Manipulations::FORMAT_JPG)
        );

        $client = $this->client
            ->windowSize(
                $this->config->get('store.' . AmazonCanadaService::class . '.screenshot_width', 1920),
                $this->config->get('store.' . AmazonCanadaService::class . '.screenshot_height', 1080)
            )
            ->userAgent(
                $this->config->get('store.' . AmazonCanadaService::class . '.user_agent')
            )
            ->waitUntilNetworkIdle()
            ->mergeManipulations($manipulations);

        if ($this->config->get('store.' . AmazonCanadaService::class . '.proxy')) {
            $client->setProxyServer(Arr::random($this->config->get('proxy.proxies')));
        }

        return $client;
    }

    private function bestBuyCanada(): Browsershot
    {
        $manipulations = new Manipulations();
        $manipulations->quality(
            $this->config->get('store.' . BestBuyCanadaService::class . '.image_quality', 20)
        )->format(
            $this->config->get('store.' . BestBuyCanadaService::class . '.image_format', Manipulations::FORMAT_JPG)
        );

        $client = $this->client
            ->windowSize(
                $this->config->get('store.' . BestBuyCanadaService::class . '.screenshot_width', 1920),
                $this->config->get('store.' . BestBuyCanadaService::class . '.screenshot_height', 1080)
            )
            ->userAgent(
                $this->config->get('store.' . BestBuyCanadaService::class . '.user_agent')
            )
            ->waitUntilNetworkIdle()
            ->mergeManipulations($manipulations);

        if ($this->config->get('store.' . BestBuyCanadaService::class . '.proxy')) {
            $client->setProxyServer(Arr::random($this->config->get('proxy.proxies')));
        }

        return $client;
    }

    private function neweggCanada(): Browsershot
    {
        $manipulations = new Manipulations();
        $manipulations->quality(
            $this->config->get('store.' . NeweggCanadaService::class . '.image_quality', 20)
        )->format(
            $this->config->get('store.' . NeweggCanadaService::class . '.image_format', Manipulations::FORMAT_JPG)
        );

        $client = $this->client
            ->windowSize(
                $this->config->get('store.' . NeweggCanadaService::class . '.screenshot_width', 1920),
                $this->config->get('store.' . NeweggCanadaService::class . '.screenshot_height', 1080)
            )
            ->userAgent(
                $this->config->get('store.' . NeweggCanadaService::class . '.user_agent')
            )
            ->waitUntilNetworkIdle()
            ->mergeManipulations($manipulations);

        if ($this->config->get('store.' . NeweggCanadaService::class . '.proxy')) {
            $client->setProxyServer(Arr::random($this->config->get('proxy.proxies')));
        }

        return $client;
    }
}
