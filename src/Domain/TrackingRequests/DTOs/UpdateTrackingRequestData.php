<?php

namespace Domain\TrackingRequests\DTOs;

use ECSPrefix202209\Webmozart\Assert\Assert;

class UpdateTrackingRequestData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?int $updateInterval,
    ) {
        Assert::nullOrGreaterThanEq($updateInterval, 30);
    }
}
