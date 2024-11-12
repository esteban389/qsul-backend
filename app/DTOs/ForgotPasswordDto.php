<?php

namespace App\DTOs;

use App\Http\Requests\Auth\ForgotPasswordRequest;

readonly class ForgotPasswordDto implements DataTransferObject
{

    public function __construct(
        public string $email,
    ) {
    }

    /**
     * @param ForgotPasswordRequest $request
     * @return self
     */
    public static function fromRequest($request): self
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
