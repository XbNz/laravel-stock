<?php

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\AmazonUs\AmazonUsService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use Spatie\Image\Manipulations;

return [
    AmazonCanadaService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'amazon_',
        'timeout' => 50,
        'proxy' => true,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
    ],
    AmazonUsService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'amazon_',
        'timeout' => 50,
        'proxy' => true,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
    ],
    BestBuyCanadaService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'bestbuy_',
        'timeout' => 50,
        'proxy' => true,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
    ],
    NeweggCanadaService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'newegg_',
        'timeout' => 50,
        'proxy' => true,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
    ],
];
