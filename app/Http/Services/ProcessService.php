<?php

namespace App\Http\Services;

use App\DTOs\University\CreateProcessRequestDto;
use App\DTOs\University\UpdateProcessRequestDto;
use App\Models\Process;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
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
        if(Auth::check()){
            $query = Process::withTrashed();
            return QueryBuilder::for($query)
                ->allowedFilters(['name',
                    AllowedFilter::callback('deleted_at', function ($query, $value) {
                        if ($value === 'null') {
                            $query->whereNull('deleted_at');
                        } elseif ($value === 'not_null') {
                            $query->whereNotNull('deleted_at');
                        }
                    }),
                ])
                ->allowedSorts(['name'])
                ->get();
        }

        return QueryBuilder::for(Process::class)
            ->allowedFilters(['name',
                AllowedFilter::callback('deleted_at', function ($query, $value) {
                        if ($value === 'null') {
                            $query->whereNull('deleted_at');
                        } elseif ($value === 'not_null') {
                            $query->whereNotNull('deleted_at');
                        }
                    }),
                ])
            ->allowedSorts(['name'])
            ->allowedIncludes(['parent','subProcesses'])
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
        ], fn($value) => $value !== null);
        $data['parent_id'] = $requestDto->parent_id;
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
