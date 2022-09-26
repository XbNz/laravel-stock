<?php

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
            ->default(InProgressState::class)
            ->allowTransition(
                [DormantState::class, InProgressState::class],
                FailedState::class,
                ToFailedTransition::class
            )
            ->allowTransition(DormantState::class, PausedState::class)
            ->allowTransition(PausedState::class, DormantState::class);
    }
}
