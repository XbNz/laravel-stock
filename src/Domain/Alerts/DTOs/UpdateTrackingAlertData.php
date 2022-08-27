<?php

namespace Domain\Alerts\DTOs;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class UpdateTrackingAlertData
{
    public function __construct(
        public readonly ?UuidInterface $alertChannelUuid,
        public readonly ?int $percentageTrigger,
        public readonly ?bool $availabilityTrigger,
    ) {
        Assert::nullOrLessThanEq($percentageTrigger, 100);
        Assert::nullOrgreaterThanEq($percentageTrigger, 1);
    }
}
