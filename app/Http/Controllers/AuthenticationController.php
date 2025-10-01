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

    public function Login(LoginRequest $request): Response | JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

                //Return error if the user account is not fully setup
        if($request->user()->role === UserRole::CampusCoordinator && $request->user()->campus_id === null) {
            $this->logout($request);
            return response()->json(['error' => 'Tu cuenta no está completamente configurada', 'message' => 'Tienes el rol de coordinador seccional pero no se te ha asignado una seccional.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($request->user()->role === UserRole::ProcessLeader && ($request->user()->employee_id === null || $request->user()->campus_id === null || $request->user()->employee()->first()?->process_id === null)) {
            $this->logout($request);
            return response()->json(['error' => 'Tu cuenta no está completamente configurada', 'message' => 'Tienes el rol de líder de proceso pero no se te ha asignado un proceso o seccional.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

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
        $status = $request->query('status', 'pending'); // default to 'pending'
        $dateRange = $request->query('date_range', null); // e.g. '7days', '3months', etc.

        if ($user->hasRole(UserRole::NationalCoordinator)) {
            $query = PendingProfileChange::with(['user', 'requester', 'approver'])
                ->where('change_type', 'campus');
        } elseif ($user->hasRole(UserRole::CampusCoordinator)) {
            $query = PendingProfileChange::with(['user', 'requester', 'approver'])
                ->whereIn('change_type', ['process','services'])
                ->whereHas('user', function($q) use ($user) {
                    $q->where('campus_id', $user->campus_id);
                });
        } else {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Date range filter
        if ($dateRange) {
            $now = \Carbon\Carbon::now();
            if (preg_match('/^(\d+)days$/', $dateRange, $matches)) {
                $query->where('created_at', '>=', $now->copy()->subDays((int)$matches[1]));
            } elseif (preg_match('/^(\d+)months$/', $dateRange, $matches)) {
                $query->where('created_at', '>=', $now->copy()->subMonths((int)$matches[1]));
            }
        }

        $changes = $query->get();
        return response()->json($changes);
    }

    /**
     * Coordinator approves or rejects a pending profile change
     */
    public function approveProfileChange(\Illuminate\Http\Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $pending = PendingProfileChange::findOrFail($id);
        Gate::authorize('approve', $pending);
        $user = $request->user();
        // Approve or reject
        $action = $request->input('action'); // 'approve' or 'reject'
        if ($action === 'approve') {
            // Apply the change
            $targetUser = $pending->user;
            
            // Handle the case where new_value might be a JSON string
            $newValue = is_string($pending->new_value) ? json_decode($pending->new_value, true) : $pending->new_value;
            
            if ($pending->change_type === 'campus') {
                $targetUser->campus_id = $newValue['campus_id'];
                $targetUser->save();
                if ($targetUser->employee) {
                    $targetUser->employee->campus_id = $newValue['campus_id'];
                    $targetUser->employee->save();
                }
            } elseif ($pending->change_type === 'process') {
                if ($targetUser->employee) {
                    // Use the new method to update process and remove services
                    $this->employeeService->updateEmployeeProcess($targetUser->employee, $newValue['process_id']);
                }
            } elseif ($pending->change_type === 'services') {
                if ($targetUser->employee) {
                    $requestedServiceIds = $newValue['services'];
                    
                    // Use a transaction to ensure data integrity
                    DB::beginTransaction();
                    try {
                        // Get current service IDs
                        $currentServiceIds = $targetUser->employee->services()->pluck('services.id')->toArray();
                        
                        // Find services to add (in requested but not in current)
                        $servicesToAdd = array_diff($requestedServiceIds, $currentServiceIds);
                        
                        // Find services to remove (in current but not in requested)
                        $servicesToRemove = array_diff($currentServiceIds, $requestedServiceIds);
                        
                        // Add new services
                        if (!empty($servicesToAdd)) {
                            $targetUser->employee->services()->attach($servicesToAdd);
                        }
                        
                        // Soft delete services to remove by updating the pivot table
                        if (!empty($servicesToRemove)) {
                            // Instead of detaching (which does hard delete), we'll manually soft delete
                            $employeeId = $targetUser->employee->id;
                            
                            // Find the pivot records to soft delete
                            $pivotRecordsToDelete = DB::table('employee_service')
                                ->where('employee_id', $employeeId)
                                ->whereIn('service_id', $servicesToRemove)
                                ->whereNull('deleted_at')
                                ->get(['id']);
                            // Soft delete each record by updating the deleted_at column
                            foreach ($pivotRecordsToDelete as $record) {
                                DB::table('employee_service')
                                    ->where('id', $record->id)
                                    ->update(['deleted_at' => now()]);
                            }
                        }
                        
                        DB::commit();
                        
                        // Log for debugging purposes
                        \Log::info('Profile change - services', [
                            'employee_id' => $targetUser->employee->id,
                            'requested_services' => $requestedServiceIds,
                            'services_to_add' => $servicesToAdd,
                            'services_to_soft_delete' => $servicesToRemove,
                            'final_services' => $targetUser->employee->services()->pluck('services.id')->toArray()
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Log::error('Error updating employee services: ' . $e->getMessage());
                        throw $e;
                    }
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

    public function getOfficeUrl(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        switch($user->role) {
            case UserRole::CampusCoordinator:
                $url = env('FRONTEND_URL') . '/encuesta/' . $user->campus->token;
                break;
            case UserRole::ProcessLeader:
                $url = env('FRONTEND_URL') . '/encuesta/' . $user->campus->token . "/" . $user->employee->process->token;
                break;
            default:
                throw new \Exception('User role not found');
        }

        return response()->json(['url' => $url]);
    }
}

