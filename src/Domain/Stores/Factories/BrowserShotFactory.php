<?php

declare(strict_types=1);

namespace Domain\Stores\Factories;

use Domain\Stores\Exceptions\StoreNotFoundException;
use Domain\Stores\Services\Amazon\AmazonService;
use Illuminate\Support\Arr;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Manipulations;
use Support\Contracts\StoreContract;
use Illuminate\Config\Repository as Config;

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
            AmazonService::class => $this->amazon(),
            default => new StoreNotFoundException("{$storeServiceClass} is not a valid store service FQCN")
        };
    }

    private function amazon(): Browsershot
    {
        $manipulations = new Manipulations();
        $manipulations->quality(
            $this->config->get('store.' . AmazonService::class . '.image_quality', 20)
        )->format(
            $this->config->get('store.' . AmazonService::class . '.image_format', Manipulations::FORMAT_JPG)
        );

        $client = $this->client
            ->windowSize(
                $this->config->get('store.' . AmazonService::class . '.screenshot_width', 1920),
                $this->config->get('store.' . AmazonService::class . '.screenshot_height', 1080)
            )
            ->userAgent(
                $this->config->get('store.' . AmazonService::class . '.user_agent')
            )
            ->mergeManipulations($manipulations);

        if ($this->config->get('store.' . AmazonService::class . '.proxy')) {
            $client->setProxyServer(Arr::random($this->config->get('proxy.proxies')));
        }

        return $client;
    }
}
