<?php

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;
use App\Http\Requests\Auth\ResetPasswordRequest;

readonly class ResetPasswordDto implements DataTransferObject
{

    public function __construct(
        public string $email,
        public string $token,
        public string $password,
        public string $password_confirmation,
    )
    {
    }

    /**
     * @param ResetPasswordRequest $request
     * @return ResetPasswordDto
     */
    public static function fromRequest($request): ResetPasswordDto
    {
        return new ResetPasswordDto(
            email: $request->validated('email'),
            token: $request->validated('token'),
            password: $request->validated('password'),
            password_confirmation: $request->get('password_confirmation'),
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ];
    }
}
