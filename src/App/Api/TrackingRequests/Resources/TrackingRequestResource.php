<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Resources;

use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TrackingRequest */
class TrackingRequestResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, string|int>
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'url' => $this->url,
            // TODO: Include any attached alerts
            'store' => $this->store,
            'tracking_type' => $this->tracking_type->value,
            'update_interval' => $this->update_interval,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
