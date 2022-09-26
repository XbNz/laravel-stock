<?php

declare(strict_types=1);

namespace Domain\Alerts\Actions;

use Domain\Alerts\Exceptions\ChannelNotVerifiableException;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Notifications\VerifyAlertChannelNotification;

class DispatchVerificationAction
{
    public function __construct(
        private readonly SignedUrlForChannelVerificationAction $generateSignedUrl,
    ) {
    }

    public function __invoke(
        AlertChannel $alertChannel,
    ): void {
        if (! $alertChannel->type->requiresVerification()) {
            throw new ChannelNotVerifiableException("Channel {$alertChannel->uuid} does not require verification");
        }

        $alertChannel->notify(
            new VerifyAlertChannelNotification(
                ($this->generateSignedUrl)($alertChannel),
            )
        );
    }
}
