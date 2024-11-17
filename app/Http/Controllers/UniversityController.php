<?php

namespace App\Http\Controllers;

use App\DTOs\University\CreateCampusRequestDto;
use App\DTOs\University\CreateProcessRequestDto;
use App\DTOs\University\CreateServiceRequestDto;
use App\DTOs\University\UpdateProcessRequestDto;
use App\DTOs\University\UpdateServiceRequestDto;
use App\Http\Requests\University\CreateCampusRequest;
use App\Http\Requests\University\CreateProcessRequest;
use App\Http\Requests\University\CreateServiceRequest;
use App\Http\Requests\University\UpdateCampusRequest;
use App\Http\Requests\University\UpdateProcessRequest;
use App\Http\Requests\University\UpdateServiceRequest;
use App\Http\Services\CampusService;
use App\Http\Services\ProcessService;
use App\Http\Services\ServiceService;
use App\Models\Campus;
use App\Models\Process;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UniversityController extends Controller
{

    public function __construct(
        public readonly CampusService  $campusService,
        public readonly ProcessService $processService,
        public readonly ServiceService $serviceService
    )
    {
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
        $requestDto = CreateCampusRequestDto::fromRequest($request);
        DB::transaction(function () use ($campus, $requestDto) {
            $this->campusService->updateCampus($campus, $requestDto);
        });

        return response()->noContent();
    }

    public function deleteCampus(Campus $campus): Response
    {
        Gate::authorize('delete', Campus::class);
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
        Gate::authorize('delete',Process::class);
        DB::transaction(function () use ($process) {
            $this->processService->deleteProcess($process);
        });
        return \response()->noContent();
    }

    public function restoreProcess(Process $process): Response
    {
        Gate::authorize('restore',Process::class);
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
}
