<?php

namespace Domain\Alerts\Actions;

use Domain\Alerts\DTOs\AlertChannelData;
use Domain\Alerts\Enums\AlertChannel as AlertChannelEnum;
use Domain\Alerts\Models\AlertChannel;
use Domain\Users\Models\User;

class CreateAlertChannelAction
{
    public function __invoke(AlertChannelData $data, User $user): AlertChannel
    {
        return AlertChannel::query()->firstOrCreate([
            'user_id' => $user->id,
            'type' => $data->type,
            'value' => $data->value,
        ], [
            'user_id' => $user->id,
            'type' => $data->type,
            'value' => $data->value,
        ]);
    }
}
