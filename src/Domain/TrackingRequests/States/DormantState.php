<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\States;

class DormantState extends TrackingRequestState
{
    public function color(): string
    {
        // Hex for blue
        return '#3498db';
    }

    public function friendlyName(): string
    {
        return 'Dormant';
    }

    public function name(): string
    {
        return 'dormant';
    }
}
