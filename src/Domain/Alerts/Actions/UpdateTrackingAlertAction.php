<?php

namespace Domain\Alerts\Actions;

use Domain\Alerts\DTOs\UpdateTrackingAlertData;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;

class UpdateTrackingAlertAction
{
    public function __invoke(UpdateTrackingAlertData $data, TrackingAlert $trackingAlert): TrackingAlert
    {
        if ($data->alertChannelUuid !== null) {
            $trackingAlert->alertChannel()->associate(
                AlertChannel::findByUuid($data->alertChannelUuid)
            );
        }

        if ($data->percentageTrigger !== null) {
            $trackingAlert->percentage_trigger = $data->percentageTrigger;
        }

        if ($data->availabilityTrigger !== null) {
            $trackingAlert->availability_trigger = $data->availabilityTrigger;
        }

        $trackingAlert->save();

        return $trackingAlert->fresh();
    }
}
