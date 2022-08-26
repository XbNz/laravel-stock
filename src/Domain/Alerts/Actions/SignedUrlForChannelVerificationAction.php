<?php

declare(strict_types=1);

namespace Domain\Alerts\Actions;

use Domain\Alerts\Models\AlertChannel;
use Illuminate\Routing\UrlGenerator;

class SignedUrlForChannelVerificationAction
{
    public function __construct(
        private readonly UrlGenerator $urlGenerator,
    ) {
    }

    public function __invoke(
        AlertChannel $alertChannel,
        int $minutes = 5,
    ): string {
        return $this->urlGenerator->temporarySignedRoute(
            'alertChannel.verify',
            now()->addMinutes($minutes),
            [
                'alertChannel' => $alertChannel->uuid,
            ],
        );
    }
}
