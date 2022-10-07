<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\DTOs;

use ECSPrefix202209\Webmozart\Assert\Assert;
use InvalidArgumentException;

class UpdateTrackingRequestData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?int $updateInterval,
    ) {
        Assert::nullOrGreaterThanEq($updateInterval, 30);

        if ($name === null && $updateInterval === null) {
            throw new InvalidArgumentException('At least one of the properties must be set');
        }
    }
}
