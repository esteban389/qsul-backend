<?php

namespace App\Policies;

use App\DTOs\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{

    public function store(User $user): bool
    {
        if (!Auth::check() || $user->role === UserRole::NationalCoordinator) {
            return false;
        }

        $authenticatedUser = Auth::user();
        return ($user->role === UserRole::CampusCoordinator && $authenticatedUser->role === UserRole::NationalCoordinator)
            || ($user->role === UserRole::ProcessLeader && $authenticatedUser->role === UserRole::CampusCoordinator);
    }
}
