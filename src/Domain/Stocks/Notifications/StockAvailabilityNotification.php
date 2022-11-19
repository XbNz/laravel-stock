<?php

declare(strict_types=1);

namespace Domain\Stocks\Notifications;

use Awssat\Notifications\Messages\DiscordEmbed;
use Awssat\Notifications\Messages\DiscordMessage;
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

    public int $tries = 5;

    public int $timeout = 600;

    public function __construct(private readonly StockHistory $current)
    {
    }

    public function backoff(): array
    {
        return [50, 100, 600, 3600];
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
        Assert::allString($channels);
        Assert::count($channels, 1, "No channels found for type {$alertChannel->type->value}");

        return $channels;
    }

    public function toDiscord(): DiscordMessage
    {
        $title = $this->current->stock->title;
        $sku = $this->current->stock->sku;
        $store = Str::of($this->current->stock->store->value)->headline();
        $link = $this->current->stock->url;
        $imageUrl = config('app.url') . '/products/' . Str::of($this->current->stock->image)->basename();

        return (new DiscordMessage())
            ->from('FreeloadBuddy')
            ->embed(function (DiscordEmbed $embed) use ($title, $store, $link, $imageUrl, $sku) {
                $embed->title("Item now available at {$store}")
                    ->description($title . PHP_EOL . PHP_EOL . $link)
                    ->field('Price', $this->current->price)
                    ->field('SKU', $sku)
                    ->image($imageUrl);
            });
    }

    public function toMail(): MailMessage
    {
        $trimmedStock = Str::of($this->current->stock->title)->limit(15);
        $store = Str::of($this->current->stock->store->value)->headline();
        $link = $this->current->stock->url;

        return (new MailMessage())
            ->subject("{$trimmedStock} is now available!")
            ->line("Stock is now available for the following product: {$this->current->stock->title}")
            ->action("Buy at {$store}", $link);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
