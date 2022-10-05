<?php

declare(strict_types=1);

namespace Domain\Stores\ValueObjects;

use Domain\Stores\Enums\Currency;
use Webmozart\Assert\Assert;

class Price
{
    public function __construct(
        public readonly int $amount,
        public readonly Currency $currency,
    ) {
        Assert::greaterThanEq($amount, 0);
    }
}
