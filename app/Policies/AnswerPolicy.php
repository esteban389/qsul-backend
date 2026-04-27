<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Answer;
use App\Models\EmployeeService;
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
        $employeeService = $this->getEmployeeService($answer);

        if ($user->hasRole(UserRole::ProcessLeader) || !$employeeService?->employee) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator) && $employeeService->employee->campus_id !== $user->campus_id) {
            return Response::deny();
        }

        return Response::allow();
    }

    public function ignore(User $user, Answer $answer)
    {
        $employeeService = $this->getEmployeeService($answer);

        if ($user->hasRole(UserRole::ProcessLeader) || !$employeeService?->employee) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator) && $employeeService->employee->campus_id !== $user->campus_id) {
            return Response::deny();
        }

        return Response::allow();
    }

    public function view(User $user, Answer $answer): Response
    {
        if ($user->hasRole(UserRole::NationalCoordinator)) {
            return Response::allow();
        }

        $employeeService = $this->getEmployeeService($answer);

        if (!$employeeService?->employee) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator)) {
            return $employeeService->employee->campus_id === $user->campus_id
                ? Response::allow()
                : Response::deny();
        }

        return Response::deny();
    }

    public function solve(User $user, Answer $answer)
    {
        $employeeService = $this->getEmployeeService($answer);

        if (!$employeeService?->employee) {
            return Response::deny();
        }

        if ($user->hasRole(UserRole::CampusCoordinator) && $employeeService->employee->campus_id === $user->campus_id) {
            return Response::allow();
        }

        return Response::deny();
    }

    private function getEmployeeService(Answer $answer): ?EmployeeService
    {
        return $answer->employeeService()
            ->withTrashed()
            ->with([
                'employee' => fn($query) => $query->withTrashed(),
                'service' => fn($query) => $query->withTrashed(),
            ])
            ->first();
    }
}
