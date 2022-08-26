<?php

declare(strict_types=1);

namespace Domain\Alerts\Rules;

use Domain\Alerts\Enums\AlertChannel;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;
use Webmozart\Assert\Assert;

class ValueChannelRule implements \Illuminate\Contracts\Validation\Rule
{
    public function __construct(
        private readonly ?AlertChannel $type,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        if ($this->type === null) {
            return false;
        }

        return match ($this->type) {
            AlertChannel::Discord => $this->isAcceptableDiscordWebhook($value),
            AlertChannel::Email => $this->isAcceptableEmail($value),
            AlertChannel::SMS => $this->isAcceptableSms($value),
            default => false,
        };
    }

    public function message(): string
    {
        return 'The value entered is not compatible with the type.';
    }

    private function isAcceptableDiscordWebhook(mixed $value): bool
    {
        Assert::string($value);
        if (! preg_match('/^(http|https):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[0-9]{1,5})?(\/\S*)?$/', $value)) {
            return false;
        }

        $hostIsDiscord = Str::of((new Uri($value))->getHost())->startsWith('discord.com');

        if (! $hostIsDiscord) {
            return false;
        }

        return true;
    }

    private function isAcceptableEmail(mixed $value): bool
    {
        $validator = Validator::make([
            'email' => $value,
        ], [
            'email' => ['email:rfc,dns,filter,spoof'],
        ]);

        return $validator->passes();
    }

    private function isAcceptableSms(mixed $value): bool
    {
        Assert::string($value);
        $validator = Validator::make([
            'sms' => $value,
        ], [
            'sms' => ['phone:AUTO,mobile'],
        ]);

        try {
            PhoneNumber::make($value)->formatE164();
        } catch (Exception) {
            return false;
        }

        return $validator->passes();
    }
}
