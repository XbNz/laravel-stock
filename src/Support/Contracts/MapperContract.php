<?php

namespace Support\Contracts;

use Psr\Http\Message\UriInterface;
use Symfony\Component\DomCrawler\Crawler;

interface MapperContract
{
    public function map(Crawler $html, UriInterface $searchUri, string $image): MappableContract;
}
