<?php

namespace Domain\TrackingRequests\States;

class PausedState extends TrackingRequestState
{

    public function color(): string
    {
        // Hex for gray
        return '#7f8c8d';
    }

    public function name(): string
    {
        return 'paused';
    }

}
