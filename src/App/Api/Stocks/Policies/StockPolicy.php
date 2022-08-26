<?php

declare(strict_types=1);

namespace App\Api\Stocks\Policies;

use Domain\Stocks\Models\Stock;
use Domain\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class StockPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Stock $stock): Response
    {
        return User::query()->whereHasStock($stock)->get()->contains($user)
            ? Response::allow()
            : Response::deny(code: SymfonyResponse::HTTP_NOT_FOUND);
    }
}
