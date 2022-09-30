<?php

namespace App\Console\Stores\Commands;

use Domain\TrackingRequests\Models\TrackingRequest;
use Illuminate\Console\Command;

class DiscoverTrackingRequestsCommand extends Command
{
    protected $signature = 'discover:tracking-requests';

    protected $description = 'Command description';

    public function handle(): int
    {
        TrackingRequest::query()->needsUpdate();
        // TODO: Continue here. Change DB time to UTC.
    }
}
