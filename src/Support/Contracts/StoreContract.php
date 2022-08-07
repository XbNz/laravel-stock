<?php

namespace Support\Contracts;

use Domain\Stores\DTOs\StockData;
use Domain\Stores\DTOs\StockSearchData;
use Psr\Http\Message\UriInterface;

interface StoreContract
{
    public function product(UriInterface $uri): StockData;
    public function search(string $term): StockSearchData;
}
