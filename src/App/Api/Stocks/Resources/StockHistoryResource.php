<?php

namespace App\Api\Stocks\Resources;

use Domain\Stocks\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockHistory */
class StockHistoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'price' => $this->price,
            'availability' => $this->availability,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
