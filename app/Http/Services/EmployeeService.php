<?php

namespace App\Http\Services;

use App\DTOs\CreateUserDto;
use App\Models\Employee;

readonly class EmployeeService
{

    public function __construct(private FileService $fileService)
    {
    }
    public function store(CreateUserDto $createUserDto): Employee{

        $avatarUrl = $this->fileService->storeAvatar($createUserDto->avatar);

        return Employee::create([
            'name' => $createUserDto->name,
            'avatar' => $avatarUrl,
            'email' => $createUserDto->email,
            'campus_id' => $createUserDto->campus_id,
        ]);
    }
}
