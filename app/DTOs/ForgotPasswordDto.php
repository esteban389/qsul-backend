<?php

namespace App\DTOs;

use App\Http\Requests\Auth\ForgotPasswordRequest;

readonly class ForgotPasswordDto
{

    public function __construct(
        public string $email,
    ) {
    }

    public static function fromRequest(ForgotPasswordRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
