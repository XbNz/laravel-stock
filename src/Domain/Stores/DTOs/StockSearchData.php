<?php

namespace Domain\Stores\DTOs;

use Domain\Stores\Collections\StockDataCollection;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Support\Contracts\MappableContract;
use Webmozart\Assert\Assert;

class StockSearchData implements MappableContract
{
    /**
     * @param StockDataCollection<StockData> $stocks
     */
    public function __construct(
        public readonly Uri $uri,
        public readonly StockDataCollection $stocks,
        public readonly ?string $image = null,
    ) {
        Assert::minCount($stocks, 1);
        if ($image !== null) {
            Assert::isArray(getimagesizefromstring($image));
        }
    }
}
