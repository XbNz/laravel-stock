<?php

namespace Domain\Stores\DTOs;

use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Support\Contracts\MappableContract;
use Webmozart\Assert\Assert;

class StockSearchData implements MappableContract
{
    /**
     * @param Collection<StockData> $stocks
     */
    public function __construct(
        public readonly Uri $uri,
        public readonly string $term,
        public readonly Collection $stocks,
        public readonly ?string $image = null,
    ) {
        if ($image !== null) {
            Assert::isArray(getimagesizefromstring($image));
        }
    }
}
