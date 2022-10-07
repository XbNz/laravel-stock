<?php

declare(strict_types=1);

namespace Domain\Alerts\Rules;

use Domain\Alerts\Models\AlertChannel;
use Domain\Users\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class UserOwnsAlertChannelRule implements Rule
{
    public function __construct(
        private readonly User $user,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        Assert::string($value);
        $alertChannel = AlertChannel::findByUuid(Uuid::fromString($value));
        return $alertChannel->user->is($this->user);
    }

    public function message(): string
    {
        return 'The :attribute is not suitable.';
    }
}
