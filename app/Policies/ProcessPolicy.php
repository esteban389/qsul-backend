<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Process;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response as SymphonyResponse;

class ProcessPolicy
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

    public function delete(User $user, Process $process): Response
    {
        if(!$user->hasRole(UserRole::NationalCoordinator)) {
            return Response::deny();
        }

        if($process->services()->exists()){
            return Response::denyWithStatus(SymphonyResponse::HTTP_UNPROCESSABLE_ENTITY,Lang::get('Cannot delete a process with services'));
        }

        return Response::allow();
    }

    public function restore(User $user): Response
    {
        return ($user->hasRole(UserRole::NationalCoordinator))
            ? Response::allow()
            : Response::deny();
    }
}
