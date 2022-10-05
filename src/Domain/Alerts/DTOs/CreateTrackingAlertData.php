<?php

declare(strict_types=1);

namespace Domain\Alerts\DTOs;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class CreateTrackingAlertData
{
    public function __construct(
        public readonly UuidInterface $alertChannelUuid,
        public readonly int $percentageTrigger,
        public readonly bool $availabilityTrigger,
    ) {
        Assert::lessThanEq($percentageTrigger, 100);
        Assert::greaterThanEq($percentageTrigger, 1);
    }
}
