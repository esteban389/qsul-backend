<?php

namespace App\DTOs;

use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Log;

readonly class ResetPasswordDto
{

    public function __construct(
        public string $email,
        public string $token,
        public string $password,
        public string $password_confirmation,
    )
    {
    }

    public static function fromRequest(ResetPasswordRequest $request): ResetPasswordDto
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
