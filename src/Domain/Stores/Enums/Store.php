<?php

declare(strict_types=1);

namespace Domain\Stores\Enums;

use Domain\Stores\Services\AmazonCanada\AmazonCanadaService;
use Domain\Stores\Services\AmazonUs\AmazonUsService;
use Domain\Stores\Services\BestBuyCanada\BestBuyCanadaService;
use Domain\Stores\Services\NeweggCanada\NeweggCanadaService;
use InvalidArgumentException;
use Support\Contracts\StoreContract;

enum Store: string
{
    case AmazonCanada = 'amazon_canada';
    case AmazonUs = 'amazon_us';
    case BestBuyCanada = 'best_buy_canada';
    case NeweggCanada = 'newegg_canada';

    /**
     * @return class-string<StoreContract>
     */
    public function serviceFqcn(): string
    {
        return match ($this) {
            self::AmazonCanada => AmazonCanadaService::class,
            self::AmazonUs => AmazonUsService::class,
            self::BestBuyCanada => BestBuyCanadaService::class,
            self::NeweggCanada => NeweggCanadaService::class,
            default => throw new InvalidArgumentException('Unknown store'),
        };
    }

    public function storeBaseUri(): string
    {
        return match ($this) {
            self::AmazonCanada => 'amazon.ca',
            self::AmazonUs => 'amazon.com',
            self::BestBuyCanada => 'bestbuy.ca',
            self::NeweggCanada => 'newegg.ca',
            default => throw new InvalidArgumentException('Unknown store'),
        };
    }

    public function currency(): Currency
    {
        return match ($this) {
            self::AmazonCanada, self::BestBuyCanada, self::NeweggCanada => Currency::CAD,
            self::AmazonUs => Currency::USD,
            default => throw new InvalidArgumentException('Unknown store'),
        };
    }
}
