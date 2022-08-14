<?php

namespace Domain\Stores\Actions;

use Domain\Stores\Enums\Store;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

class ParseStoreByLinkAction
{
    public function __invoke(string $link): Store
    {
        $host = (new Uri($link))->getHost();

        return Collection::make(Store::cases())
            ->filter(fn (Store $store) => Str::of($host)->lower()->contains(Str::of($store->storeBaseUri())->lower()))
            ->sole();
    }
}
