<?php

namespace App\Http\Services;

use App\DTOs\University\CreateServiceRequestDto;
use App\DTOs\University\UpdateServiceRequestDto;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

readonly class ServiceService
{

    public function __construct(
        public FileService $fileService
    )
    {
    }
    public function getServices(): Collection
    {
        return QueryBuilder::for(Service::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['name'])
            ->get();
    }

    public function createService(CreateServiceRequestDto $requestDto): void
    {
        $icon = $this->fileService->storeIcon($requestDto->icon);
        Service::query()->create([
            'name' => $requestDto->name,
            'icon' => $icon,
            'process_id'=> $requestDto->process_id
        ]);
    }

    public function updateService(Service $service, UpdateServiceRequestDto $requestDto): void
    {
        if ($requestDto->icon) {
            $this->fileService->deleteIcon($service->icon);
            $icon = $this->fileService->storeIcon($requestDto->icon);
        }
        $service->update([
            'name' => $requestDto->name ?? $service->name,
            'icon' => $icon,
        ]);
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
