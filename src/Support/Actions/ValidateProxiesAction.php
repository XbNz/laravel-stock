<?php

namespace Support\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Webmozart\Assert\Assert;

class ValidateProxiesAction
{
    public function __invoke(): Collection
    {
        $proxies = Config::get('proxy.proxies');
        Assert::isArray($proxies);
        Assert::allStringNotEmpty($proxies);
        Assert::allRegex($proxies, '/^(http|https|socks4|socks4a|socks5|socks5h):\/\//');
        Assert::allNotRegex($proxies, '/:\/\/[^:]+:[^@]+@/');
        Assert::allRegex($proxies, '/:[1-9][0-9]{0,3}$/');

        return Collection::make($proxies);
    }
}
