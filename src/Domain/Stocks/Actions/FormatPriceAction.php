<?php

declare(strict_types=1);

namespace Domain\Stocks\Actions;

use Domain\Stores\Enums\Currency;
use InvalidArgumentException;

class FormatPriceAction
{
    public function __invoke(int $priceInLowestCurrencyUnit, Currency $currency)
    {
        $numberFormat = match ($currency) {
            Currency::USD, Currency::GBP, Currency::CAD, Currency::EUR => number_format(
                $priceInLowestCurrencyUnit / 100,
                2,
                $currency->decimalSeparator(),
                $currency->thousandSeparator()
            ),
            default => throw new InvalidArgumentException("Unexpected currency {$currency->value}"),
        };

        return $currency->symbol() . $numberFormat;
    }
}
