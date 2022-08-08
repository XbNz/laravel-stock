<?php

declare(strict_types=1);

namespace Domain\Stores\Enums;

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use InvalidArgumentException;
use Support\Contracts\StoreContract;

enum Store: string
{
    case AmazonCanada = 'amazon_canada';
    case BestBuyCanada = 'best_buy_canada';
    case NeweggCanada = 'newegg_canada';

    /**
     * @return class-string<StoreContract>
     */
    public function serviceFqcn(): string
    {
        return match ($this) {
            self::AmazonCanada => AmazonCanadaService::class,
            self::BestBuyCanada => BestBuyCanadaService::class,
            self::NeweggCanada => NeweggCanadaService::class,
            default => throw new InvalidArgumentException('Unknown store'),
        };
    }
}
