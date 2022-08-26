<?php

declare(strict_types=1);

namespace Domain\Alerts\Enums;

enum AlertChannel: string
{
    case Email = 'email';
    case SMS = 'sms';
    case Discord = 'discord';

    public function requiresVerification(): bool
    {
        return match ($this) {
            self::SMS, self::Email => true,
            default => false,
        };
    }
}
