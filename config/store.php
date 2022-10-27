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
    ],
    AmazonUsService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'amazon_',
        'timeout' => 50,
        'proxy' => true,
    ],
    BestBuyCanadaService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'bestbuy_',
        'timeout' => 50,
        'proxy' => true,
    ],
    NeweggCanadaService::class => [
        'image_format' => Manipulations::FORMAT_PNG,
        'image_prefix' => 'newegg_',
        'timeout' => 50,
        'proxy' => true,
    ],
];
