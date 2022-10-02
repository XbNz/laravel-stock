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

    public static function fromDifference(float $valueA, float $valueB): self
    {
        $difference = $valueA - $valueB;
        $percentage = ($difference / $valueA) * 100;

        return new self($percentage);
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
