<?php

declare(strict_types=1);

namespace App\Api\Alerts\Resources;

use App\Api\TrackingRequests\Resources\TrackingRequestResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webmozart\Assert\Assert;

/** @mixin \Domain\Alerts\Models\TrackingAlert */
class TrackingAlertResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        Assert::notNull($this->created_at);
        Assert::notNull($this->updated_at);

        return [
            'uuid' => $this->uuid,
            'alert_channel' => AlertChannelResource::make($this->alertChannel()->sole()),
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
