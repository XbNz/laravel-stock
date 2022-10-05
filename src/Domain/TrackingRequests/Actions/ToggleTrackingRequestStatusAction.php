<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Actions;

use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\TrackingRequests\States\DormantState;
use Domain\TrackingRequests\States\PausedState;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class ToggleTrackingRequestStatusAction
{
    public function __invoke(TrackingRequest $trackingRequest): TrackingRequest
    {
        match ($trackingRequest->status::class) {
            PausedState::class => $this->handlePausedToDormant($trackingRequest),
            DormantState::class => $this->handleDormantToPaused($trackingRequest),
            default => throw new InvalidArgumentException('Invalid state'),
        };

        return $trackingRequest->fresh();
    }

    private function handlePausedToDormant(TrackingRequest $trackingRequest): void
    {
        Assert::true($trackingRequest->status->canTransitionTo(DormantState::class));
        $trackingRequest->status->transitionTo(DormantState::class);
    }

    private function handleDormantToPaused(TrackingRequest $trackingRequest): void
    {
        Assert::true($trackingRequest->status->canTransitionTo(PausedState::class));
        $trackingRequest->status->transitionTo(PausedState::class);
    }
}
