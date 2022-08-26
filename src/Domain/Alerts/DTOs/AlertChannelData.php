<?php

declare(strict_types=1);

namespace Domain\Alerts\DTOs;

use Domain\Alerts\Enums\AlertChannel;

class AlertChannelData
{
    public function __construct(
        public readonly AlertChannel $type,
        public readonly string $value
    ) {
    }
}
