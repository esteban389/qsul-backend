<?php

namespace App\DTOs;

use App\Http\Requests\Auth\CreateUserRequest;
use Illuminate\Http\UploadedFile;

readonly class CreateUserDto implements DataTransferObject
{
    public function __construct(
        public string $email,
        public string $name,
        public UploadedFile $avatar,
        public ?int $campus_id = null,
    ) {
    }

    /**
     * @param CreateUserRequest $request
     * @return CreateUserDto
     */
    public static function fromRequest($request): CreateUserDto
    {
        return new CreateUserDto(
            email: $request->validated('email'),
            name: $request->validated('name'),
            avatar: $request->validated('avatar'),
            campus_id: $request->validated('campus_id'),
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'campus_id' => $this->campus_id,
        ];
    }
}
