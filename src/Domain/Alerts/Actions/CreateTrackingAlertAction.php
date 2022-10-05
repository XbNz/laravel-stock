<?php

declare(strict_types=1);

namespace Domain\Alerts\Actions;

use Domain\Alerts\DTOs\CreateTrackingAlertData;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Users\Models\User;

class CreateTrackingAlertAction
{
    public function __invoke(CreateTrackingAlertData $data, User $user): TrackingAlert
    {
        return TrackingAlert::query()->firstOrCreate([
            'user_id' => $user->id,
            'alert_channel_id' => AlertChannel::findByUuid($data->alertChannelUuid)->id,
            'percentage_trigger' => $data->percentageTrigger,
            'availability_trigger' => $data->availabilityTrigger,
        ], [
            'user_id' => $user->id,
            'alert_channel_id' => AlertChannel::findByUuid($data->alertChannelUuid)->id,
            'percentage_trigger' => $data->percentageTrigger,
            'availability_trigger' => $data->availabilityTrigger,
        ]);
    }
}
