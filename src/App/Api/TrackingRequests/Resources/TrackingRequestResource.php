<?php

declare(strict_types=1);

namespace App\Api\TrackingRequests\Resources;

use App\Api\Alerts\Resources\TrackingAlertResource;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TrackingRequest */
class TrackingRequestResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'url' => $this->url,
            'tracking_alerts' => TrackingAlertResource::collection(
                $this->whenLoaded('trackingAlerts', $this->trackingAlerts()->get())
            ),
            'store' => $this->store->value,
            'tracking_type' => $this->tracking_type->value,
            'update_interval' => $this->update_interval,
            'status' => $this->status->name(),
            'color' => $this->status->color(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
