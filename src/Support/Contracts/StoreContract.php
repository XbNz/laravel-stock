<?php

declare(strict_types=1);

namespace Support\Contracts;

use Domain\Stores\Enums\Store;

interface StoreContract
{
    public function product(array $uris): array;

    public function search(array $uris): array;

    public function supports(Store $store): bool;
}
