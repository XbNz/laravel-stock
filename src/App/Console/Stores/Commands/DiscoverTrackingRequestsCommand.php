<?php

declare(strict_types=1);

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
        while (true) {
            ($trackingRequestAction)(TrackingRequest::query()->needsUpdate()->get());
            sleep(10);
        }
    }
}
