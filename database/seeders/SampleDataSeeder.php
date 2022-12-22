<?php

namespace Database\Seeders;

use Domain\Alerts\Models\AlertChannel;
use Domain\Alerts\Models\TrackingAlert;
use Domain\Stocks\Models\Stock;
use Domain\Stocks\Models\StockHistory;
use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(10)->create()
            ->each(function (User $user) {
                TrackingRequest::factory(['user_id' => $user->id])
                    ->has(
                        TrackingAlert::factory(['user_id' => $user->id])
                            ->for(AlertChannel::factory(['user_id' => $user->id]))
                            ->count(2)
                    )
                    ->has(
                        Stock::factory()->has(
                            StockHistory::factory()->count(3),
                            'histories'
                        )->count(5)
                    )
                    ->count(120)
                    ->create();
            });


    }
}
