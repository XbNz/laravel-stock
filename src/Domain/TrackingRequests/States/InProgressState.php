<?php

namespace Domain\TrackingRequests\States;

class InProgressState extends TrackingRequestState
{

    public function color(): string
    {
        // Hex for yellow
        return '#f1c40f';
    }

    public function name(): string
    {
        return 'in_progress';
    }

}
