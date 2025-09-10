<?php

namespace App\Http\Controllers;

use App\DTOs\University\CreateCampusRequestDto;
use App\DTOs\University\CreateEmployeeRequestDto;
use App\DTOs\University\CreateProcessRequestDto;
use App\DTOs\University\CreateServiceRequestDto;
use App\DTOs\University\UpdateCampusRequestDto;
use App\DTOs\University\UpdateEmployeeRequestDto;
use App\DTOs\University\UpdateProcessRequestDto;
use App\DTOs\University\UpdateServiceRequestDto;
use App\Http\Requests\University\AddServiceToEmployeeRequest;
use App\Http\Requests\University\CreateCampusRequest;
use App\Http\Requests\University\CreateEmployeeRequest;
use App\Http\Requests\University\CreateProcessRequest;
use App\Http\Requests\University\CreateServiceRequest;
use App\Http\Requests\University\UpdateCampusRequest;
use App\Http\Requests\University\UpdateEmployeeRequest;
use App\Http\Requests\University\UpdateProcessRequest;
use App\Http\Requests\University\UpdateServiceRequest;
use App\Http\Services\CampusService;
use App\Http\Services\EmployeeService;
use App\Http\Services\ProcessService;
use App\Http\Services\ServiceService;
use App\Http\Services\UserService;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Process;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UniversityController extends Controller
{

    public function __construct(
        public readonly CampusService $campusService,
        public readonly ProcessService $processService,
        public readonly ServiceService $serviceService,
        public readonly EmployeeService $employeeService,
        public readonly UserService $userService
    ) {
    }

    public function getCampuses(): JsonResponse
    {
        $users = $this->campusService->getCampuses();
        return response()->json($users);
    }

    public function getCampusById(Campus $campus): JsonResponse
    {
        return response()->json($campus);
    }

    public function createCampus(CreateCampusRequest $request): Response
    {
        $requestDto = CreateCampusRequestDto::fromRequest($request);
        DB::transaction(function () use ($requestDto) {
            $this->campusService->createCampus($requestDto);
        });

        return response()->created();
    }

    public function updateCampus(Campus $campus, UpdateCampusRequest $request): Response
    {
        $requestDto = UpdateCampusRequestDto::fromRequest($request);
        DB::transaction(function () use ($campus, $requestDto) {
            $this->campusService->updateCampus($campus, $requestDto);
        });

        return response()->noContent();
    }

    public function deleteCampus(Campus $campus): Response
    {
        Gate::authorize('delete', $campus);
        DB::transaction(function () use ($campus) {
            $this->campusService->deleteCampus($campus);
        });

        return response()->noContent();
    }

    public function restoreCampus(Campus $campus): Response
    {
        Gate::authorize('restore', Campus::class);
        DB::transaction(function () use ($campus) {
            $this->campusService->restoreCampus($campus);
        });

        return response()->noContent();
    }

    public function getProcesses(): JsonResponse
    {
        $users = $this->processService->getProcesses();
        return response()->json($users);
    }

    public function getProcessById(Process $process): JsonResponse
    {
        $process->load('parent', 'subProcesses', 'services');
        return response()->json($process);
    }

    public function createProcess(CreateProcessRequest $request): Response
    {
        $requestDto = CreateProcessRequestDto::fromRequest($request);
        DB::transaction(function () use ($requestDto) {
            $this->processService->createProcess($requestDto);
        });

        return response()->created();
    }

    public function updateProcess(Process $process, UpdateProcessRequest $request): Response
    {
        $requestDto = UpdateProcessRequestDto::fromRequest($request);
        DB::transaction(function () use ($process, $requestDto) {
            $this->processService->updateProcess($process, $requestDto);
        });

        return response()->noContent();
    }

    public function deleteProcess(Process $process): Response
    {
        Gate::authorize('delete', $process);
        DB::transaction(function () use ($process) {
            $this->processService->deleteProcess($process);
        });
        return \response()->noContent();
    }

    public function restoreProcess(Process $process): Response
    {
        Gate::authorize('restore', Process::class);
        DB::transaction(function () use ($process) {
            $this->processService->restoreProcess($process);
        });
        return \response()->noContent();
    }

    public function getServices(): JsonResponse
    {
        $services = $this->serviceService->getServices();
        return response()->json($services);
    }

    public function getServiceById(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function createService(CreateServiceRequest $request): Response
    {
        $requestDto = CreateServiceRequestDto::fromRequest($request);
        DB::transaction(function () use ($requestDto) {
            $this->serviceService->createService($requestDto);
        });
        return response()->created();
    }

    public function updateService(Service $service, UpdateServiceRequest $request): Response
    {
        $requestDto = UpdateServiceRequestDto::fromRequest($request);
        DB::transaction(function () use ($service, $requestDto) {
            $this->serviceService->updateService($service, $requestDto);
        });
        return response()->noContent();
    }

    public function deleteService(Service $service): Response
    {
        Gate::authorize('delete', Service::class);
        DB::transaction(function () use ($service) {
            $this->serviceService->deleteService($service);
        });
        return \response()->noContent();
    }

    public function restoreService(Service $service): Response
    {
        Gate::authorize('restore', Service::class);
        DB::transaction(function () use ($service) {
            $this->serviceService->restoreService($service);
        });
        return \response()->noContent();
    }

    public function getEmployees(): JsonResponse
    {
        $employees = $this->employeeService->getEmployees();
        return response()->json($employees);
    }

    public function getEmployeeById(Employee $employee): JsonResponse
    {
        $employee->load([
            'campus',
            'process',
            'services' => function ($query) {
                $query->withPivot('id');  // Add any other pivot columns you need
            }
        ]);
        if (Auth::check()) {
            $employee->load('user');
        }
        if($employee->campus && $employee->process && $employee->token) {
            $employee->url = $employee->campus->token . "/" . $employee->process->token . "/" . $employee->token;
        }
        return response()->json($employee);
    }

    public function getEmployeeServices(Employee $employee): JsonResponse
    {
        $services = $employee->services;
        return response()->json($services);
    }

    public function createEmployee(CreateEmployeeRequest $request): Response
    {
        $requestDto = CreateEmployeeRequestDto::fromRequest($request);
        DB::transaction(function () use ($requestDto) {
            $this->employeeService->createEmployee($requestDto);
        });
        return response()->created();
    }

    public function updateEmployee(Employee $employee, UpdateEmployeeRequest $request): Response
    {
        $requestDto = UpdateEmployeeRequestDto::fromRequest($request);
        DB::transaction(function () use ($employee, $requestDto) {
            $this->employeeService->updateEmployee($employee, $requestDto);
        });
        return response()->noContent();
    }

    public function addEmployeeService(Employee $employee, AddServiceToEmployeeRequest $request): Response
    {
        $serviceId = $request->validated('service_id');
        DB::transaction(function () use ($employee, $serviceId) {
            $this->employeeService->addServiceToEmployee($employee, $serviceId);
        });
        return response()->created();
    }

    public function removeEmployeeService(Employee $employee, Service $service): Response
    {
        DB::transaction(function () use ($employee, $service) {
            $this->employeeService->removeServiceToEmployee($employee, $service);
        });
        return response()->noContent();
    }

    public function deleteEmployee(Employee $employee): Response
    {
        Gate::authorize('delete', $employee);
        DB::transaction(function () use ($employee) {
            $this->employeeService->deleteEmployee($employee);
            if ($employee->user()->exists()) {
                $this->userService->deleteUser($employee->user);
            }
        });
        return \response()->noContent();
    }

    public function restoreEmployee(Employee $employee): Response
    {
        Gate::authorize('restore', $employee);
        DB::transaction(function () use ($employee) {
            $this->employeeService->restoreEmployee($employee);
            if ($employee->user()->withTrashed()->exists()) {
                $this->userService->restoreUser($employee->user()->withTrashed()->first());
            }
        });
        return \response()->noContent();
    }

    public function getEmployeeUrl(Employee $employee): JsonResponse
    {
        $url = env('FRONTEND_URL') . '/encuesta/' . $employee->campus->token . "/" . $employee->process->token . "/" . $employee->token;

        return response()->json(['url'=>$url]);
    }
}
