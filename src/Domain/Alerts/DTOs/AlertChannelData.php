<?php

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
