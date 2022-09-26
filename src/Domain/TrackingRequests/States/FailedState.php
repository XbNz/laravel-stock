<?php

namespace Domain\TrackingRequests\States;

class FailedState extends TrackingRequestState
{

    public function color(): string
    {
        // Hex for red
        return '#e74c3c';
    }

    public function name(): string
    {
        return 'failed';
    }

}
