<?php

namespace App\Http\Services;

use App\DTOs\Auth\UserRole;
use App\DTOs\University\CreateServiceRequestDto;
use App\DTOs\University\UpdateServiceRequestDto;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class ServiceService
{
    public function __construct(
        public FileService $fileService
    ) {}

    public function getServices(): Collection
    {
        $query = Service::query();
        if (Auth::check()) {
            if (Auth::user()->hasRole(UserRole::NationalCoordinator)) {
                $query = Service::withTrashed();
            }
            if (Auth::user()->hasRole(UserRole::ProcessLeader)) {
                $query = $query->where('process_id', Auth::user()->employee()->first()->process_id);
            }
        }

        return QueryBuilder::for($query)
            ->allowedFilters([
                'name',
                'process_id',
                AllowedFilter::callback('deleted_at', function ($query, $value) {
                    if ($value === 'null') {
                        $query->whereNull('deleted_at');
                    } elseif ($value === 'not_null') {
                        $query->whereNotNull('deleted_at');
                    }
                }),
            ])
            ->allowedIncludes(['process'])
            ->allowedSorts(['name'])
            ->get();
    }

    public function createService(CreateServiceRequestDto $requestDto): void
    {
        $icon = $this->fileService->storeIcon($requestDto->icon);
        Service::query()->create([
            'name' => $requestDto->name,
            'icon' => $icon,
            'process_id' => $requestDto->process_id
        ]);
    }

    public function updateService(Service $service, UpdateServiceRequestDto $requestDto): void
    {
        if ($requestDto->icon) {
            $this->fileService->deleteIcon($service->icon);
            $icon = $this->fileService->storeIcon($requestDto->icon);
        }
        $data = array_filter([
            'name' => $requestDto->name,
            'icon' => $icon ?? null,
            'process_id' => $requestDto->process_id
        ], fn($value) => $value !== null);
        $service->update($data);
    }

    public function deleteService(Service $service)
    {
        $service->delete();
    }

    public function restoreService(Service $service)
    {
        $service->restore();
    }
}
