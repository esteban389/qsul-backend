<?php

namespace App\DTOs;

use App\Http\Requests\Auth\CreateUserRequest;

readonly class CreateUserDto
{
    public function __construct(
        public string $email,
        public string $name,
    ) {
    }

    public static function fromRequest(CreateUserRequest $request): CreateUserDto
    {
        return new CreateUserDto(
            email: $request->validated('email'),
            name: $request->validated('name'),
        );
    }
}
