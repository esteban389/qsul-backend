<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }

    public function restore(User $user)
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }
}
