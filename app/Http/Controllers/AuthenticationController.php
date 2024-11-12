<?php

namespace App\Http\Controllers;

use App\DTOs\CreateUserDto;
use App\DTOs\ForgotPasswordDto;
use App\DTOs\ResetPasswordDto;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\EmployeeService;
use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{

    public function __construct(private readonly UserService $userService, private readonly EmployeeService $employeeService)
    {
    }

    /**
     * Handle an incoming authentication request.
     * @param LoginRequest $request
     * @return Response
     * @throws ValidationException
     */
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

    /**
     * Handle an incoming registration request.
     * @param CreateUserRequest $request
     * @return Response
     * @throws ValidationException
     */
    public function Register(CreateUserRequest $request): Response
    {
        $createUserDto = CreateUserDto::fromRequest($request);

        $employee = $this->employeeService->store($createUserDto);

        $this->userService->store($createUserDto, $employee);

        return response()->created();
    }

    /**
     * Handle an incoming password reset link request.
     * @throws ValidationException
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function ForgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $passwordRequestDto = ForgotPasswordDto::fromRequest($request);

        return \response()->json(["status" => __($this->userService->forgotPassword($passwordRequestDto))]);

    }

    /**
     * Handle an incoming password reset request.
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function ResetPassword(ResetPasswordRequest $request): JsonResponse
    {

        $dto = ResetPasswordDto::fromRequest($request);

        return \response()->json(["status" => __($this->userService->resetPassword($dto))]);
    }

    /**
     * Destroy an authenticated session.
     * @param Request $request
     * @return Response
     */
    public function Logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function DisableUser()
    {

    }

    public function EnableUser()
    {

    }

    /**
     * @throws AuthorizationException
     */
    public function ShowUsers(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);
        $users = $this->userService->getUsers();

        return response()->json($users);
    }

    public function getUserById(User $user,Request $request): JsonResponse
    {
        Gate::authorize('view',$user);
        return response()->json($user);
    }

    public function deleteUser(User $user,Request $request): Response
    {
        Gate::authorize('delete',$user);
        $this->userService->deleteUser($user);
        return response()->noContent();
    }

    public function restoreUser(User $user,Request $request): Response
    {
        Gate::authorize('restore',$user);
        $user->restore();
        return response()->noContent();
    }
}
