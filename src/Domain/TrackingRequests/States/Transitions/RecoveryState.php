<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\States\Transitions;

use Domain\TrackingRequests\States\TrackingRequestState;

class RecoveryState extends TrackingRequestState
{
    public function color(): string
    {
        // Hex for orange
        return '#e67e22';
    }

    public function name(): string
    {
        return 'recovery';
    }
}
