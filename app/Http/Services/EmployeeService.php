<?php

namespace App\Http\Services;

use App\DTOs\Auth\CreateUserDto;
use App\DTOs\Auth\UserRole;
use App\DTOs\University\CreateEmployeeRequestDto;
use App\DTOs\University\UpdateEmployeeRequestDto;
use App\Models\Employee;
use App\Models\EmployeeService as EmployeeServiceModel;
use App\Models\Service;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

readonly class EmployeeService
{

    public function __construct(private FileService $fileService)
    {
    }

    public function getEmployees(): Collection
    {
        $query = Employee::query();
        if (Auth::check()) {
            $query = Employee::withTrashed();
            if (Auth::user()->hasRole(UserRole::CampusCoordinator)) {
                $query = $query->where('campus_id', Auth::user()->campus_id);
            }
        }
        return QueryBuilder::for($query)
            ->allowedFilters(['name', 'email', 'process.token','campus_id'])
            ->allowedSorts(['name', 'email'])
            ->allowedIncludes(['services', 'process'])
            ->get();
    }

    /**
     * @throws ValidationException
     */
    public function createEmployeeFromUser(CreateUserDto $createUserDto): Employee
    {

        $campus = match (Auth::user()->role) {
            UserRole::NationalCoordinator => $createUserDto->campus_id,
            UserRole::CampusCoordinator => Auth::user()->campus_id,
        };

        if ($campus === null) {
            throw ValidationException::withMessages([
                'campus_id' => [__('required')],
            ]);
        }
        $avatarUrl = $this->fileService->storeAvatar($createUserDto->avatar);

        return Employee::query()->create([
            'name' => $createUserDto->name,
            'avatar' => $avatarUrl,
            'email' => $createUserDto->email,
            'campus_id' => $campus,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createEmployee(CreateEmployeeRequestDto $requestDto): void
    {
        $campus = Auth::user()->campus_id;
        $process_id = match (Auth::user()->role) {
            UserRole::CampusCoordinator => $requestDto->process_id,
            UserRole::ProcessLeader => Auth::user()->employee()->first()->process_id,
        };
        if (isset($requestDto->process_id) && Auth::user()->role === UserRole::ProcessLeader) {
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
        $process_id = match (Auth::user()->role) {
            UserRole::CampusCoordinator => $requestDto->process_id,
            UserRole::ProcessLeader => Auth::user()->employee()->first()->process_id,
        };
        if (isset($requestDto->process_id) && Auth::user()->role === UserRole::ProcessLeader) {
            throw new AuthorizationException();
        }
        if ($requestDto->avatar) {
            $this->fileService->deleteAvatar($employee->avatar);
            $path = $this->fileService->storeAvatar($requestDto->avatar);
        }

        $data = array_filter([
            'name' => $requestDto->name,
            'email' => $requestDto->email,
            'process_id' => $process_id,
            'avatar' => $path ?? $employee->avatar,
        ], fn($value) => $value !== null);

        $employee->update($data);
    }

    public function deleteEmployee(Employee $employee): void
    {
        $employee->delete();
    }

    public function restoreEmployee(Employee $employee): void
    {
        $employee->restore();
    }

    public function addServiceToEmployee(Employee $employee, mixed $serviceId): void
    {
        if ($employee->services()->where('service_id', $serviceId)->exists()) {
            return;
        }
        $employee->services()->attach($serviceId);
    }

    public function removeServiceToEmployee(Employee $employee, Service $service): void
    {
        $employee->services()->detach($service);
    }

    public function getEmployeeByEmployeeServiceId(int $employeeServiceId): Employee
    {
        //Query the employee that by checking the employeeServices intermediate relationship table that connects with Services has the employeeServiceId
        return EmployeeServiceModel::query()
            ->where('id', $employeeServiceId)
            ->firstOrFail()
            ->employee;

    }
}
