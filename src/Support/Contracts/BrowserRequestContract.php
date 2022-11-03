<?php

namespace Support\Contracts;

use Psr\Http\Message\UriInterface;

interface BrowserRequestContract
{
    public function uri(): UriInterface;

}
