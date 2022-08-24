<?php

namespace App\Api\Alerts\Controllers;


use App\Api\Alerts\Resources\AlertChannelResource;
use Domain\Alerts\Models\AlertChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;

class VerifyAlertChannelController
{
    public function __invoke(Request $request, AlertChannel $alertChannel): JsonResource
    {
        if (! $alertChannel->type->requiresVerification()) {
            abort(404);
        }

        if ($alertChannel->verified_at !== null) {
            abort(404);
        }

        $alertChannel->verified_at = now();
        $alertChannel->save();


        return AlertChannelResource::make($alertChannel);
    }
}
