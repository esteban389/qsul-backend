<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response as SymphonyResponse;

class CampusPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    /**
     * Determine whether the user can create a campus.
     */
    public function create(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can update the campus.
     */
    public function update(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the user can delete the campus.
     */
    public function delete(User $user, Campus $campus): Response
    {
        if(!$user->hasRole(UserRole::NationalCoordinator)) {
            return Response::deny();
        }

        if($campus->users()->exists() || $campus->employees()->exists()) {
            return Response::denyWithStatus(SymphonyResponse::HTTP_UNPROCESSABLE_ENTITY,Lang::get("Cannot delete campus with associated users or employees"));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can restore the campus.
     */
    public function restore(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }
}
