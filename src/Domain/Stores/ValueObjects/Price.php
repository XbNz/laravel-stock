<?php

namespace Domain\Stores\ValueObjects;

use Domain\Stores\Enums\Currency;
use Webmozart\Assert\Assert;

class Price
{
    public function __construct(
        public readonly int $baseAmount,
        public readonly Currency $currency,
        public readonly ?int $fractionalAmount = null,
    ) {
        Assert::greaterThanEq($baseAmount, 0);
        Assert::greaterThanEq($fractionalAmount, 0);
    }

    public function __toString(): string
    {
        return $this->currency->name . ' ' . $this->baseAmount . $this->currency->fractionNotation() . $this->fractionalAmount;
    }
}
