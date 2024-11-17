<?php

namespace App\Http\Controllers;

use App\DTOs\University\CreateCampusRequestDto;
use App\Http\Requests\University\CreateCampusRequest;
use App\Http\Requests\University\CreateProcessRequest;
use App\Http\Requests\University\UpdateCampusRequest;
use App\Http\Services\CampusService;
use App\Http\Services\ProcessService;
use App\Models\Campus;
use App\Models\Process;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UniversityController extends Controller
{

    public function __construct(
        public readonly CampusService  $campusService,
        public readonly ProcessService $processService,
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
}
