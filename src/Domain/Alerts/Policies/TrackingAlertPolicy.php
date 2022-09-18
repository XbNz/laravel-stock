<?php

namespace Domain\Alerts\Policies;

use Domain\Alerts\Models\TrackingAlert;
use Domain\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TrackingAlertPolicy
{
    use HandlesAuthorization;

    public function view(User $user, TrackingAlert $trackingAlert): Response
    {
        return $trackingAlert->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function update(User $user, TrackingAlert $trackingAlert): Response
    {
        return $trackingAlert->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function delete(User $user, TrackingAlert $trackingAlert): Response
    {
        return $trackingAlert->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }

}
