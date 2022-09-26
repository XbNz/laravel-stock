<?php

namespace Domain\TrackingRequests\States;

class DormantState extends TrackingRequestState
{

    public function color(): string
    {
        // Hex for blue
        return '#3498db';
    }

    public function name(): string
    {
        return 'dormant';
    }

}
