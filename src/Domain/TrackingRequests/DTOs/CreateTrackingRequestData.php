<?php

namespace Domain\TrackingRequests\DTOs;

use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

class CreateTrackingRequestData
{
    public function __construct(
        public readonly string $name,
        public readonly UriInterface $url,
        public readonly int $updateInterval,
    ) {
        Assert::greaterThanEq($updateInterval, 30);
    }
}
