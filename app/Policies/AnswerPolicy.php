<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnswerPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function restore(User $user, Answer $answer)
    {
        if ($user->hasRole(UserRole::ProcessLeader)) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator) && $answer->employee->campus_id !== $user->campus_id) {
            return Response::deny();
        }

        return Response::allow();
    }

    public function ignore(User $user, Answer $answer)
    {
        if ($user->hasRole(UserRole::ProcessLeader)) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator) && $answer->employee->campus_id !== $user->campus_id) {
            return Response::deny();
        }

        return Response::allow();
    }
}
