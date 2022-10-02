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
        $trackingRequest->load('user');
        Assert::true($trackingRequest->user->is($alertChannel->user), 'The alert channel must be owned by the tracking request user.');

        if ($alertChannel->type->requiresVerification()) {
            Assert::notNull($alertChannel->verified_at, 'The alert channel must be verified.');
        }

        $alertChannel->notify(
            new TrackingRequestFailedNotification($trackingRequest)
        );
    }

}
