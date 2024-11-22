<?php

namespace App\Http\Services;

use App\DTOs\University\CreateProcessRequestDto;
use App\DTOs\University\UpdateProcessRequestDto;
use App\Models\Process;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

readonly class ProcessService
{

    public function __construct(
        public FileService $fileService
    )
    {
    }

    public function getProcesses(): Collection
    {
        return QueryBuilder::for(Process::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name'])
            ->get();
    }

    public function createProcess(CreateProcessRequestDto $requestDto): void
    {
        $iconPath = $this->fileService->storeIcon($requestDto->icon);
        Process::query()->create([
            'name' => $requestDto->name,
            'icon' => $iconPath,
            'parent_id' => $requestDto->parent_id,
        ]);
    }

    public function updateProcess(Process $process, UpdateProcessRequestDto $requestDto)
    {
        if ($requestDto->icon) {
            $this->fileService->deleteIcon($process->icon);
            $iconPath = $this->fileService->storeIcon($requestDto->icon);
        }
        $data = array_filter([
            'name' => $requestDto->name,
            'icon' => $iconPath ?? null,
            'parent_id' => $requestDto->parent_id,
        ], fn($value) => $value !== null);
        $process->update($data);
    }

    public function deleteProcess(Process $process)
    {
        $process->delete();
    }

    public function restoreProcess(Process $process)
    {
        $process->restore();
    }

}
