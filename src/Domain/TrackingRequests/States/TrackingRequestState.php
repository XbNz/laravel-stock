<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\States;

use Domain\TrackingRequests\States\Transitions\ToFailedTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class TrackingRequestState extends State
{
    abstract public function color(): string;

    abstract public function name(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(DormantState::class)
            ->allowTransition(
                [DormantState::class, InProgressState::class],
                FailedState::class,
                ToFailedTransition::class
            )
            ->allowTransition(DormantState::class, PausedState::class)
            ->allowTransition(PausedState::class, DormantState::class)
            ->allowTransition(DormantState::class, InProgressState::class)
            ->allowTransition(InProgressState::class, DormantState::class)
            ->allowTransition(RecoveryState::class, DormantState::class)
            ->allowTransition(InProgressState::class, RecoveryState::class);
    }
}
