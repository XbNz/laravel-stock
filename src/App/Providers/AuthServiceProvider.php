<?php

declare(strict_types=1);

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Api\Alerts\Policies\AlertChannelPolicy;
use App\Api\Stocks\Policies\StockPolicy;
use Domain\Alerts\Models\AlertChannel;
use Domain\Stocks\Models\Stock;
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
