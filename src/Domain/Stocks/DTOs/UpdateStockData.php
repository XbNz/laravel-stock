<?php

declare(strict_types=1);

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
        Assert::true($request->has('update_interval'));
        return new self(
            /** @phpstan-ignore-next-line  */
            $request->get('update_interval')
        );
    }
}
