<?php

use Domain\Alerts\Enums\AlertChannel;

return [
    'mappings' => [
        AlertChannel::Discord->value => 'discord',
        AlertChannel::Email->value => 'mail',
        AlertChannel::SMS->value => 'vonage',
    ]
];
