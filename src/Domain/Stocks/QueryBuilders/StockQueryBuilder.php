<?php

declare(strict_types=1);

namespace Domain\Stocks\QueryBuilders;

use Domain\Stocks\Models\Stock;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of Stock
 * @extends Builder<Stock>
 */
class StockQueryBuilder extends Builder
{

}
