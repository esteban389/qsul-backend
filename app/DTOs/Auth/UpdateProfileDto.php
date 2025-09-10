<?php

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

class UpdateProfileDto implements DataTransferObject
{

    public function __construct(
        public ?UploadedFile $avatar,
        public string $name,
        public string $email,
    ) {
    }
    /**
     * @inheritDoc
     */
    public static function fromRequest($request): self
    {
        return new self(
            $request->validated('avatar'),
            $request->validated('name'),
            $request->validated('email'),
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'avatar' => $this->avatar,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
