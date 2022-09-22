<?php

declare(strict_types=1);

namespace Domain\Stores\Enums;

use InvalidArgumentException;

enum Currency: string
{
    case USD = 'usd';
    case EUR = 'eur';
    case GBP = 'gbp';
    case CAD = 'cad';

    public function decimalSeparator(): string
    {
        return match ($this) {
            self::USD, self::GBP, self::CAD => '.',
            self::EUR => ',',
            default => throw new InvalidArgumentException('Unexpected match value'),
        };
    }

    public function thousandSeparator(): string
    {
        return match ($this) {
            self::USD, self::GBP, self::CAD => ',',
            self::EUR => '.',
            default => throw new InvalidArgumentException('Unexpected match value'),
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::USD, self::CAD => '$',
            self::EUR => '€',
            self::GBP => '£',
            default => throw new InvalidArgumentException('Unexpected match value'),
        };
    }
}
