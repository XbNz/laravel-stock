<?php

namespace App\Api\TrackingRequests\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Domain\TrackingRequests\Models\TrackingRequest */
class TrackingRequestResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'url' => $this->url,
            'store' => $this->store,
            'tracking_type' => $this->tracking_type,
            'update_interval' => $this->update_interval,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
