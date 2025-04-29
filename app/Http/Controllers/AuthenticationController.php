<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\CreateUserDto;
use App\DTOs\Auth\ForgotPasswordDto;
use App\DTOs\Auth\ResetPasswordDto;
use App\DTOs\Auth\UpdateProfileDto;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
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
use App\Models\PendingProfileChange;
use App\DTOs\Profile\PendingProfileChangeDto;
use App\Http\Requests\Profile\RequestProfileChangeRequest;
use App\DTOs\Auth\UserRole;
use App\Http\Services\PendingProfileChangeService;


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

    public function updateProfile(UpdateProfileRequest $request)
    {
        $dto = UpdateProfileDto::fromRequest($request);
        $this->userService->updateProfile($dto);

        return response()->json([
            'status' => __('Profile updated successfully'),
            'user' => auth()->user(),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $this->userService->profileResetPassword($request->password);
        return response()->json([
            'status' => __('Password updated successfully'),
        ]);
    }

    /**
     * User requests a profile change (campus, process, or services)
     */
    public function requestProfileChange(RequestProfileChangeRequest $request, PendingProfileChangeService $service): \Illuminate\Http\JsonResponse
    {
        $dto = PendingProfileChangeDto::fromRequest($request);
        $pending = $service->createPendingChange($dto);
        return response()->json(['status' => 'Request submitted', 'pending' => $pending], 201);
    }

    /**
     * Coordinator views all pending profile changes they can approve
     */
    public function pendingProfileChanges(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if ($user->hasRole(UserRole::NationalCoordinator)) {
            $changes = PendingProfileChange::where('change_type', 'campus')->where('status', 'pending')->get();
        } elseif ($user->hasRole(UserRole::CampusCoordinator)) {
            $changes = PendingProfileChange::whereIn('change_type', ['process','services'])
                ->where('status', 'pending')
                ->whereHas('user', function($q) use ($user) {
                    $q->where('campus_id', $user->campus_id);
                })->get();
        } else {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return response()->json($changes);
    }

    /**
     * Coordinator approves or rejects a pending profile change
     */
    public function approveProfileChange(\Illuminate\Http\Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $pending = PendingProfileChange::findOrFail($id);
        $this->authorize('approve', $pending);
        $user = $request->user();
        // Approve or reject
        $action = $request->input('action'); // 'approve' or 'reject'
        if ($action === 'approve') {
            // Apply the change
            $targetUser = $pending->user;
            if ($pending->change_type === 'campus') {
                $targetUser->campus_id = $pending->new_value['campus_id'];
                $targetUser->save();
                if ($targetUser->employee) {
                    $targetUser->employee->campus_id = $pending->new_value['campus_id'];
                    $targetUser->employee->save();
                }
            } elseif ($pending->change_type === 'process') {
                if ($targetUser->employee) {
                    $targetUser->employee->process_id = $pending->new_value['process_id'];
                    $targetUser->employee->save();
                }
            } elseif ($pending->change_type === 'services') {
                if ($targetUser->employee) {
                    $targetUser->employee->services()->sync($pending->new_value['services']);
                }
            }
            $pending->status = 'approved';
            $pending->approved_by = $user->id;
            $pending->approved_at = now();
            $pending->save();
            return response()->json(['status' => 'Change approved and applied']);
        } else {
            $pending->status = 'rejected';
            $pending->approved_by = $user->id;
            $pending->approved_at = now();
            $pending->save();
            return response()->json(['status' => 'Change rejected']);
        }
    }

    /**
     * Get the requests made by the logged-in user
     */
    public function myProfileChangeRequests(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $requests = PendingProfileChange::where('user_id', $user->id)->get();
        return response()->json($requests);
    }
}

