<?php

declare(strict_types=1);

namespace Domain\Stores\Collections;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Support\Contracts\MappableContract;

class StockDataCollection extends Collection implements MappableContract
{

}
