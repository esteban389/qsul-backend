<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
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
        return ($user->hasRole(UserRole::CampusCoordinator)) || ($user->hasRole(UserRole::ProcessLeader))
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Employee $employee): Response
    {
        if ($employee->user()->exists()) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::NationalCoordinator)) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator) && ($employee->campus_id !== $user->campus_id)) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::ProcessLeader) && ($employee->process_id !== $user->employee()->first()->process_id || $employee->campus_id !== $user->campus_id)) {
            return Response::deny();
        }

        return Response::allow();
    }

    public function delete(User $user, Employee $employee): Response
    {
        if ($user->hasRole(UserRole::CampusCoordinator) && ($employee->campus_id !== $user->campus_id)) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::ProcessLeader) && ($employee->process_id !== $user->employee()->first()->process_id || $employee->campus_id !== $user->campus_id)) {
            return Response::deny();
        }

        return Response::allow();
    }

    public function restore(User $user, Employee $employee): Response
    {
        return ($user->hasRole(UserRole::CampusCoordinator) && ($employee->campus_id === $user->campus_id))
            || ($user->hasRole(UserRole::ProcessLeader) && ($employee->process_id === $user->employee()->first()->process_id) && ($employee->campus_id === $user->campus_id))
            ? Response::allow()
            : Response::deny();
    }

    public function addService(User $user, Employee $employee, Service $service): Response
    {
        if ($user->hasRole(UserRole::CampusCoordinator) && $employee->campus_id !== $user->campus_id) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::ProcessLeader) && ($employee->process_id !== $user->employee()->first()->process_id || $employee->campus_id !== $user->campus_id)) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::ProcessLeader) && $service->process_id !== $user->employee()->first()->process_id) {
            return Response::deny();
        }

        return Response::allow();
    }
}
