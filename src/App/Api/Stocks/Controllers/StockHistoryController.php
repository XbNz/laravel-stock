<?php

declare(strict_types=1);

namespace App\Api\Stocks\Controllers;

use App\Api\Stocks\Resources\StockHistoryResource;
use Domain\Stocks\Models\Stock;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilder;
use Webmozart\Assert\Assert;

class StockHistoryController
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function __invoke(Stock $stock): JsonResource
    {
        $gate = $this->gate->inspect('view', $stock);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        /** @phpstan-ignore-next-line  */
        $stockHistories = QueryBuilder::for($stock->histories()->with('stock'))
            ->allowedSorts(['price', 'availability', 'created_at'])
            ->cursorPaginate(20);

        return StockHistoryResource::collection($stockHistories);
    }
}
