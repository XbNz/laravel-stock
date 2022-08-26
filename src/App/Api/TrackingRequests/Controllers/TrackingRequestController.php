<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Controllers;

use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilder;

class TrackingRequestController
{
    public function index(Request $request): JsonResource
    {
        /** @phpstan-ignore-next-line  */
        $trackingRequests = QueryBuilder::for($request->user()->trackingRequests())
            ->allowedFilters(['store'])
            ->cursorPaginate(20);

        return TrackingRequestResource::collection($trackingRequests);
    }

    public function create(): JsonResource
    {
    }
}
