<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Enums;

enum TrackingRequest: string
{
    case Search = 'search';
    case SingleProduct = 'single_product';

    public function friendlyName(): string
    {
        return match ($this) {
            self::Search => 'Search',
            self::SingleProduct => 'Single Product',
        };
    }
}
