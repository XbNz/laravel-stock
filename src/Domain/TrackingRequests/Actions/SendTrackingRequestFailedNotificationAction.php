<?php

namespace Domain\TrackingRequests\Actions;

use Domain\Alerts\Models\AlertChannel;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Notifications\TrackingRequestFailedNotification;
use Webmozart\Assert\Assert;

class SendTrackingRequestFailedNotificationAction
{
    public function __invoke(
        TrackingRequest $trackingRequest,
        AlertChannel $alertChannel,
    ) {
        if ($alertChannel->type->requiresVerification()) {
            Assert::notNull($alertChannel->verified_at);
        }

        $alertChannel->notify(
            new TrackingRequestFailedNotification($trackingRequest)
        );
    }

}
