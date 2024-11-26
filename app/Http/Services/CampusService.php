<?php

namespace App\Http\Services;

use App\DTOs\University\CreateCampusRequestDto;
use App\Models\Campus;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

readonly class CampusService
{

    public function __construct(
        public FileService $fileService
    )
    {}
    public function getCampuses(): Collection
    {
        return QueryBuilder::for(Campus::class)
            ->allowedFilters('name')
            ->allowedSorts('name')
            ->get();
    }

   public function createCampus(CreateCampusRequestDto $requestDto): void
   {
        $iconPath = $this->fileService->storeIcon($requestDto->icon);
        Campus::query()->create([
            'name' => $requestDto->name,
            'address' => $requestDto->address,
            'icon' => $iconPath,
        ]);
    }

    public function updateCampus(Campus $campus, CreateCampusRequestDto $requestDto): void
    {
        if($requestDto->icon){
        $this->fileService->deleteIcon($campus->icon);
        $iconPath = $this->fileService->storeIcon($requestDto->icon);
        }

        $data = array_filter([
            'name' => $requestDto->name,
            'address' => $requestDto->address,
            'icon' => $iconPath ?? null,
        ], fn($value) => $value !== null);
        $campus->update($data);
    }

     public function deleteCampus(Campus $campus): void
     {
        $campus->delete();
    }

    public function restoreCampus(Campus $campus): void
    {
        $campus->restore();
    }
}
