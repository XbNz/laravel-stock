<?php

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Spatie\Image\Manipulations;

return [
    AmazonCanadaService::class => [
        'screenshot_width' => 1920,
        'screenshot_height' => 1080,
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36',
        'image_quality' => 20,
        'image_format' => Manipulations::FORMAT_JPG,
        'image_prefix' => 'amazon_',
        'proxy' => false,
    ]
];
