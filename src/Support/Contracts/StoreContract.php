<?php

declare(strict_types=1);

namespace Support\Contracts;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Domain\Stores\Enums\Store;
use Psr\Http\Message\UriInterface;

interface StoreContract
{
    /**
     * @param array<UriInterface> $uris
     * @return array<StockData>
     */
    public function product(array $uris): array;

    /**
     * @param array<UriInterface> $uris
     * @return array<StockSearchData>
     */
    public function search(array $uris): array;

    public function supports(Store $store): bool;
}
