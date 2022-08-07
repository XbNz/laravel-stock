<?php

namespace Domain\Stores\Enums;

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use InvalidArgumentException;
use Support\Contracts\StoreContract;

enum Store: string
{
    case AmazonCanada = 'amazon_canada';
    case BestBuyCanada = 'best_buy_canada';

    /**
     * @return class-string<StoreContract>
     */
    public function serviceFqcn(): string
    {
        return match ($this) {
            self::AmazonCanada => AmazonCanadaService::class,
            self::BestBuyCanada => BestBuyCanadaService::class,
            default => throw new InvalidArgumentException('Unknown store'),
        };
    }
}
