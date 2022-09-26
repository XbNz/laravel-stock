<?php

namespace Domain\TrackingRequests\Notifications;

use Domain\Alerts\Models\AlertChannel;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Webmozart\Assert\Assert;

class TrackingRequestFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly TrackingRequest $trackingRequest)
    {
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

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject('Your recent tracking request failed')
            ->line('We were not able to find any items associated with your tracking request URL.')
            ->line('The following tracking request will be queued for deletion in our system:')
            ->line((string) $this->trackingRequest->url)
            ->line('Thank you!');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
