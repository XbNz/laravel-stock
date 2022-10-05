<?php

declare(strict_types=1);

namespace Domain\TrackingRequests\Policies;

use Domain\TrackingRequests\Models\TrackingRequest;
use Domain\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TrackingRequestPolicy
{
    use HandlesAuthorization;

    public function view(User $user, TrackingRequest $trackingRequest): Response
    {
        return $trackingRequest->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function update(User $user, TrackingRequest $trackingRequest): Response
    {
        return $trackingRequest->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function delete(User $user, TrackingRequest $trackingRequest): Response
    {
        return $trackingRequest->user->is($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }
}
