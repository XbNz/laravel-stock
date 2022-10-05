<?php

declare(strict_types=1);

namespace Tests\Unit\Fakes;

use Domain\Stores\Enums\Store;
use Support\Contracts\StoreContract;

class FakeStore implements StoreContract
{
    public function product(array $uris): array
    {
        return [];
    }

    public function search(array $uris): array
    {
        return [];
    }

    public function supports(Store $store): bool
    {
        return true;
    }
}
