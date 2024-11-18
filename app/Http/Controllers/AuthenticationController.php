<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\CreateUserDto;
use App\DTOs\Auth\ForgotPasswordDto;
use App\DTOs\Auth\ResetPasswordDto;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\EmployeeService;
use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AuthenticationController extends Controller
{

    public function __construct(private readonly UserService $userService, private readonly EmployeeService $employeeService)
    {
    }

    public function Login(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (!$request->user()->hasVerifiedEmail()) {
            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
            }
        }
        ;

        return response()->noContent();
    }

    public function Register(CreateUserRequest $request): Response
    {
        $createUserDto = CreateUserDto::fromRequest($request);

        DB::transaction(function () use ($createUserDto) {
            $employee = $this->employeeService->createEmployeeFromUser($createUserDto);
            $this->userService->store($createUserDto, $employee);
        });

        return response()->created();
    }

    public function ForgotPassword(ForgotPasswordRequest $request): Response
    {
        $passwordRequestDto = ForgotPasswordDto::fromRequest($request);
        $this->userService->forgotPassword($passwordRequestDto);
        return \response()->noContent();

    }

    public function ResetPassword(ResetPasswordRequest $request): JsonResponse
    {

        $dto = ResetPasswordDto::fromRequest($request);

        return \response()->json(["status" => __($this->userService->resetPassword($dto))]);
    }

    public function Logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function getUsers(): JsonResponse
    {
        Gate::authorize('viewAny', User::class);
        $users = $this->userService->getUsers();

        return response()->json($users);
    }

    public function getUserById(User $user): JsonResponse
    {
        Gate::authorize('view',$user);
        $user->load('campus');
        $user->load('employee');
        return response()->json($user);
    }

    public function deleteUser(User $user, Request $request): Response
    {
        Gate::authorize('delete',$user);
        DB::transaction(function () use ($user, $request) {
            $this->userService->deleteUser($user);
            if($request->exists('employee')){
                $this->employeeService->deleteEmployee($user->employee);
            }
        });
        return response()->noContent();
    }

    public function restoreUser(User $user, Request $request): Response
    {
        Gate::authorize('restore',$user);
        DB::transaction(function () use ($user, $request) {
            $this->userService->restoreUser($user);
            if($request->exists('employee') && $user->employee()->withTrashed()->exists()){
                $this->employeeService->restoreEmployee($user->employee()->withTrashed()->first());
            }
        });
        return response()->noContent();
    }
}
