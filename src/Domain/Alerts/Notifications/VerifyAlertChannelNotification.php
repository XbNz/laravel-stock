<?php

declare(strict_types=1);

namespace Domain\Alerts\Notifications;

use Domain\Alerts\Models\AlertChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Webmozart\Assert\Assert;

class VerifyAlertChannelNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $signedUrl,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(AlertChannel $alertChannel): array
    {
        $mappings = Config::get('alert.mappings');
        $channels = [$mappings[$alertChannel->type->value]];
        Assert::count($channels, 1, "No channels found for type {$alertChannel->type->value}");

        return $channels;
    }

    public function shouldSend(AlertChannel $alertChannel, string $channel): bool
    {
        return $alertChannel->verified_at === null && $alertChannel->type->requiresVerification();
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject('Verify your alert channel')
            ->line('Verification required to start receiving alerts.')
            ->action('Verify now', $this->signedUrl)
            ->line('Thank you!');
    }
}
