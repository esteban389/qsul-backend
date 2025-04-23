<?php

namespace App\Http\Services;

use App\DTOs\Auth\CreateUserDto;
use App\DTOs\Auth\ForgotPasswordDto;
use App\DTOs\Auth\ResetPasswordDto;
use App\DTOs\Auth\UpdateProfileDto;
use App\DTOs\Auth\UserRole;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

readonly class UserService
{

    public function __construct(private FileService $fileService)
    {
    }

    /**
     * Store a new user in the database.
     * @param CreateUserDto $createUserDto
     * @return User
     * @throws ValidationException
     */
    public function store(CreateUserDto $createUserDto, Employee $employee = null): User
    {
        $newUserRole = match (Auth::user()->role){
            UserRole::NationalCoordinator => UserRole::CampusCoordinator,
            UserRole::CampusCoordinator => UserRole::ProcessLeader,
        };

        $campus = match (Auth::user()->role){
            UserRole::NationalCoordinator => $createUserDto->campus_id,
            UserRole::CampusCoordinator => Auth::user()->campus_id,
        };

        if($campus === null){
            throw ValidationException::withMessages([
                'campus_id' => [__('required')],
            ]);
        }

        $path = $this->fileService->storeAvatar($createUserDto->avatar);

        $password = Str::random(16);
        $user = User::query()->create([
            'name' => $createUserDto->name,
            'email' => $createUserDto->email,
            'password' => Hash::make($password),
            'role' => $newUserRole,
            'campus_id' => $campus,
            'avatar' => $path,
            'employee_id' => $employee?->id,
        ]);

        $user->notify(new UserCreatedNotification($password));
        return $user;
    }

    /**
     * Creates a token for the user to reset their password.
     * If the token can't be sent it will log an alert.
     * @param ForgotPasswordDto $dto
     * @return void
     */
    public function forgotPassword(ForgotPasswordDto $dto): void
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $dto->toArray()
        );

        if ($status != Password::RESET_LINK_SENT) {
            Log::alert('Password reset link not sent', ['status' => $status, 'email' => $dto->email]);
        }
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

    public function getUsers(): Collection
    {
        $authenticatedUserRole = Auth::user()->role;

        $query = match ($authenticatedUserRole) {
            UserRole::NationalCoordinator => User::withTrashed()->whereNot('id', Auth::id()),
            UserRole::CampusCoordinator => User::query()->whereNot('id',Auth::id())->where('campus_id', Auth::user()->campus_id),
        };

        return QueryBuilder::for($query)
            ->allowedFilters(['name', 'email', 'role', 'campus_id'])
            ->defaultSort('name')
            ->allowedSorts(['name', 'email', 'role', 'campus_id'])
            ->get();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function restoreUser(User $user): void
    {
        $user->restore();
    }

    public function updateProfile(UpdateProfileDto $dto): void
    {
        $user = \auth()->user();
        $hasEmployee = $user->employee()->exists();
        if ($dto->avatar) {
            if ($user->avatar) {
                $this->fileService->deleteAvatar($user->avatar);
            }
            $path = $this->fileService->storeAvatar($dto->avatar);
            $user->update(['avatar' => $path]);
            if($hasEmployee){
                $user->employee()->update(['avatar' => $path]);
            }
        }

        $user->update([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);
        if($hasEmployee){
            $user->employee()->update([
                'name' => $dto->name,
                'email' => $dto->email,
            ]);
        }
    }

    public function profileResetPassword(string $password): void
    {
        $user = \auth()->user();
        $user->update([
            'password' => Hash::make($password),
        ]);
        event(new PasswordReset($user));
    }
}
