<?php

namespace App\Http\Services;

use App\DTOs\Auth\CreateUserDto;
use App\DTOs\Auth\UserRole;
use App\DTOs\University\CreateEmployeeRequestDto;
use App\DTOs\University\UpdateEmployeeRequestDto;
use App\Models\Employee;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

readonly class EmployeeService
{

    public function __construct(private FileService $fileService)
    {
    }

    public function getEmployees(): Collection
    {
        return QueryBuilder::for(Employee::class)
            ->allowedFilters(['name', 'email'])
            ->allowedSorts(['name', 'email'])
            ->get();
    }

    public function createEmployeeFromUser(CreateUserDto $createUserDto): Employee{

        $avatarUrl = $this->fileService->storeAvatar($createUserDto->avatar);

        return Employee::query()->create([
            'name' => $createUserDto->name,
            'avatar' => $avatarUrl,
            'email' => $createUserDto->email,
            'campus_id' => $createUserDto->campus_id,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createEmployee(CreateEmployeeRequestDto $requestDto): void
    {
        $campus = Auth::user()->campus_id;
        $process_id = match (Auth::user()->role){
            UserRole::CampusCoordinator => $requestDto->process_id,
            UserRole::ProcessLeader => Auth::user()->employee()->first()->process_id,
        };
        if(isset($requestDto->process_id) && Auth::user()->role === UserRole::ProcessLeader){
            throw new AuthorizationException();
        }
        $path = $this->fileService->storeAvatar($requestDto->avatar);
        Employee::query()->create([
            'name' => $requestDto->name,
            'email' => $requestDto->email,
            'avatar' => $path,
            'campus_id' => $campus,
            'process_id' => $process_id,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function updateEmployee(Employee $employee, UpdateEmployeeRequestDto $requestDto): void
    {
        $process_id = match (Auth::user()->role){
            UserRole::CampusCoordinator => $requestDto->process_id,
            UserRole::ProcessLeader => Auth::user()->employee()->first()->process_id,
        };
        if(isset($requestDto->process_id) && Auth::user()->role === UserRole::ProcessLeader){
            throw new AuthorizationException();
        }
        $this->fileService->deleteAvatar($employee->avatar);
        $path = $this->fileService->storeAvatar($requestDto->avatar);
        $employee->update([
            'name' => $requestDto->name,
            'email' => $requestDto->email,
            'avatar' => $path,
            'process_id' => $process_id,
        ]);
    }

    public function deleteEmployee(Employee $employee): void
    {
        $employee->delete();
    }

    public function restoreEmployee(Employee $employee): void
    {
        $employee->restore();
    }

}
