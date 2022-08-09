<?php

declare(strict_types=1);

namespace Domain\Stores\DTOs;

use Domain\Stores\Enums\Store;
use Domain\Stores\ValueObjects\Price;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MappableContract;
use Webmozart\Assert\Assert;

class StockData implements MappableContract
{
    public function __construct(
        public readonly string $title,
        public readonly UriInterface $link,
        public readonly Store $store,
        public readonly ?Price $price,
        public readonly bool $available,
        public readonly string $sku,
        public readonly ?string $image = null,
    ) {
        Assert::minLength($title, 2);
        Assert::minLength($sku, 2);
        if ($image !== null) {
            Assert::isArray(getimagesizefromstring($image));
        }
    }
}
