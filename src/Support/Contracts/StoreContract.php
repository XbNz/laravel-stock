<?php

namespace Support\Contracts;

use Psr\Http\Message\UriInterface;

interface StoreContract
{
    public function product(UriInterface $uri);
    public function search(string $term);
}
