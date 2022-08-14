<?php

namespace Domain\TrackingRequests\Enums;

enum TrackingRequest: string
{
    case Search = 'search';
    case SingleProduct = 'single_product';
}
