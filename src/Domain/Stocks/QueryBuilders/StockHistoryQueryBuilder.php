<?php

declare(strict_types=1);

namespace Domain\Stocks\QueryBuilders;

use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of StockHistory
 * @extends Builder<StockHistory>
 */
class StockHistoryQueryBuilder extends Builder
{
    /**
     * @return self<TModelClass>
     */
    public function whereHasStock(Stock $stock): self
    {
        return $this->where('stock_id', $stock->id);
    }
}
