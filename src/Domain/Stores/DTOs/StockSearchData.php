<?php

declare(strict_types=1);

namespace Domain\Stores\DTOs;

use Domain\Stores\Collections\StockDataCollection;
use Illuminate\Support\Facades\File;
use Psr\Http\Message\UriInterface;
use Support\Contracts\MappableContract;
use Webmozart\Assert\Assert;

class StockSearchData implements MappableContract
{
    /**
     * @param StockDataCollection<int, StockData> $stocks
     */
    public function __construct(
        public readonly UriInterface $uri,
        public readonly StockDataCollection $stocks,
        public readonly ?string $imagePath = null,
    ) {
        if ($imagePath !== null) {
            Assert::fileExists($imagePath);
            Assert::isArray(getimagesizefromstring(File::get($imagePath)));
        }

        Assert::minCount($stocks, 1, "Zero stocks found for {$uri}. Image stored in {$imagePath}");
    }
}
