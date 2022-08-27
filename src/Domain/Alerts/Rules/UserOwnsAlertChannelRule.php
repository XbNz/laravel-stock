<?php

namespace Domain\Alerts\Rules;

use Domain\Alerts\Models\AlertChannel;
use Domain\Users\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Ramsey\Uuid\Uuid;

class UserOwnsAlertChannelRule implements Rule
{
    public function __construct(
        private readonly User $user,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $alertChannel = AlertChannel::findByUuid(Uuid::fromString($value));
        return $alertChannel->user->is($this->user);
    }

    public function message(): string
    {
        return 'The :attribute is not suitable.';
    }
}
