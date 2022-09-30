<?php

namespace Support\ValueObjects;

use Stringable;
use Webmozart\Assert\Assert;

class Percentage
{
    public function __construct(public readonly float $value)
    {
        Assert::lessThanEq($value, 100);
        Assert::greaterThanEq($value, 0);
    }

    public static function from(float $value): self
    {
        return new self($value);
    }

    public function greaterThan(float $value): bool
    {
        return $this->value > $value;
    }

    public function lessThan(float $value): bool
    {
        return $this->value < $value;
    }

}
