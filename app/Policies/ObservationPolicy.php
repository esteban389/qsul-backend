<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Answer;
use App\Models\User;

class ObservationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user, Answer $answer)
    {
        if($user->hasRole(UserRole::NationalCoordinator)){
            return true;
        }

        if($user->hasRole(UserRole::CampusCoordinator)){
            $employeeService = $answer->employeeService()->first();
            return $employeeService->employee->campus_id === $user->campus_id;
        }

        if($user->hasRole(UserRole::ProcessLeader)){
            $employeeService = $answer->employeeService()->first();
            $user->load('employee');
            return $employeeService->employee->campus_id === $user->campus_id && $employeeService->employee->process_id === $user->employee->process_id;
        }

        return false;
    }
}
