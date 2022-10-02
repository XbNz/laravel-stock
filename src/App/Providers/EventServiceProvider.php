<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Stocks\Subscribers\StockHistorySubscriber;
use Domain\Stocks\Subscribers\StockSubscriber;
use Domain\TrackingRequests\Subscribers\TrackingRequestSubscriber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    protected $subscribe = [
        TrackingRequestSubscriber::class,
        StockSubscriber::class,
        StockHistorySubscriber::class
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
