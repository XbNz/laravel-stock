<?php

declare(strict_types=1);

namespace App\Api\Stocks\Resources;

use Domain\Stocks\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Stock */
class StockResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'url' => $this->url,
            'store' => $this->store->value,
            'price' => $this->price,
            'sku' => $this->sku,
            'image' => $this->image,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
