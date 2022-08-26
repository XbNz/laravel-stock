<?php

declare(strict_types=1);

namespace App\Api\Alerts\Controllers;

use Domain\Alerts\Actions\DispatchVerificationAction;
use Domain\Alerts\Models\AlertChannel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SendVerificationUrlToAlertChannelController
{
    public function __invoke(
        AlertChannel $alertChannel,
        DispatchVerificationAction $verificationAction,
    ): Response {
        if (! $alertChannel->type->requiresVerification()) {
            abort(404);
        }

        if ($alertChannel->verified_at !== null) {
            abort(404);
        }

        ($verificationAction)($alertChannel);

        return ResponseFacade::noContent(SymfonyResponse::HTTP_ACCEPTED);
    }
}
