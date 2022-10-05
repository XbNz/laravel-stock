<?php

declare(strict_types=1);

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Alerts\Policies\AlertChannelPolicy;
use Domain\Alerts\Policies\TrackingAlertPolicy;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Policies\StockPolicy;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\Policies\TrackingRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Stock::class => StockPolicy::class,
        AlertChannel::class => AlertChannelPolicy::class,
        TrackingAlert::class => TrackingAlertPolicy::class,
        TrackingRequest::class => TrackingRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
