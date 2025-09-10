<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\User;

class RespondentTypePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::NationalCoordinator);
    }

    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::NationalCoordinator);
    }
}
