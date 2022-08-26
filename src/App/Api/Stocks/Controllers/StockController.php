<?php

declare(strict_types=1);

namespace App\Api\Stocks\Controllers;

use App\Api\Stocks\Resources\StockResource;
use App\Controller;
use Domain\Stocks\Models\Stock;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Response;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response as SynfonyResponse;
use Webmozart\Assert\Assert;

class StockController extends Controller
{
    public function __construct(private readonly Gate $gate)
    {
    }

    public function index(Request $request): JsonResource
    {
        /** @phpstan-ignore-next-line  */
        $stocks = QueryBuilder::for($request->user()->stocks())
            ->allowedFilters(['store'])
            ->cursorPaginate(20);

        return StockResource::collection($stocks);
    }

    public function show(Stock $stock): JsonResource
    {
        $gate = $this->gate->inspect('view', $stock);

        if ($gate->denied()) {
            Assert::integer($gate->code());
            abort($gate->code());
        }

        return StockResource::make($stock);
    }

    public function destroy(Stock $stock, Request $request): \Illuminate\Http\Response
    {
        $stock->users()->detach($request->user());
        // TODO: Garbage collector should delete the stock if it has no users.

        return Response::noContent(SynfonyResponse::HTTP_NO_CONTENT);
    }
}
