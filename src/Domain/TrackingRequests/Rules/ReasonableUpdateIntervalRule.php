<?php

namespace Domain\TrackingRequests\Rules;

use Carbon\CarbonInterval;
use Carbon\Exceptions\InvalidIntervalException;
use Illuminate\Contracts\Validation\Rule;
use Webmozart\Assert\Assert;

class ReasonableUpdateIntervalRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        try {
            $interval = CarbonInterval::fromString($value);
        } catch (InvalidIntervalException) {
            return false;
        }

        Assert::true(isset($interval));

        if ($interval->lessThan(CarbonInterval::seconds(30))) {
            return false;
        }

        if ($interval->greaterThan(CarbonInterval::months(1))) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'The update interval is not a reasonable update interval.';
    }
}
