<?php

namespace App\Api\Alerts\Controllers;

use App\Api\Alerts\Requests\CreateAlertChannelRequest;
use App\Api\Alerts\Resources\AlertChannelResource;
use Domain\Alerts\Actions\CreateAlertChannelAction;
use Domain\Alerts\Actions\DispatchVerificationAction;
use Domain\Alerts\DTOs\AlertChannelData;
use Domain\Alerts\Enums\AlertChannel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertChannelController
{
    public function index(Request $request): JsonResource
    {
        return AlertChannelResource::collection($request->user()->alertChannels()->paginate(20));
    }

    public function store(
        CreateAlertChannelRequest $request,
        CreateAlertChannelAction $alertChannelAction,
    ): JsonResource
    {
        return AlertChannelResource::make(
            ($alertChannelAction)(
                new AlertChannelData(
                    AlertChannel::from($request->get('type')),
                    $request->get('value'),
                ),
                $request->user(),
            )
        );
    }

}
