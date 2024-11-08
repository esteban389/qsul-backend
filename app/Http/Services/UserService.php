<?php

namespace App\Http\Services;

use App\DTOs\CreateUserDto;
use App\DTOs\ForgotPasswordDto;
use App\DTOs\ResetPasswordDto;
use App\Events\UserCreated;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{

    public function store(CreateUserDto $createUserDto): User
    {
        $password = Str::random(16);
        $user = User::create([
            'name' => $createUserDto->name,
            'email' => $createUserDto->email,
            'password' => Hash::make($password),
        ]);

        event(new UserCreated($user, $password));

        return $user;
    }

    /**
     * Creates a token for the user to reset their password.
     *
     * @throws ValidationException
     * @param ForgotPasswordDto $dto
     * @return string
     */
    public function forgotPassword(ForgotPasswordDto $dto): string
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $dto->toArray()
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }

    /**
     * Reset the user's password.
     * @throws ValidationException
     * @param ResetPasswordDto $dto
     * @return string
     */
    public function resetPassword(ResetPasswordDto $dto): string
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise, we will parse the error and return the response.
        $status = Password::reset(
            $dto->toArray(),
            function ($user) use ($dto) {
                $user->forceFill([
                    'password' => Hash::make($dto->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
        return $status;
    }
}
