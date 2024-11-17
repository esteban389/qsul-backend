<?php

namespace App\Http\Services;

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
        Process::query()->create([
            'name' => $requestDto->name,
            'icon' => $iconPath,
        ]);
    }
}
