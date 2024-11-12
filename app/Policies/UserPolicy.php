<?php

namespace App\Policies;

use App\DTOs\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{

    public function create(User $user): bool
    {
        return Auth::check() || $user->role === UserRole::NationalCoordinator || $user->role === UserRole::CampusCoordinator;
    }

    public function view(User $authenticatedUser, User $toShow): Response
    {
        if (!Auth::check()) {
            return Response::denyAsNotFound();
        }

        return ($authenticatedUser->role === UserRole::NationalCoordinator
            || ($authenticatedUser->role === UserRole::CampusCoordinator && $toShow->campus_id === $authenticatedUser->campus_id))
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $authenticatedUser,User $user): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return ($authenticatedUser->role === UserRole::NationalCoordinator && $user->role === UserRole::CampusCoordinator)
            || ($authenticatedUser->role === UserRole::CampusCoordinator && $user->role === UserRole::ProcessLeader);
    }

    public function delete(User $authenticatedUser,User $user): Response
    {
        if (!Auth::check()) {
            return Response::denyAsNotFound();
        }

        return ($authenticatedUser->role === UserRole::NationalCoordinator
            || ($authenticatedUser->role === UserRole::CampusCoordinator && $user->campus_id === $authenticatedUser->campus_id))
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function viewAny(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $authenticatedUser = Auth::user();
        return $authenticatedUser->role === UserRole::NationalCoordinator || $authenticatedUser->role === UserRole::CampusCoordinator;
    }

    public function restore(User $authenticatedUser, User $user): Response
    {
        if (!Auth::check()) {
            return Response::denyAsNotFound();
        }

        return ($authenticatedUser->role === UserRole::NationalCoordinator
            || ($authenticatedUser->role === UserRole::CampusCoordinator && $user->campus_id === $authenticatedUser->campus_id))
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
