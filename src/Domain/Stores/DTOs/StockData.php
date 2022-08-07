<?php

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
        public readonly bool $available,
        public readonly ?Price $price,
        public readonly ?string $image = null,
        public readonly ?string $sku = null,
    ) {
        if ($image !== null) {
            Assert::isArray(getimagesizefromstring($image));
        }
    }
}
