<?php

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use Spatie\Image\Manipulations;

return [
    AmazonCanadaService::class => [
        'screenshot_width' => 1920,
        'screenshot_height' => 1080,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36',
        'image_quality' => 20,
        'image_format' => Manipulations::FORMAT_JPG,
        'image_prefix' => 'amazon_',
        'timeout' => 30,
        'proxy' => false,
    ],
    BestBuyCanadaService::class => [
        'screenshot_width' => 1920,
        'screenshot_height' => 1080,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36',
        'image_quality' => 20,
        'image_format' => Manipulations::FORMAT_JPG,
        'image_prefix' => 'bestbuy_',
        'timeout' => 100,
        'proxy' => false,
    ],
    NeweggCanadaService::class => [
        'screenshot_width' => 1920,
        'screenshot_height' => 1080,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36',
        'image_quality' => 20,
        'image_format' => Manipulations::FORMAT_JPG,
        'image_prefix' => 'newegg_',
        'timeout' => 30,
        'proxy' => false,
    ],
];
