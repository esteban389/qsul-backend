<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class CreateEmployeeRequestDto implements DataTransferObject
{

    public function __construct(
        public string $name,
        public string $email,
        public UploadedFile $avatar,
        public ?int $process_id = null,
    )
    {
    }
    public static function fromRequest($request): CreateEmployeeRequestDto
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            avatar: $request->validated('avatar'),
            process_id: $request->validated('process_id')
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'process_id' => $this->process_id,
        ];
    }
}
