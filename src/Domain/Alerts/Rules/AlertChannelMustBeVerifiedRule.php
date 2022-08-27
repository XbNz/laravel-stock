<?php

namespace Domain\Alerts\Rules;

use Domain\Alerts\Models\AlertChannel;
use Illuminate\Contracts\Validation\Rule;
use Ramsey\Uuid\Uuid;

class AlertChannelMustBeVerifiedRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $alertChannel = AlertChannel::findByUuid(Uuid::fromString($value));
        if (! $alertChannel->type->requiresVerification()) {
            return true;
        }

        return $alertChannel->isVerified();
    }

    public function message(): string
    {
        return 'The alert channel must be verified before it can be used.';
    }
}
