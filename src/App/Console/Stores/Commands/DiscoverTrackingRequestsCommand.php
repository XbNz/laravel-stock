<?php

namespace App\Console\Stores\Commands;

use Domain\TrackingRequests\Actions\FulfillTrackingRequestAction;
use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Console\Command;

class DiscoverTrackingRequestsCommand extends Command
{
    protected $signature = 'discover:tracking-requests';

    protected $description = 'Command description';

    public function handle(FulfillTrackingRequestAction $trackingRequestAction): int
    {
        ($trackingRequestAction)(TrackingRequest::query()->needsUpdate()->get());
        return 0;
    }
}
