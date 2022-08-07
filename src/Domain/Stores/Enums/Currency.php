<?php

namespace Domain\Stores\Enums;

use Exception;
use InvalidArgumentException;

enum Currency: string
{
    case USD = 'usd';
    case EUR = 'eur';
    case GBP = 'gbp';
    case CAD = 'cad';

    public function fractionNotation(): string
    {
        return match ($this) {
            self::USD, self::GBP, self::CAD => '.',
            self::EUR => ',',
            default => throw new InvalidArgumentException('Unexpected match value'),
        };
    }
}
