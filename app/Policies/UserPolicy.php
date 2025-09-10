<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{

    public function view(User $authenticatedUser, User $toShow): Response
    {
        if (!Auth::check()) {
            return Response::denyAsNotFound();
        }

        return ($authenticatedUser->hasRole(UserRole::NationalCoordinator
        )|| ($authenticatedUser->hasRole(UserRole::CampusCoordinator) && $toShow->campus_id === $authenticatedUser->campus_id))
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $authenticatedUser,User $user): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return ($authenticatedUser->hasRole(UserRole::NationalCoordinator )&& $user->hasRole(UserRole::CampusCoordinator))
            || ($authenticatedUser->hasRole(UserRole::CampusCoordinator )&& $user->hasRole(UserRole::ProcessLeader));
    }

    public function delete(User $authenticatedUser,User $user): Response
    {
        if (!Auth::check()) {
            return Response::denyAsNotFound();
        }

        return ($authenticatedUser->hasRole(UserRole::NationalCoordinator
        )|| ($authenticatedUser->hasRole(UserRole::CampusCoordinator)&& $user->campus_id === $authenticatedUser->campus_id))
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function viewAny(User $authenticatedUser): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $authenticatedUser->hasRole(UserRole::NationalCoordinator)|| $authenticatedUser->hasRole(UserRole::CampusCoordinator);
    }

    public function restore(User $authenticatedUser, User $user): Response
    {
        if (!Auth::check()) {
            return Response::denyAsNotFound();
        }

        return ($authenticatedUser->hasRole(UserRole::NationalCoordinator)
            || ($authenticatedUser->hasRole(UserRole::CampusCoordinator) && $user->campus_id === $authenticatedUser->campus_id))
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
