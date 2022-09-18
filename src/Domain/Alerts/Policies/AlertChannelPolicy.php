<?php

declare(strict_types=1);

namespace Domain\Alerts\Policies;

use Domain\Alerts\Models\AlertChannel;
use Domain\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AlertChannelPolicy
{
    use HandlesAuthorization;

    public function view(User $user, AlertChannel $alertChannel): Response
    {
        return $alertChannel->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function delete(User $user, AlertChannel $alertChannel): Response
    {
        return $alertChannel->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }
}
