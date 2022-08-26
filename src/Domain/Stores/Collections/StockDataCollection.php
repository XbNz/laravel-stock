<?php

declare(strict_types=1);

namespace Domain\Stores\Collections;

use Illuminate\Support\Collection;
use Support\Contracts\MappableContract;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
class StockDataCollection extends Collection implements MappableContract
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
