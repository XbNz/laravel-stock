<?php

namespace Domain\Alerts\Rules;

use Domain\Alerts\Models\TrackingAlert;
use Domain\Users\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Ramsey\Uuid\Uuid;

class UserOwnsTrackingAlertRule implements Rule
{
    public function __construct(
        private readonly User $user,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $trackingAlert = TrackingAlert::findByUuid(Uuid::fromString($value));
        return $trackingAlert->user->is($this->user);
    }

    public function message(): string
    {
        return 'The :attribute is not suitable.';
    }
}
