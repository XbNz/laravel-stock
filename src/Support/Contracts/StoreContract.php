<?php

declare(strict_types=1);

namespace Support\Contracts;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Psr\Http\Message\UriInterface;

interface StoreContract
{
    public function product(array $uris): array;
    public function search(array $uris): array;
}
