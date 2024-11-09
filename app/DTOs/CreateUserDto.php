<?php

namespace App\DTOs;

use App\Http\Requests\Auth\CreateUserRequest;
use Illuminate\Http\UploadedFile;

readonly class CreateUserDto
{
    public function __construct(
        public string $email,
        public string $name,
        public UploadedFile $avatar,
        public ?int $campus_id = null,
    ) {
    }

    public static function fromRequest(CreateUserRequest $request): CreateUserDto
    {
        return new CreateUserDto(
            email: $request->validated('email'),
            name: $request->validated('name'),
            avatar: $request->validated('avatar'),
            campus_id: $request->validated('campus_id'),
        );
    }
}
