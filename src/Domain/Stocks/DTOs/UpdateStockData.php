<?php

namespace Domain\Stocks\DTOs;

use App\Api\Stocks\Requests\UpdateStockRequest;
use Webmozart\Assert\Assert;

class UpdateStockData
{
    public function __construct(
        public readonly int $updateInterval
    ) {
        Assert::greaterThanEq($updateInterval, 30);
    }

    public static function fromUpdateRequest(UpdateStockRequest $request): self
    {
        return new self(
            $request->get('update_interval')
        );
    }
}
