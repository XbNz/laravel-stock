<?php

namespace Domain\Stocks\Notifications;

use Domain\Alerts\Models\AlertChannel;
use Domain\Stocks\Models\StockHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

class StockAvailabilityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly StockHistory $current)
    {
    }

    public function shouldSend(AlertChannel $alertChannel, string $channel): bool
    {
        if (! $alertChannel->type->requiresVerification()) {
            return true;
        }

        return $alertChannel->verified_at !== null;
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

    public function toDiscord()
    {
        // TODO: Implement toDiscord() method.
    }

    public function toMail($notifiable): MailMessage
    {
        $trimmedStock = Str::of($this->current->stock->title)->limit(15);
        $store = Str::of($this->current->stock->store->value)->headline();
        $link = $this->current->stock->url;

        return (new MailMessage)
            ->subject("{$trimmedStock} is now available!")
            ->line("Stock is now available for the following product: {$this->current->stock->title}")
            ->action("Buy at {$store}", $link);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}