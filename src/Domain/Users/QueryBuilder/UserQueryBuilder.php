<?php

declare(strict_types=1);

namespace Domain\Users\QueryBuilder;

use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of User
 * @extends Builder<User>
 */
class UserQueryBuilder extends Builder
{
    /**
     * @return self<TModelClass>
     */
    public function whereHasStock(Stock $stock): self
    {
        return $this->whereHas('stocks', function (Builder $query) use ($stock) {
            $query->where('id', $stock->id);
        });
    }
}
