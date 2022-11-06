<?php

declare(strict_types=1);

namespace App\Api\Stocks\Resources;

use Domain\Stocks\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webmozart\Assert\Assert;

/** @mixin StockHistory */
class StockHistoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        Assert::notNull($this->created_at);

        return [
            'price' => $this->price,
            'availability' => $this->availability,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
