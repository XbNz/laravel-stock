<?php

namespace App\Api\Alerts\Resources;

use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Domain\Alerts\Models\TrackingAlert */
class TrackingAlertResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'alert_channel' => AlertchannelResource::make($this->alertChannel()->sole()),
            'tracking_requests' => TrackingRequestResource::collection(
                $this->whenLoaded(
                    'trackingRequests',
                    $this->trackingRequests()->take(5)->get(),
                )
            ),
            'percentage_trigger' => $this->percentage_trigger,
            'availability_trigger' => $this->availability_trigger,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
