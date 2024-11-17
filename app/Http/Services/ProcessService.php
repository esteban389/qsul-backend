<?php

namespace App\Http\Services;

use App\DTOs\DataTransferObject;
use App\DTOs\University\CreateProcessRequestDto;
use App\Models\Process;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class ProcessService
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
        $parent = Process::query()->find($requestDto->parent_id);
        Process::query()->create([
            'name' => $requestDto->name,
            'icon' => $iconPath,
            'parent_id' => $parent?->id,
        ]);
    }

    public function updateProcess(Process $process, DataTransferObject $requestDto)
    {
        $this->fileService->deleteIcon($process->icon);
        $iconPath = $this->fileService->storeIcon($requestDto->icon);
        $process->update([
            'name' => $requestDto->name,
            'icon' => $iconPath,
        ]);
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
