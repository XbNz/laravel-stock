<?php

declare(strict_types=1);

namespace App\Console\Stores\Commands;

use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class DiscoverTrackingRequestsCommand extends Command
{
    protected $signature = 'discover:tracking-requests';

    protected $description = 'Command description';

    public function handle(FulfillTrackingRequestAction $trackingRequestAction): int
    {
        while (true) {
            TrackingRequest::query()->needsUpdate()->get()
                ->each(fn(TrackingRequest $trackingRequest) => ($trackingRequestAction)(Collection::make([$trackingRequest])));
            sleep(10);
        }
    }
}
